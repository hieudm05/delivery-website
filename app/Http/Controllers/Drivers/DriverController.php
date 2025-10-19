<?php
namespace App\Http\Controllers\Drivers;

use App\Http\Controllers\Controller;
use App\Models\Driver\DriverProfile;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class DriverController extends Controller
{
    protected $goongApiKey;

    public function __construct()
    {
        $this->goongApiKey = config('services.goong.api_key');
    }

    public function index()
    {
        return view('driver.index');
    }

    // Form ứng tuyển
    public function create()
    {
        return view('driver.apply');
    }

    // Gửi đơn ứng tuyển
    public function store(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'province_code' => 'required|string',
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

        // Kiểm tra xem email đã có trong bảng users chưa
        $existingUser = User::where('email', $request->email)->first();
        $userId = $existingUser ? $existingUser->id : null;

        // Kiểm tra trùng hồ sơ ứng tuyển
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

        // Upload ảnh nếu có
        $licensePath = $request->hasFile('license_image')
            ? $request->file('license_image')->store('licenses', 'public')
            : null;

        $identityPath = $request->hasFile('identity_image')
            ? $request->file('identity_image')->store('identities', 'public')
            : null;

        // Tạo hồ sơ tài xế
        DriverProfile::create([
            'user_id' => $userId,
            'full_name' => $request->full_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'province_code' => $request->province_code,
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
     * API: Lấy danh sách bưu cục theo tỉnh (dùng Goong)
     * GET /api/post-offices/by-province?province_code=01&province_name=Hà Nội
     */
    public function getByProvince(Request $request)
    {
        $provinceCode = $request->query('province_code');
        $provinceName = $request->query('province_name', '');
        
        if (!$provinceCode) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng cung cấp province_code'
            ], 400);
        }

        try {
            // ✅ Dùng Goong Place Autocomplete để tìm bưu cục
            $searchResults = $this->searchPostOfficesByGoong($provinceName);
            
            if ($searchResults->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'message' => 'Không tìm thấy bưu cục nào'
                ]);
            }

            // ✅ Lấy chi tiết từng bưu cục (có tọa độ chính xác)
            $offices = [];
            foreach ($searchResults as $result) {
                $detail = $this->getPlaceDetailByGoong($result['place_id']);
                if ($detail) {
                    $offices[] = $detail;
                }
            }

            return response()->json([
                'success' => true,
                'data' => $offices,
                'count' => count($offices)
            ]);

        } catch (\Exception $e) {
            \Log::error('Lỗi lấy bưu cục Goong: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Lỗi hệ thống: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Tìm bưu cục bằng Goong Place Autocomplete
     */
    private function searchPostOfficesByGoong($provinceName)
    {
        try {
            $keywords = ['bưu cục', 'viettel post', 'bưu điện','bưu tá'];
            $results = collect();

            foreach ($keywords as $keyword) {
                $query = $keyword;
                if ($provinceName) {
                    $query .= " {$provinceName}";
                }

                $response = Http::timeout(10)->get('https://rsapi.goong.io/Place/AutoComplete', [
                    'api_key' => $this->goongApiKey,
                    'input' => $query,
                    'limit' => 20
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    
                    if (isset($data['predictions'])) {
                        foreach ($data['predictions'] as $prediction) {
                            // Lọc kết quả có chứa "bưu" hoặc "post"
                            $desc = strtolower($prediction['description'] ?? '');
                            if (
                                strpos($desc, 'bưu') !== false || 
                                strpos($desc, 'post') !== false ||
                                strpos($desc, 'viettel') !== false
                            ) {
                                $results->push([
                                    'place_id' => $prediction['place_id'],
                                    'name' => $prediction['structured_formatting']['main_text'] ?? '',
                                    'address' => $prediction['description'] ?? '',
                                ]);
                            }
                        }
                    }
                }

                usleep(100000); // Delay 100ms để tránh rate limit
            }

            return $results->unique('place_id')->take(10);

        } catch (\Exception $e) {
            \Log::error('Goong search error: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     * Lấy chi tiết địa điểm từ Goong (có tọa độ chính xác)
     */
    private function getPlaceDetailByGoong($placeId)
    {
        try {
            $response = Http::timeout(10)->get('https://rsapi.goong.io/Place/Detail', [
                'place_id' => $placeId,
                'api_key' => $this->goongApiKey
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['result'])) {
                    $result = $data['result'];
                    
                    return [
                        'id' => $placeId,
                        'name' => $result['name'] ?? 'Bưu cục',
                        'address' => $result['formatted_address'] ?? '',
                        'latitude' => $result['geometry']['location']['lat'] ?? null,
                        'longitude' => $result['geometry']['location']['lng'] ?? null,
                        'phone' => $result['formatted_phone_number'] ?? null,
                        'place_id' => $placeId
                    ];
                }
            }

            return null;

        } catch (\Exception $e) {
            \Log::error("Goong detail error for {$placeId}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * API: Tìm bưu cục gần nhất theo tọa độ
     * GET /api/post-offices/nearby?lat=21.0285&lng=105.8542&limit=5
     */
    public function getNearby(Request $request)
    {
        $lat = $request->query('lat');
        $lng = $request->query('lng');
        $limit = $request->query('limit', 5);

        if (!$lat || !$lng) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng cung cấp lat và lng'
            ], 400);
        }

        try {
            // Tìm kiếm bưu cục gần vị trí
            $response = Http::timeout(10)->get('https://rsapi.goong.io/Place/AutoComplete', [
                'api_key' => $this->goongApiKey,
                'input' => 'bưu cục',
                'location' => "{$lat},{$lng}",
                'radius' => 10000, // 10km
                'limit' => $limit
            ]);

            if (!$response->successful()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể kết nối Goong API'
                ], 500);
            }

            $data = $response->json();
            $offices = collect();

            if (isset($data['predictions'])) {
                foreach ($data['predictions'] as $prediction) {
                    $detail = $this->getPlaceDetailByGoong($prediction['place_id']);
                    if ($detail) {
                        // Tính khoảng cách
                        $distance = $this->calculateDistance(
                            $lat, $lng,
                            $detail['latitude'], $detail['longitude']
                        );
                        $detail['distance'] = round($distance, 2);
                        $offices->push($detail);
                    }
                }
            }

            return response()->json([
                'success' => true,
                'data' => $offices->sortBy('distance')->values()
            ]);

        } catch (\Exception $e) {
            \Log::error('Goong nearby error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Tính khoảng cách Haversine (km)
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $R = 6371; // Bán kính Trái Đất (km)
        
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        
        return $R * $c;
    }
}