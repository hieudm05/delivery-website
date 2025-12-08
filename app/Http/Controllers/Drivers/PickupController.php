<?php

namespace App\Http\Controllers\Drivers;

use App\Http\Controllers\Controller;
use App\Models\Driver\DriverProfile;
use App\Models\Driver\Orders\OrderDelivery;
use App\Models\Customer\Dashboard\Orders\Order;
use App\Models\Customer\Dashboard\Orders\OrderImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class PickupController extends Controller
{
    /**
     * Danh sách đơn hàng cần lấy
     */
    public function index(Request $request)
    {
        $status = $request->get('status', 'confirmed');
        $search = $request->get('search');
        $hubId = DriverProfile::where('user_id', Auth::id())->value('post_office_id');
        
        if(!$hubId){
            return redirect()->back()->with('error', 'Chưa có thông tin bưu cục. Vui lòng cập nhật hồ sơ tài xế.');
        }
        
        $orders = Order::query()
            ->whereIn('status', ['confirmed', 'picking_up'])
            ->where(function($q) use ($hubId) {
                $q->where('current_hub_id', $hubId)
                  ->orWhere('post_office_id', $hubId);
            })
            ->when($search, function($q) use ($search) {
                $q->where(function($query) use ($search) {
                    $query->where('id', 'like', "%{$search}%")
                          ->orWhere('sender_name', 'like', "%{$search}%")
                          ->orWhere('sender_phone', 'like', "%{$search}%")
                          ->orWhere('sender_address', 'like', "%{$search}%");
                });
            })
            ->with('products')
            ->orderBy('pickup_time', 'asc')
            ->paginate(20);

        return view('driver.pickup.index', compact('orders'));
    }

    /**
     * Chi tiết đơn hàng cần lấy
     */
    public function show($id)
    {
        $order = Order::with(['products', 'pickupImages'])
            ->findOrFail($id);

        // Chỉ cho phép xem đơn đã xác nhận hoặc đang lấy hàng
        if (!in_array($order->status, ['confirmed', 'picking_up'])) {
            return redirect()->route('driver.pickup.index')
                ->with('error', 'Đơn hàng không ở trạng thái cần lấy hàng');
        }

        return view('driver.pickup.show', compact('order'));
    }

    /**
     * Bắt đầu lấy hàng (cập nhật status = picking_up)
     */
    public function startPickup($id)
    {
        try {
            $order = Order::findOrFail($id);

            if ($order->status !== 'confirmed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Đơn hàng không ở trạng thái chờ lấy hàng'
                ], 400);
            }

            $order->update([
                'status' => 'picking_up',
                'actual_pickup_start_time' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Đã bắt đầu lấy hàng',
                'order' => $order
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ XÁC NHẬN ĐÃ LẤY HÀNG THÀNH CÔNG
     * Tự động phân công giao hàng cho đơn nội thành Hà Nội
     */
    public function confirmPickup(Request $request, $id)
    {
        $request->validate([
            'images' => 'required|array|min:1',
            'images.*' => 'required|image|mimes:jpeg,png,jpg|max:5120',
            'actual_packages' => 'required|integer|min:1',
            'actual_weight' => 'nullable|numeric|min:0',
            'note' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $order = Order::findOrFail($id);

            if (!in_array($order->status, ['confirmed', 'picking_up'])) {
                throw new \Exception('Đơn hàng không ở trạng thái có thể lấy hàng');
            }

            // Lưu ảnh lấy hàng
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $index => $image) {
                    $path = $image->store('orders/pickup', 'public');
                    
                    OrderImage::create([
                        'order_id' => $order->id,
                        'image_path' => $path,
                        'type' => 'pickup',
                        'note' => $request->note ?? "Ảnh lấy hàng " . ($index + 1),
                    ]);
                }
            }

            // ✅ KIỂM TRA: Đơn có trong nội thành Hà Nội không?
            // ✅ ƯU TIÊN: Lấy từ tọa độ GPS thực tế (chính xác nhất)
            $districtToCheck = null;
            
            if ($order->recipient_latitude && $order->recipient_longitude) {
                // Gọi Goong API lấy thông tin quận từ tọa độ
                $districtToCheck = $this->getDistrictFromCoordinates(
                    $order->recipient_latitude,
                    $order->recipient_longitude
                );
                
                \Log::info('Check inner Hanoi from GPS', [
                    'order_id' => $order->id,
                    'lat' => $order->recipient_latitude,
                    'lng' => $order->recipient_longitude,
                    'district_from_goong' => $districtToCheck
                ]);
            }
            
            // ✅ FALLBACK: Nếu API lỗi hoặc không có GPS, dùng district_code
            if (!$districtToCheck) {
                $districtToCheck = $order->district_code;
                \Log::info('Fallback to district_code', [
                    'order_id' => $order->id,
                    'district_code' => $districtToCheck
                ]);
            }
            
            $isInnerHanoi = $this->isInnerHanoiByDistrict($districtToCheck);
            
            \Log::info('Inner Hanoi check result', [
                'order_id' => $order->id,
                'district_checked' => $districtToCheck,
                'is_inner_hanoi' => $isInnerHanoi
            ]);

            if ($isInnerHanoi) {
                // ✅ ĐƠN NỘI THÀNH: Tự động phân công giao luôn
                $order->update([
                    'status' => Order::STATUS_SHIPPING, // Đang giao
                    'actual_pickup_time' => now(),
                    'actual_packages' => $request->actual_packages,
                    'actual_weight' => $request->actual_weight,
                    'pickup_note' => $request->note,
                    'pickup_driver_id' => Auth::id(),
                    'driver_id' => Auth::id(), // ✅ Gắn luôn tài xế giao hàng
                ]);

                // ✅ Tạo bản ghi OrderDelivery
                OrderDelivery::createNewAttempt($order->id, Auth::id());

                $districtName = $this->getDistrictName($districtToCheck);
                
                $message = '✅ Đã lấy hàng thành công đơn #' . $order->id . 
                           '<br><strong>🚚 Đơn giao ' . $districtName . ' (Nội thành Hà Nội)</strong>' .
                           '<br>📍 Bạn sẽ giao hàng ngay!';
                
                $redirectRoute = route('driver.delivery.show', $order->id);

            } else {
                // ✅ ĐƠN NGOẠI THÀNH: Về hub như cũ
                $order->update([
                    'status' => Order::STATUS_PICKED_UP, // Đã lấy hàng
                    'actual_pickup_time' => now(),
                    'actual_packages' => $request->actual_packages,
                    'actual_weight' => $request->actual_weight,
                    'pickup_note' => $request->note,
                    'pickup_driver_id' => Auth::id(),
                ]);

                $districtName = $this->getDistrictName($districtToCheck);

                $message = '✅ Đã lấy hàng thành công đơn #' . $order->id . 
                           '<br>📦 Đơn giao ' . $districtName . ' (Ngoại thành)' .
                           '<br>🏢 Vui lòng mang về bưu cục để phân công giao hàng.';
                
                $redirectRoute = route('driver.pickup.index');
            }

            // Cập nhật group status (nếu có)
            if ($order->isPartOfGroup()) {
                $order->orderGroup->updateGroupStatus();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $message,
                'redirect_url' => $redirectRoute,
                'is_inner_hanoi' => $isInnerHanoi,
                'order' => $order->fresh(['pickupImages'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ KIỂM TRA ĐỊA CHỈ CÓ TRONG NỘI THÀNH HÀ NỘI KHÔNG
     * Hỗ trợ cả MÃ HÀNH CHÍNH (001-019) và TÊN QUẬN từ Goong API
     */
    private function isInnerHanoiByDistrict($districtCode)
    {
        if (!$districtCode) {
            return false;
        }

        // ✅ MAP MÃ HÀNH CHÍNH -> TÊN QUẬN (12 quận nội thành Hà Nội)
        $districtCodeMap = [
            '001' => 'Ba Đình',
            '002' => 'Hoàn Kiếm',
            '003' => 'Tây Hồ',
            '004' => 'Long Biên',
            '005' => 'Cầu Giấy',
            '006' => 'Đống Đa',
            '007' => 'Hai Bà Trưng',
            '008' => 'Hoàng Mai',
            '009' => 'Thanh Xuân',
            '016' => 'Nam Từ Liêm',
            '017' => 'Bắc Từ Liêm',
            '019' => 'Hà Đông',
        ];

        // ✅ DANH SÁCH TÊN QUẬN (có dấu + không dấu)
        $innerDistrictNames = [
            'Ba Đình', 'Ba Dinh',
            'Hoàn Kiếm', 'Hoan Kiem',
            'Tây Hồ', 'Tay Ho',
            'Long Biên', 'Long Bien',
            'Cầu Giấy', 'Cau Giay',
            'Đống Đa', 'Dong Da',
            'Hai Bà Trưng', 'Hai Ba Trung',
            'Hoàng Mai', 'Hoang Mai',
            'Thanh Xuân', 'Thanh Xuan',
            'Nam Từ Liêm', 'Nam Tu Liem',
            'Bắc Từ Liêm', 'Bac Tu Liem',
            'Hà Đông', 'Ha Dong',
        ];

        $normalized = trim($districtCode);

        // ✅ TRƯỜNG HỢP 1: Là mã hành chính (001, 019,...)
        if (is_numeric($normalized)) {
            $paddedCode = str_pad($normalized, 3, '0', STR_PAD_LEFT);
            return isset($districtCodeMap[$paddedCode]);
        }

        // ✅ TRƯỜNG HỢP 2: Là tên quận (có hoặc không có "Quận ")
        $cleanName = str_replace(['Quận ', 'quận ', 'Quan ', 'quan '], '', $normalized);

        // Kiểm tra khớp chính xác
        foreach ($innerDistrictNames as $districtName) {
            if (strcasecmp($cleanName, $districtName) === 0) {
                return true;
            }
        }

        // Kiểm tra khớp một phần (case-insensitive)
        foreach ($innerDistrictNames as $districtName) {
            if (stripos($cleanName, $districtName) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * ✅ LẤY TÊN QUẬN/HUYỆN ĐỂ HIỂN THỊ
     * Từ mã hành chính hoặc tên Goong API -> Tên chuẩn có dấu
     */
    private function getDistrictName($districtCode)
    {
        // Map mã hành chính -> tên quận
        $codeToName = [
            '001' => 'Ba Đình',
            '002' => 'Hoàn Kiếm',
            '003' => 'Tây Hồ',
            '004' => 'Long Biên',
            '005' => 'Cầu Giấy',
            '006' => 'Đống Đa',
            '007' => 'Hai Bà Trưng',
            '008' => 'Hoàng Mai',
            '009' => 'Thanh Xuân',
            '016' => 'Nam Từ Liêm',
            '017' => 'Bắc Từ Liêm',
            '019' => 'Hà Đông',
        ];

        // Map tên không dấu -> tên có dấu
        $nameMap = [
            'Ba Dinh' => 'Ba Đình',
            'Hoan Kiem' => 'Hoàn Kiếm',
            'Tay Ho' => 'Tây Hồ',
            'Long Bien' => 'Long Biên',
            'Cau Giay' => 'Cầu Giấy',
            'Dong Da' => 'Đống Đa',
            'Hai Ba Trung' => 'Hai Bà Trưng',
            'Hoang Mai' => 'Hoàng Mai',
            'Thanh Xuan' => 'Thanh Xuân',
            'Nam Tu Liem' => 'Nam Từ Liêm',
            'Bac Tu Liem' => 'Bắc Từ Liêm',
            'Ha Dong' => 'Hà Đông',
        ];

        $normalized = trim($districtCode);

        // ✅ Nếu là mã số
        if (is_numeric($normalized)) {
            $paddedCode = str_pad($normalized, 3, '0', STR_PAD_LEFT);
            return $codeToName[$paddedCode] ?? 'nội thành Hà Nội';
        }

        // ✅ Loại bỏ "Quận " nếu có
        $cleanName = str_replace(['Quận ', 'quận ', 'Quan ', 'quan '], '', $normalized);

        // ✅ Tìm trong map tên không dấu
        foreach ($nameMap as $key => $value) {
            if (strcasecmp($cleanName, $key) === 0) {
                return $value;
            }
        }

        // ✅ Tìm trong map tên có dấu (trả về nguyên bản)
        foreach (array_values($nameMap) as $districtName) {
            if (strcasecmp($cleanName, $districtName) === 0) {
                return $districtName;
            }
        }

        return 'nội thành Hà Nội';
    }

    /**
     * ✅ PHƯƠNG THỨC HỖ TRỢ: Lấy thông tin địa chỉ từ Goong API
     * Đây là phương thức CHÍNH để xác định quận/huyện chính xác
     */
    private function getDistrictFromCoordinates($latitude, $longitude)
    {
        try {
            $apiKey = config("services.goong.api_key");
            
            if (!$apiKey) {
                \Log::warning('GOONG_API_KEY not configured in .env');
                return null;
            }

            // Cache key (làm tròn 4 chữ số để tăng cache hit rate)
            $latRounded = round($latitude, 4);
            $lngRounded = round($longitude, 4);
            $cacheKey = "goong_district_{$latRounded}_{$lngRounded}";
            
            // Kiểm tra cache (24h)
            if (\Cache::has($cacheKey)) {
                $cached = \Cache::get($cacheKey);
                \Log::info('Goong API: Using cached district', [
                    'district' => $cached
                ]);
                return $cached;
            }

            // ✅ Gọi Goong Reverse Geocoding API
            $url = "https://rsapi.goong.io/Geocode?latlng={$latitude},{$longitude}&api_key={$apiKey}";
            
            \Log::info('Calling Goong API', ['url' => $url]);
            
            $response = \Http::timeout(10)->get($url);

            if ($response->successful()) {
                $data = $response->json();
                
                \Log::info('Goong API Response', ['data' => $data]);
                
                // ✅ Ưu tiên 1: Lấy từ compound.district
                if (isset($data['results'][0]['compound']['district'])) {
                    $district = $data['results'][0]['compound']['district'];
                    
                    // Cache kết quả
                    \Cache::put($cacheKey, $district, now()->addHours(24));
                    
                    \Log::info('Goong API SUCCESS: Got district from compound', [
                        'lat' => $latitude,
                        'lng' => $longitude,
                        'district' => $district
                    ]);
                    
                    return $district;
                }
                
                // ✅ Ưu tiên 2: Parse từ formatted_address
                if (isset($data['results'][0]['formatted_address'])) {
                    $address = $data['results'][0]['formatted_address'];
                    $district = $this->extractDistrictFromAddress($address);
                    
                    if ($district) {
                        // Cache kết quả
                        \Cache::put($cacheKey, $district, now()->addHours(24));
                        
                        \Log::info('Goong API SUCCESS: Extracted district from address', [
                            'address' => $address,
                            'district' => $district
                        ]);
                        
                        return $district;
                    }
                }
            } else {
                \Log::error('Goong API Error', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
            }

            return null;

        } catch (\Exception $e) {
            \Log::error('Goong API Exception: ' . $e->getMessage(), [
                'lat' => $latitude,
                'lng' => $longitude
            ]);
            return null;
        }
    }

    /**
     * ✅ TRÍCH XUẤT TÊN QUẬN TỪ ĐỊA CHỈ ĐẦY ĐỦ
     * VD: "13 Trịnh Văn Bô, Phường Xuân Phương, Quận Nam Từ Liêm, Thành phố Hà Nội"
     * -> "Nam Từ Liêm"
     */
    private function extractDistrictFromAddress($address)
    {
        // Danh sách pattern để tìm quận
        $patterns = [
            '/Quận\s+([^,]+)/ui',      // "Quận Nam Từ Liêm"
            '/Huyện\s+([^,]+)/ui',     // "Huyện Gia Lâm"
            '/Thị xã\s+([^,]+)/ui',    // "Thị xã Sơn Tây"
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $address, $matches)) {
                $district = trim($matches[1]);
                
                \Log::info('Extracted district from address', [
                    'address' => $address,
                    'pattern' => $pattern,
                    'district' => $district
                ]);
                
                return $district;
            }
        }

        \Log::warning('Could not extract district from address', [
            'address' => $address
        ]);

        return null;
    }

    /**
     * ✅ TEST: Kiểm tra Goong API với tọa độ thực tế
     * Route test: GET /driver/test-goong-api?order_id=23
     */
    public function testGoongApi(Request $request)
    {
        $orderId = $request->get('order_id');
        
        if (!$orderId) {
            return response()->json([
                'error' => 'Thiếu order_id. Sử dụng: /driver/test-goong-api?order_id=23'
            ]);
        }

        $order = Order::findOrFail($orderId);

        $result = [
            'order_id' => $order->id,
            'recipient_name' => $order->recipient_name,
            'recipient_address' => $order->recipient_full_address,
            'database' => [
                'district_code' => $order->district_code,
                'latitude' => $order->recipient_latitude,
                'longitude' => $order->recipient_longitude,
            ],
            'goong_api' => [],
            'check_result' => []
        ];

        // Test Goong API
        if ($order->recipient_latitude && $order->recipient_longitude) {
            $districtFromGoong = $this->getDistrictFromCoordinates(
                $order->recipient_latitude,
                $order->recipient_longitude
            );

            $result['goong_api'] = [
                'district' => $districtFromGoong,
                'status' => $districtFromGoong ? 'success' : 'failed'
            ];

            // Kiểm tra nội thành
            $isInnerByGoong = $this->isInnerHanoiByDistrict($districtFromGoong);
            $isInnerByCode = $this->isInnerHanoiByDistrict($order->district_code);

            $result['check_result'] = [
                'by_goong_api' => [
                    'district' => $districtFromGoong,
                    'is_inner_hanoi' => $isInnerByGoong,
                    'action' => $isInnerByGoong ? 'Tài xế giao luôn' : 'Về hub'
                ],
                'by_district_code' => [
                    'district_code' => $order->district_code,
                    'is_inner_hanoi' => $isInnerByCode,
                    'action' => $isInnerByCode ? 'Tài xế giao luôn' : 'Về hub'
                ],
                'match' => $isInnerByGoong === $isInnerByCode ? '✅ Khớp' : '⚠️ Không khớp (dùng Goong chính xác hơn)'
            ];
        } else {
            $result['error'] = 'Đơn hàng không có tọa độ GPS';
        }

        return response()->json($result, 200, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    /**
     * Báo cáo lỗi khi lấy hàng (shop không có hàng, địa chỉ sai, ...)
     */
    public function reportIssue(Request $request, $id)
    {
        $request->validate([
            'issue_type' => 'required|in:shop_closed,wrong_address,no_goods,customer_cancel,other',
            'issue_note' => 'required|string|max:500',
            'images' => 'required|array|min:1',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        DB::beginTransaction();
        try {
            $order = Order::findOrFail($id);

            if (!in_array($order->status, ['confirmed', 'picking_up'])) {
                throw new \Exception('Không thể báo cáo lỗi cho đơn hàng này');
            }

            // Cập nhật trạng thái về cancelled
            $order->update([
                'status' => 'cancelled',
                'pickup_issue_type' => $request->issue_type,
                'pickup_issue_note' => $request->issue_note,
                'pickup_issue_time' => now(),
                'pickup_issue_driver_id' => Auth::id(),
            ]);

            // Lưu ảnh minh chứng (nếu có)
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $index => $image) {
                    $path = $image->store('orders/pickup_issues', 'public');
                    
                    OrderImage::create([
                        'order_id' => $order->id,
                        'image_path' => $path,
                        'type' => 'pickup_issue',
                        'note' => $request->issue_note,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Đã báo cáo vấn đề. Bưu cục sẽ xử lý',
                'order' => $order
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Chuyển hàng về bưu cục (sau khi lấy hàng)
     */
    public function transferToHub(Request $request)
    {
        // Xử lý đầu vào
        $orderIds = $request->order_ids;
        
        if (is_string($orderIds)) {
            $decoded = json_decode($orderIds, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $orderIds = $decoded;
            } else {
                $orderIds = [$orderIds];
            }
        }

        if (!is_array($orderIds)) {
            $orderIds = [$orderIds];
        }

        $orderIds = array_filter($orderIds);

        $request->validate([
            'note' => 'nullable|string|max:500',
        ]);

        if (empty($orderIds)) {
            return response()->json([
                'success' => false,
                'message' => 'Không có đơn hàng nào được chọn.',
            ], 400);
        }

        DB::beginTransaction();
        try {
            $orders = Order::whereIn('id', $orderIds)
                ->where('status', 'picked_up')
                ->get();

            if ($orders->count() !== count($orderIds)) {
                throw new \Exception('Một số đơn hàng không hợp lệ hoặc chưa được lấy.');
            }

            foreach ($orders as $order) {
                $order->update([
                    'status' => 'at_hub',
                    'current_hub_id' => $request->post_office_id ?: $order->current_hub_id,
                    'hub_transfer_time' => now(),
                    'hub_transfer_note' => $request->note,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Đã chuyển ' . $orders->count() . ' đơn hàng về bưu cục.',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Lấy đơn hàng đã lấy trong ngày (để chuẩn bị về bưu cục)
     */
    public function pickedOrders()
    {
        $orders = Order::where('pickup_driver_id', Auth::id())
            ->where('status', 'picked_up')
            ->whereDate('actual_pickup_time', today())
            ->with('products')
            ->orderBy('actual_pickup_time', 'desc')
            ->get();

        return view('driver.pickup.picked-orders', compact('orders'));
    }

    /**
     * Lấy bưu cục tài xế
     */
    public function location()
    {
        try {
            $driver = Auth::user();

            $profile = DriverProfile::where('user_id', $driver->id)
                ->select('post_office_name', 'post_office_address', 'post_office_lat', 'post_office_lng')
                ->first();

            if (!$profile) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy hồ sơ tài xế'
                ], 404);
            }

            if (empty($profile->post_office_lat) || empty($profile->post_office_lng)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chưa có thông tin tọa độ bưu cục'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $profile->post_office_id,
                    'name' => $profile->post_office_name,
                    'address' => $profile->post_office_address,
                    'latitude' => $profile->post_office_lat,
                    'longitude' => $profile->post_office_lng,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy ảnh pickup của đơn hàng
     */
    public function getImages($orderId)
    {
        try {
            $order = Order::findOrFail($orderId);

            $images = OrderImage::where('order_id', $orderId)
                ->where('type', 'pickup')
                ->get()
                ->map(function($image) {
                    return [
                        'id' => $image->id,
                        'image_path' => $image->image_path,
                        'image_url' => Storage::url($image->image_path),
                        'note' => $image->note,
                        'created_at' => $image->created_at->format('H:i d/m/Y'),
                    ];
                });

            return response()->json([
                'success' => true,
                'images' => $images
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 404);
        }
    }
}