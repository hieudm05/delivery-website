<?php
namespace App\Http\Controllers\Drivers;

use App\Http\Controllers\Controller;
use App\Models\Driver\DriverProfile;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DriverController extends Controller
{
    public function index()
    {
        return view('driver.index');
    }

    public function create()
    {
        return view('driver.apply');
    }

    public function store(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'post_office_id' => 'required|string',
            'post_office_name' => 'required|string',
            'post_office_address' => 'required|string',
            'post_office_lat' => 'nullable|numeric',
            'post_office_lng' => 'nullable|numeric',
            'post_office_phone' => 'nullable|string',
            'license_number' => 'nullable|string|max:50',
            'license_image' => 'nullable|image|max:2048',
            'identity_image' => 'nullable|image|max:2048',
            'experience' => 'nullable|string',
        ]);

        $existingUser = User::where('email', $request->email)->first();
        $userId = $existingUser ? $existingUser->id : null;

        $duplicate = DriverProfile::where('email', $request->email)
            ->orWhere(function($q) use ($userId) {
                if ($userId) {
                    $q->where('user_id', $userId);
                }
            })
            ->first();

        if ($duplicate) {
            return back()->with('error', 'Email này đã ứng tuyển rồi, vui lòng chờ duyệt!');
        }

        $licensePath = $request->hasFile('license_image')
            ? $request->file('license_image')->store('licenses', 'public')
            : null;

        $identityPath = $request->hasFile('identity_image')
            ? $request->file('identity_image')->store('identities', 'public')
            : null;

        DriverProfile::create([
            'user_id' => $userId,
            'full_name' => $request->full_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'province_code' => $request->province_code ?? 1,
            'post_office_id' => $request->post_office_id,
            'post_office_name' => $request->post_office_name,
            'post_office_address' => $request->post_office_address,
            'post_office_lat' => $request->post_office_lat,
            'post_office_lng' => $request->post_office_lng,
            'post_office_phone' => $request->post_office_phone,
            'vehicle_type' => 'Xe máy',
            'license_number' => $request->license_number,
            'license_image' => $licensePath,
            'identity_image' => $identityPath,
            'experience' => $request->experience,
            'status' => 'pending',
        ]);

        return redirect()->back()->with('success', 'Ứng tuyển tài xế thành công! Hồ sơ của bạn đang được xem xét.');
    }

    /**
     * ✅ TÌM BƯU CỤC THEO TỈNH - CẤU TRÚC 3 TẦNG API
     */
    public function getByProvince(Request $request)
    {
        $provinceCode = $request->query('province_code');
        $provinceName = $request->query('province_name', '');

        if (!$provinceCode || !$provinceName) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng cung cấp province_code và province_name'
            ], 400);
        }

        try {
            // 🗺️ Tầng 1: Lấy tọa độ trung tâm bằng Nominatim
            $geocodeResponse = Http::timeout(3)
                ->withHeaders(['User-Agent' => 'ViettelPostApp/1.0'])
                ->get('https://nominatim.openstreetmap.org/search', [
                    'q' => $provinceName . ', Vietnam',
                    'format' => 'json',
                    'limit' => 1
                ]);

            if (!$geocodeResponse->successful() || empty($geocodeResponse->json())) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy tọa độ của tỉnh'
                ], 404);
            }

            $location = $geocodeResponse->json()[0];
            $centerLat = $location['lat'];
            $centerLng = $location['lon'];

            // 🏢 Tầng 2: Gọi Overpass API (đa endpoint)
            $postOffices = $this->searchPostOfficesOverpass($centerLat, $centerLng, 50000, $provinceName);

            // ⚡ Tầng 3: Nếu Overpass không ra kết quả → fallback sang Nominatim search
            if (empty($postOffices)) {
                Log::warning("Overpass không có kết quả, fallback Nominatim Search");
                $postOffices = $this->searchPostOfficesNominatim($provinceName, $centerLat, $centerLng);
            }

            if (empty($postOffices)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy bưu cục nào trong khu vực'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $postOffices,
                'count' => count($postOffices),
                'center' => [
                    'lat' => $centerLat,
                    'lng' => $centerLng
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Lỗi tìm bưu cục theo tỉnh', [
                'error' => $e->getMessage(),
                'province' => $provinceName
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Lỗi hệ thống: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ Tầng 2: TÌM BƯU CỤC BẰNG OVERPASS (đa endpoint fallback)
     */
    private function searchPostOfficesOverpass($lat, $lng, $radius = 500000, $provinceName = null)
    {
        $overpassEndpoints = [
            'https://overpass-api.de/api/interpreter',
            'https://overpass.openstreetmap.fr/api/interpreter',
            'https://overpass.kumi.systems/api/interpreter',
        ];

        $query = "[out:json][timeout:25];
        (
          node['amenity'='post_office'](around:$radius,$lat,$lng);
          node['office'='post_office'](around:$radius,$lat,$lng);
          way['amenity'='post_office'](around:$radius,$lat,$lng);
        );
        out body;>;out skel qt;";

        foreach ($overpassEndpoints as $endpoint) {
            try {
                Log::info("Thử gọi Overpass API: $endpoint");
                $response = Http::timeout(5)->get($endpoint, ['data' => $query]);

                if ($response->successful()) {
                    $data = $response->json();
                    if (!empty($data['elements'])) {
                        Log::info("Overpass trả về kết quả tại $endpoint");
                        return $this->processOverpassResults($data, $lat, $lng, $provinceName);
                    }
                }
            } catch (\Exception $e) {
                Log::warning("Overpass lỗi tại $endpoint: " . $e->getMessage());
                continue;
            }
        }

        return []; // Tất cả endpoint đều lỗi
    }

    private function processOverpassResults($data, $lat, $lng, $provinceName)
    {
        $nodes = array_filter($data['elements'], fn($i) => $i['type'] === 'node' && isset($i['lat'], $i['lon']));
        $postOffices = [];

        foreach ($nodes as $item) {
            $tags = $item['tags'] ?? [];
            $name = $tags['name'] ?? $tags['name:vi'] ?? null;
            $address = $tags['addr:full'] ?? $tags['addr:street'] ?? $tags['addr:city'] ?? null;
            if (!$name || !$address) continue;
            if ($provinceName && stripos($address, $provinceName) === false) continue;

            $distance = $this->haversine($lat, $lng, $item['lat'], $item['lon']);

            $postOffices[] = [
                'id' => 'node_' . $item['id'],
                'name' => $name,
                'address' => $address,
                'latitude' => $item['lat'],
                'longitude' => $item['lon'],
                'distance' => round($distance, 2),
                'phone' => $tags['phone'] ?? $tags['contact:phone'] ?? null,
            ];
        }

        usort($postOffices, fn($a, $b) => $a['distance'] <=> $b['distance']);
        return array_slice($postOffices, 0, 20);
    }

    /**
     * ✅ Tầng 3: Fallback bằng Nominatim Search
     */
    private function searchPostOfficesNominatim($provinceName, $lat, $lng)
    {
        try {
            $url = "https://nominatim.openstreetmap.org/search";
            $response = Http::timeout(5)
                ->withHeaders(['User-Agent' => 'ViettelPostApp/1.0'])
                ->get($url, [
                    'q' => 'bưu cục ' . $provinceName . ', Vietnam',
                    'format' => 'json',
                    'limit' => 50,
                    'lat' => $lat,
                    'lon' => $lng,
                    'bounded' => 1,
                ]);

            if (!$response->successful()) return [];

            $data = $response->json();
            $results = [];
            foreach ($data as $item) {
                $results[] = [
                    'id' => $item['osm_id'],
                    'name' => $item['display_name'],
                    'address' => $item['display_name'],
                    'latitude' => $item['lat'],
                    'longitude' => $item['lon'],
                    'distance' => round($this->haversine($lat, $lng, $item['lat'], $item['lon']), 2),
                    'phone' => null,
                ];
            }
            return $results;
        } catch (\Exception $e) {
            Log::error('Lỗi Nominatim fallback: ' . $e->getMessage());
            return [];
        }
    }

    private function haversine($lat1, $lon1, $lat2, $lon2)
    {
        $R = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;
        return $R * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
