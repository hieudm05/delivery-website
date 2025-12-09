<?php
namespace App\Http\Controllers\Drivers;

use App\Http\Controllers\Controller;
use App\Models\Driver\DriverProfile;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DriverController extends Controller
{
    // Bán kính tối đa để tìm bưu cục (km)
    const MAX_SEARCH_RADIUS = 50; // Chỉ lấy bưu cục trong vòng 50km
    const MAX_RESULTS = 30;

    public function index()
    {
        return view('driver.index');
    }

    public function create()
    {
        return view('driver.apply');
    }

    /**
     * ✅ KIỂM TRA VỊ TRÍ HIỆN TẠI
     */
    public function checkLocation(Request $request)
    {
        $lat = floatval($request->query('lat'));
        $lng = floatval($request->query('lng'));

        if (!$lat || !$lng) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng cung cấp tọa độ'
            ], 400);
        }

        try {
            // Reverse geocoding sử dụng Nominatim API
            $url = "https://nominatim.openstreetmap.org/reverse?format=json&lat={$lat}&lon={$lng}&zoom=18&addressdetails=1";
            
            $options = [
                'http' => [
                    'header' => "User-Agent: DriverApplicationApp/1.0\r\n"
                ]
            ];
            $context = stream_context_create($options);
            $response = @file_get_contents($url, false, $context);

            if ($response === false) {
                throw new \Exception('Không thể lấy thông tin địa chỉ');
            }

            $data = json_decode($response, true);

            $address = $data['address'] ?? [];
            $displayName = $data['display_name'] ?? 'Không xác định được địa chỉ';

            return response()->json([
                'success' => true,
                'location' => [
                    'lat' => $lat,
                    'lng' => $lng,
                    'address' => $displayName,
                    'details' => [
                        'road' => $address['road'] ?? null,
                        'suburb' => $address['suburb'] ?? $address['neighbourhood'] ?? null,
                        'district' => $address['district'] ?? $address['county'] ?? null,
                        'city' => $address['city'] ?? $address['town'] ?? null,
                        'province' => $address['state'] ?? null,
                        'country' => $address['country'] ?? null,
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Lỗi khi kiểm tra vị trí', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Không thể xác định địa chỉ',
                'location' => [
                    'lat' => $lat,
                    'lng' => $lng,
                    'address' => "Tọa độ: {$lat}, {$lng}"
                ]
            ]);
        }
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

    // 1️⃣ Kiểm tra email hoặc phone đã tồn tại trong bảng users
    $userEmailExists = User::where('email', $request->email)->exists();
    $userPhoneExists = User::where('phone', $request->phone)->exists();

    if ($userPhoneExists) {
        return back()->with('error', 'Số điện thoại đã tồn tại trong hệ thống!');
    }

    if ($userEmailExists) {
        return back()->with('error', 'Email đã tồn tại trong hệ thống!');
    }

    // 2️⃣ Kiểm tra email hoặc phone đã ứng tuyển trước đó (driver_profiles)
    $duplicateProfile = DriverProfile::where('email', $request->email)
        ->orWhere('phone', $request->phone)
        ->first();

    if ($duplicateProfile) {
        return back()->with('error', 'Email hoặc số điện thoại đã ứng tuyển rồi, vui lòng chờ duyệt!');
    }

    // 3️⃣ Lưu ảnh
    $licensePath = $request->hasFile('license_image')
        ? $request->file('license_image')->store('licenses', 'public')
        : null;

    $identityPath = $request->hasFile('identity_image')
        ? $request->file('identity_image')->store('identities', 'public')
        : null;

    // 4️⃣ Tạo hồ sơ ứng tuyển
    DriverProfile::create([
        'user_id' => null,
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
     * ✅ TÌM BƯU CỤC GẦN NHẤT - ĐÃ FIX
     */
    public function getNearbyPostOffices(Request $request)
    {
        $lat = floatval($request->query('lat'));
        $lng = floatval($request->query('lng'));

        // Validate input
        if (!$lat || !$lng) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng cung cấp tọa độ hợp lệ'
            ], 400);
        }

        // Kiểm tra tọa độ có trong phạm vi Hà Nội không
        if ($lat < 20.85 || $lat > 21.25 || $lng < 105.60 || $lng > 106.10) {
            Log::warning('Tọa độ ngoài phạm vi Hà Nội', ['lat' => $lat, 'lng' => $lng]);
            return response()->json([
                'success' => false,
                'message' => 'Vị trí của bạn nằm ngoài khu vực Hà Nội'
            ], 400);
        }

        try {
            $jsonPath = storage_path('app/data/hanoi_post_offices.json');
            
            if (!file_exists($jsonPath)) {
                Log::error('File JSON không tồn tại: ' . $jsonPath);
                return $this->getFallbackOffices($lat, $lng);
            }

            $jsonContent = file_get_contents($jsonPath);
            $data = json_decode($jsonContent, true);

            if (!$data || !isset($data['all_offices']) || !is_array($data['all_offices'])) {
                Log::error('JSON không hợp lệ hoặc không có key all_offices');
                return $this->getFallbackOffices($lat, $lng);
            }

            $offices = $data['all_offices'];
            $validOffices = [];

            // Tính khoảng cách và lọc
            foreach ($offices as $office) {
                // Validate dữ liệu bưu cục
                if (!isset($office['lat']) || !isset($office['lng']) || 
                    !is_numeric($office['lat']) || !is_numeric($office['lng'])) {
                    Log::warning('Bưu cục thiếu tọa độ', ['office' => $office['name'] ?? 'Unknown']);
                    continue;
                }

                $distance = $this->haversine($lat, $lng, $office['lat'], $office['lng']);
                
                // Chỉ lấy bưu cục trong bán kính MAX_SEARCH_RADIUS
                if ($distance <= self::MAX_SEARCH_RADIUS) {
                    $office['distance'] = $distance;
                    
                    // Chuẩn hóa field để frontend dùng chung
                    $office['id'] = $office['id'] ?? 'office_' . uniqid();
                    $office['latitude'] = $office['lat'];
                    $office['longitude'] = $office['lng'];
                    $office['phone'] = $office['phone'] ?? null;
                    
                    $validOffices[] = $office;
                }
            }

            // Kiểm tra nếu không có bưu cục nào trong bán kính
            if (empty($validOffices)) {
                Log::warning('Không tìm thấy bưu cục trong bán kính ' . self::MAX_SEARCH_RADIUS . 'km', [
                    'user_lat' => $lat,
                    'user_lng' => $lng
                ]);
                return $this->getFallbackOffices($lat, $lng);
            }

            // Sắp xếp theo khoảng cách
            usort($validOffices, fn($a, $b) => $a['distance'] <=> $b['distance']);

            // Lấy tối đa MAX_RESULTS bưu cục gần nhất
            $validOffices = array_slice($validOffices, 0, self::MAX_RESULTS);

            Log::info('✅ Tìm thấy ' . count($validOffices) . ' bưu cục trong bán kính ' . self::MAX_SEARCH_RADIUS . 'km', [
                'user_lat' => $lat,
                'user_lng' => $lng,
                'nearest' => $validOffices[0]['name'] ?? 'N/A',
                'nearest_distance' => $validOffices[0]['distance'] ?? 'N/A'
            ]);

            return response()->json([
                'success' => true,
                'data' => array_values($validOffices),
                'count' => count($validOffices),
                'user_location' => ['lat' => $lat, 'lng' => $lng],
                'search_radius_km' => self::MAX_SEARCH_RADIUS
            ]);

        } catch (\Exception $e) {
            Log::error('Lỗi khi tìm bưu cục gần', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->getFallbackOffices($lat, $lng);
        }
    }

    /**
     * ✅ FALLBACK: Danh sách bưu cục mặc định nếu JSON lỗi
     */
    private function getFallbackOffices($lat, $lng)
    {
        $fallbackOffices = [
            ['id' => 'vtp_hoankiem', 'name' => 'Viettel Post Hoàn Kiếm', 'address' => '75 Đinh Tiên Hoàng, Hoàn Kiếm, Hà Nội', 'latitude' => 21.0245, 'longitude' => 105.8412, 'phone' => '024 3824 5678'],
            ['id' => 'vtp_dongda', 'name' => 'Viettel Post Đống Đa', 'address' => '43 Thái Hà, Đống Đa, Hà Nội', 'latitude' => 21.0154, 'longitude' => 105.8194, 'phone' => '024 3852 3456'],
            ['id' => 'vtp_badinh', 'name' => 'Viettel Post Ba Đình', 'address' => '28 Nguyễn Thái Học, Ba Đình, Hà Nội', 'latitude' => 21.0333, 'longitude' => 105.8361, 'phone' => '024 3733 2345'],
            ['id' => 'vtp_caugiay', 'name' => 'Viettel Post Cầu Giấy', 'address' => '122 Cầu Giấy, Cầu Giấy, Hà Nội', 'latitude' => 21.0333, 'longitude' => 105.7944, 'phone' => '024 3755 4567'],
            ['id' => 'vtp_thanxuan', 'name' => 'Viettel Post Thanh Xuân', 'address' => '98 Nguyễn Trãi, Thanh Xuân, Hà Nội', 'latitude' => 20.9953, 'longitude' => 105.8066, 'phone' => '024 3557 8901'],
            ['id' => 'vtp_haibatrung', 'name' => 'Viettel Post Hai Bà Trưng', 'address' => '45 Bà Triệu, Hai Bà Trưng, Hà Nội', 'latitude' => 21.0167, 'longitude' => 105.8486, 'phone' => '024 3974 5678'],
            ['id' => 'vtp_tayho', 'name' => 'Viettel Post Tây Hồ', 'address' => '272 Lạc Long Quân, Tây Hồ, Hà Nội', 'latitude' => 21.0545, 'longitude' => 105.8095, 'phone' => '024 3718 2345'],
            ['id' => 'vtp_longbien', 'name' => 'Viettel Post Long Biên', 'address' => '56 Nguyễn Văn Cừ, Long Biên, Hà Nội', 'latitude' => 21.0368, 'longitude' => 105.8936, 'phone' => '024 3872 3456'],
            ['id' => 'vtp_namtuliem', 'name' => 'Viettel Post Nam Từ Liêm', 'address' => '234 Phạm Văn Đồng, Nam Từ Liêm, Hà Nội', 'latitude' => 21.0458, 'longitude' => 105.7600, 'phone' => '024 3767 8901'],
            ['id' => 'vtp_bactuliem', 'name' => 'Viettel Post Bắc Từ Liêm', 'address' => '89 Xuân Đỉnh, Bắc Từ Liêm, Hà Nội', 'latitude' => 21.0690, 'longitude' => 105.7547, 'phone' => '024 3768 4567'],
        ];

        foreach ($fallbackOffices as &$office) {
            $office['distance'] = $this->haversine($lat, $lng, $office['latitude'], $office['longitude']);
        }

        usort($fallbackOffices, fn($a, $b) => $a['distance'] <=> $b['distance']);

        return response()->json([
            'success' => true,
            'data' => $fallbackOffices,
            'count' => count($fallbackOffices),
            'user_location' => ['lat' => $lat, 'lng' => $lng],
            'fallback' => true,
            'message' => 'Đang sử dụng danh sách bưu cục mặc định'
        ]);
    }

    /**
     * ✅ TÍNH KHOẢNG CÁCH HAVERSINE (KM) - ĐÃ FIX
     */
    private function haversine($lat1, $lon1, $lat2, $lon2)
    {
        // Validate input
        if (!is_numeric($lat1) || !is_numeric($lon1) || 
            !is_numeric($lat2) || !is_numeric($lon2)) {
            Log::warning('Invalid coordinates for haversine calculation');
            return PHP_INT_MAX; // Trả về giá trị lớn để đẩy xuống cuối
        }

        $R = 6371; // Bán kính trái đất (km)
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        
        $a = sin($dLat / 2) ** 2 + 
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * 
             sin($dLon / 2) ** 2;
        
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        
        return round($R * $c, 2);
    }
}