<?php

namespace App\Http\Controllers\Drivers;

use App\Http\Controllers\Controller;
use App\Models\Customer\Dashboard\Orders\CodTransaction;
use App\Models\Customer\Dashboard\Orders\Order;
use App\Models\Driver\DriverProfile;
use App\Models\Driver\Orders\OrderDelivery;
use App\Models\Driver\Orders\OrderDeliveryImage;
use App\Models\Driver\Orders\OrderDeliveryIssue;
use App\Models\Driver\Orders\OrderReturn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class DriverDeliveryController extends Controller
{
    /**
     * Danh sách đơn hàng cần giao (đã về hub hoặc đang giao)
     */
    public function index(Request $request)
    {
        $status = $request->get('status', 'all');
        $search = $request->get('search');
        $hubId = DriverProfile::where('user_id', operator: Auth::id())->value('post_office_id');
        if (!$hubId) {
            return redirect()->back()->with('error', 'Chưa có thông tin bưu cục. Vui lòng cập nhật hồ sơ tài xế.');
        }
        $orders = Order::query()
            ->whereIn('status', [Order::STATUS_AT_HUB, Order::STATUS_SHIPPING])
            ->where('driver_id', Auth::id())
            ->where('has_return', 0)
            ->when($status !== 'all', function ($q) use ($status) {
                $q->where('status', $status);
            })
            ->when($search, function ($q) use ($search) {
                $q->where(function ($query) use ($search) {
                    $query->where('id', 'like', "%{$search}%")
                        ->orWhere('recipient_name', 'like', "%{$search}%")
                        ->orWhere('recipient_phone', 'like', "%{$search}%");
                });
            })
            ->with(['orderGroup', 'delivery.images', 'delivery.issues'])
            ->orderBy('delivery_time', 'asc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('driver.delivery.index', compact('orders', 'status', 'search'));
    }

    /**
     * Chi tiết đơn hàng cần giao
     */
    public function show($id)
    {
        $order = Order::with([
            'orderGroup',
            'products',
            'delivery.images',
            'delivery.issues',
            'delivery.driver'
        ])->findOrFail($id);

        // ✅ SỬA: Kiểm tra xem tài xế có phải là người được phân giao không
        if ($order->driver_id !== Auth::id()) {
            return redirect()->route('driver.delivery.index')
                ->with('error', "Bạn không có quyền truy cập đơn hàng này");
        }

        // Kiểm tra trạng thái
        if (!in_array($order->status, [Order::STATUS_AT_HUB, Order::STATUS_SHIPPING])) {
            return redirect()->route('driver.delivery.index')
                ->with('error', 'Đơn hàng này không ở trạng thái cần giao.');
        }

        return view('driver.delivery.show', compact('order'));
    }

    /**
     * Bắt đầu giao hàng
     */
    public function startDelivery(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        // Validate
        if ($order->status !== Order::STATUS_AT_HUB) {
            return back()->with('error', 'Chỉ có thể bắt đầu giao hàng với đơn hàng đang ở bưu cục.');
        }

        try {
            DB::beginTransaction();

            // Cập nhật trạng thái order
            $order->update([
                'status' => Order::STATUS_SHIPPING,
            ]);

            // ✅ SỬA: Tạo lần thử mới thay vì create trực tiếp
            OrderDelivery::createNewAttempt($order->id, auth()->id());

            // Cập nhật trạng thái group nếu có
            if ($order->isPartOfGroup()) {
                $order->orderGroup->updateGroupStatus();
            }

            DB::commit();

            return redirect()->route('driver.delivery.form', $order->id)
                ->with('success', 'Đã bắt đầu giao hàng đơn #' . $order->id);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    /**
     * Form giao hàng thành công
     */
    public function deliveryForm($id)
    {
        $order = Order::with(['orderGroup', 'products', 'delivery'])
            ->findOrFail($id);

        if ($order->status !== Order::STATUS_SHIPPING) {
            return redirect()->route('driver.delivery.index')
                ->with('error', 'Đơn hàng này không ở trạng thái đang giao.');
        }

        return view('driver.delivery.form', compact('order'));
    }

    /**
     * Xử lý giao hàng thành công
     */
    public function completeDelivery(Request $request, $id)
    {
        $order = Order::with('delivery')->findOrFail($id);

        // Validate trạng thái
        if ($order->status !== Order::STATUS_SHIPPING) {
            return back()
                ->with('error', 'Đơn hàng không ở trạng thái đang giao.')
                ->with('alert_type', 'error');
        }

        // Validate dữ liệu
        $validator = Validator::make($request->all(), [
            'received_by_name' => 'required|string|max:255',
            'received_by_phone' => 'required|string|max:20',
            'received_by_relation' => 'required|in:self,family,neighbor,security,other',
            'delivery_note' => 'nullable|string|max:1000',
            'images' => 'required|array|min:1',
            'images.*' => 'required|image|mimes:jpeg,png,jpg|max:5120',
            'image_types' => 'required|array',
            'image_types.*' => 'required|in:delivery_proof,recipient_signature,package_condition,location_proof',
            'image_notes' => 'nullable|array',
            'image_notes.*' => 'nullable|string|max:500',
        ], [
            'received_by_name.required' => 'Vui lòng nhập tên người nhận hàng',
            'received_by_phone.required' => 'Vui lòng nhập số điện thoại người nhận',
            'received_by_relation.required' => 'Vui lòng chọn mối quan hệ với người nhận',
            'images.required' => 'Vui lòng chụp ít nhất 1 ảnh chứng từ giao hàng',
            'images.min' => 'Vui lòng chụp ít nhất 1 ảnh chứng từ',
            'images.*.image' => 'File phải là ảnh (JPG, PNG)',
            'images.*.max' => 'Kích thước ảnh tối đa 5MB',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Vui lòng kiểm tra lại thông tin!')
                ->with('alert_type', 'error');
        }

        try {
            DB::beginTransaction();

            // Xử lý COD nếu có
            $codCollected = 0;
            $paymentDetails = $order->payment_details;

            if ($paymentDetails['has_cod'] && $paymentDetails['payer'] === 'recipient') {
                $codCollected = $paymentDetails['recipient_pays'];
            }

            // ✅ SỬA: Lấy lần thử mới nhất
            $delivery = OrderDelivery::getLatestAttempt($order->id);

            if (!$delivery) {
                // Fallback: tạo mới nếu chưa có
                $delivery = OrderDelivery::createNewAttempt($order->id, auth()->id());
            }

            // ✅ THÊM: Đánh dấu giao hàng thành công
            $delivery->update([
                'actual_delivery_time' => now(),
                'is_successful' => true, // ✅ QUAN TRỌNG
                'received_by_name' => $request->received_by_name,
                'received_by_phone' => $request->received_by_phone,
                'received_by_relation' => $request->received_by_relation,
                'delivery_note' => $request->delivery_note,
                'cod_collected_amount' => $codCollected ?? 0,
                'cod_collected_at' => $codCollected > 0 ? now() : null,
            ]);

            // Cập nhật trạng thái order
            $order->update([
                'status' => Order::STATUS_DELIVERED,
            ]);

            if (!$order->codTransaction) {
                CodTransaction::createFromOrder($order);
            }

            // Lưu ảnh vào bảng order_delivery_images
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $index => $image) {
                    $path = $image->store('delivery_images/' . date('Y/m'), 'public');

                    OrderDeliveryImage::create([
                        'order_id' => $order->id,
                        'image_path' => $path,
                        'type' => $request->image_types[$index] ?? OrderDeliveryImage::TYPE_DELIVERY_PROOF,
                        'note' => $request->image_notes[$index] ?? null,
                    ]);
                }
            }

            // Cập nhật trạng thái group
            if ($order->isPartOfGroup()) {
                $order->orderGroup->updateGroupStatus();
            }

            DB::commit();

            // Tạo thông báo thành công
            $successMessage = 'Đã giao hàng thành công đơn #' . $order->id;
            if ($codCollected > 0) {
                $successMessage .= '<br><strong>Đã thu COD: ' . number_format($codCollected) . ' đ</strong>';
            }

            return redirect()->route('driver.delivery.index')
                ->with('success', $successMessage)
                ->with('alert_type', 'success')
                ->with('alert_title', 'Giao hàng thành công!');

        } catch (\Exception $e) {
            DB::rollBack();
            dd($e);
            return back()
                ->withInput()
                ->with('error', 'Có lỗi xảy ra khi lưu thông tin giao hàng. Vui lòng thử lại!')
                ->with('alert_type', 'error')
                ->with('alert_title', '❌ Lỗi hệ thống');
        }
    }

    /**
     * Form báo cáo giao hàng thất bại
     */
    public function failureForm($id)
    {
        $order = Order::with(['orderGroup', 'products', 'delivery'])
            ->findOrFail($id);

        if ($order->status !== Order::STATUS_SHIPPING) {
            return redirect()->route('driver.delivery.index')
                ->with('error', 'Đơn hàng này không ở trạng thái đang giao.')
                ->with('alert_type', 'error');
        }

        // Danh sách lý do giao hàng thất bại
        $issueTypes = [
            'recipient_not_home' => 'Người nhận không có nhà',
            'wrong_address' => 'Địa chỉ sai/không tìm thấy',
            'refused_package' => 'Người nhận từ chối nhận hàng',
            'unable_to_contact' => 'Không liên lạc được với người nhận',
            'address_too_far' => 'Địa chỉ quá xa',
            'dangerous_area' => 'Khu vực nguy hiểm',
            'other' => 'Lý do khác',
        ];

        return view('driver.delivery.failure', compact('order', 'issueTypes'));
    }

    /**
     * Xử lý giao hàng thất bại
     */
    public function reportFailure(Request $request, $orderId)
    {
        $validator = Validator::make($request->all(), [
            'issue_type' => 'required|in:recipient_not_home,unable_to_contact,wrong_address,refused_package,address_too_far,dangerous_area,other',
            'issue_note' => 'required|string|max:1000',
            'images' => 'nullable|array',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'image_notes' => 'nullable|array',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ], [
            'issue_type.required' => 'Vui lòng chọn lý do giao hàng thất bại',
            'issue_note.required' => 'Vui lòng mô tả chi tiết lý do',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()
                ->with('error', 'Vui lòng kiểm tra lại thông tin!');
        }

        DB::beginTransaction();
        try {
            $order = Order::with('delivery')->findOrFail($orderId);

            if ($order->driver_id !== Auth::id()) {
                throw new \Exception('Bạn không có quyền báo cáo cho đơn hàng này');
            }

            // ✅ KIỂM TRA: Đơn đã giao thành công thì không được báo thất bại
            if ($order->status === Order::STATUS_DELIVERED) {
                throw new \Exception('Đơn hàng đã được giao thành công, không thể báo cáo thất bại');
            }

            // ✅ Tăng số lần thử giao
            $attemptCount = ($order->delivery_attempt_count ?? 0) + 1;
            $order->update([
                'delivery_attempt_count' => $attemptCount,
                'status' => Order::STATUS_AT_HUB, // ✅ Đưa về hub
            ]);

            // ✅ Tạo OrderDeliveryIssue
            $issue = OrderDeliveryIssue::create([
                'order_id' => $order->id,
                'issue_type' => $request->issue_type,
                'issue_note' => $request->issue_note,
                'issue_time' => now(),
                'reported_by' => Auth::id(),
                'issue_latitude' => $request->latitude,
                'issue_longitude' => $request->longitude,
                'resolution_action' => OrderDeliveryIssue::ACTION_PENDING,
            ]);

            // ✅ Lưu ảnh chứng từ (nếu có)
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $index => $image) {
                    $path = $image->store('delivery_issues/' . date('Y/m'), 'public');

                    OrderDeliveryImage::create([
                        'order_id' => $order->id,
                        'image_path' => $path,
                        'type' => OrderDeliveryImage::TYPE_DELIVERY_PROOF,
                        'note' => $request->image_notes[$index] ?? null,
                        'order_index' => $index,
                    ]);
                }
            }

            // ✅ LOGIC TỰ ĐỘNG HOÀN HÀNG
            $isInnerCity = $this->isInnerCityOrder($order);
            $shouldAutoReturn = false;
            $returnReason = '';

            if ($isInnerCity && $attemptCount >= 1) {
                // ✅ Đơn nội thành: 1 lần thất bại = hoàn về
                $shouldAutoReturn = true;
                $returnReason = "Đơn nội thành giao thất bại {$attemptCount} lần - Tự động hoàn về";
            } elseif (!$isInnerCity && $attemptCount >= 3) {
                // ✅ Đơn ngoại thành: 3 lần thất bại = hoàn về
                $shouldAutoReturn = true;
                $returnReason = "Đơn ngoại thành giao thất bại {$attemptCount} lần - Tự động hoàn về";
            }

            if ($shouldAutoReturn) {
                // ✅ Tạo đơn hoàn tự động
                $orderReturn = OrderReturn::createFromOrder(
                    $order,
                    OrderReturn::REASON_AUTO_FAILED,
                    $returnReason,
                    Auth::id()
                );

                // ✅ THÊM: Tự động gán tài xế hiện tại (bỏ qua hub duyệt)
                $orderReturn->assignDriver(Auth::id(), Auth::id());

                // ✅ Cập nhật issue resolution
                $issue->update([
                    'resolution_action' => OrderDeliveryIssue::ACTION_RETURN,
                    'order_return_id' => $orderReturn->id,
                    'resolved_by' => Auth::id(),
                    'resolved_at' => now(),
                    'resolution_note' => $returnReason,
                ]);

                DB::commit();

                $alertMessage = $isInnerCity
                    ? "Đơn nội thành giao thất bại. Đã tự động chuyển sang hoàn hàng."
                    : "Đơn hàng đã giao thất bại {$attemptCount} lần. Đã tự động chuyển sang hoàn hàng.";

                return redirect()->route('driver.delivery.index')
                    ->with('warning', $alertMessage)
                    ->with('alert_type', 'warning')
                    ->with('alert_title', '⚠️ Đã chuyển sang hoàn hàng');
            }

            DB::commit();

            $message = "Đã báo cáo giao hàng thất bại lần {$attemptCount}.<br>";
            $message .= $isInnerCity
                ? "Lưu ý: Đơn nội thành sẽ tự động hoàn về nếu thất bại."
                : "Lưu ý: Đơn sẽ tự động hoàn về sau 3 lần thất bại.";

            return redirect()->route('driver.delivery.index')
                ->with('success', $message)
                ->with('alert_type', 'info')
                ->with('alert_title', '📋 Đã ghi nhận thất bại');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Report failure error: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    /**
     * Tính khoảng cách giữa 2 điểm (km) - Haversine formula
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        if (!$lat1 || !$lon1 || !$lat2 || !$lon2) {
            return 0;
        }

        $earthRadius = 6371; // km
        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lon1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($lon2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return $angle * $earthRadius;
    }
    /**
     * ✅ THÊM PHƯƠNG THỨC: Khởi tạo hoàn hàng từ form giao hàng
     */
    public function initiateReturn($id)
    {
        $order = Order::with(['deliveryIssues'])->findOrFail($id);

        // Validate trạng thái
        if (!in_array($order->status, [Order::STATUS_SHIPPING])) {
            return back()->with('error', 'Chỉ có thể khởi tạo hoàn hàng khi đang giao.');
        }

        // Kiểm tra đã có OrderReturn chưa
        if ($order->has_return) {
            return redirect()->route('driver.returns.show', $order->activeReturn->id)
                ->with('info', 'Đơn hàng này đã có yêu cầu hoàn trước đó');
        }

        try {
            DB::beginTransaction();

            // Lấy issue gần nhất (nếu có)
            $latestIssue = $order->deliveryIssues()->latest('issue_time')->first();

            $reasonType = $latestIssue
                ? OrderReturn::REASON_HUB_DECISION
                : OrderReturn::REASON_OTHER;

            $reasonDetail = $latestIssue
                ? "Giao hàng thất bại: {$latestIssue->issue_type_label}. {$latestIssue->issue_note}"
                : "Tài xế quyết định hoàn hàng";

            // ✅ CHỈ TẠO OrderReturn, KHÔNG TỰ ASSIGN
            $return = OrderReturn::createFromOrder(
                $order,
                $reasonType,
                $reasonDetail,
                Auth::id()
            );

            // ✅ Đơn về hub, chờ Hub phân công
            $order->update([
                'status' => Order::STATUS_AT_HUB,
            ]);

            DB::commit();

            // ✅ Thông báo cho tài xế biết đơn đã được chuyển về hub
            return redirect()->route('driver.delivery.index')
                ->with('success', 'Đã khởi tạo hoàn hàng thành công. Đơn đã được chuyển về hub để phân công.')
                ->with('alert_type', 'success');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Có lỗi: ' . $e->getMessage());
        }
    }
    /**
     * ✅ KIỂM TRA ĐƠN NỘI THÀNH HAY NGOẠI THÀNH
     */
    private function isInnerCityOrder(Order $order)
    {
        // ✅ Kiểm tra cột is_inner_city nếu có
        if ($order->is_inner_city !== null) {
            return $order->is_inner_city;
        }

        // ✅ Lấy từ GPS hoặc district_code
        $districtToCheck = null;

        if ($order->recipient_latitude && $order->recipient_longitude) {
            $districtToCheck = $this->getDistrictFromCoordinates(
                $order->recipient_latitude,
                $order->recipient_longitude
            );
        }

        if (!$districtToCheck && $order->district_code) {
            $districtToCheck = $order->district_code;
        }

        return $this->isInnerHanoiByDistrict($districtToCheck);
    }

    /**
     * ✅ KIỂM TRA QUẬN CÓ PHẢI NỘI THÀNH KHÔNG
     */
    private function isInnerHanoiByDistrict($districtCode)
    {
        if (!$districtCode) {
            return false;
        }

        $innerDistrictCodes = [
            '001',
            '002',
            '003',
            '004',
            '005',
            '006',
            '007',
            '008',
            '009',
            '016',
            '017',
            '019'
        ];

        $innerDistrictNames = [
            'Ba Đình',
            'Ba Dinh',
            'Hoàn Kiếm',
            'Hoan Kiem',
            'Tây Hồ',
            'Tay Ho',
            'Long Biên',
            'Long Bien',
            'Cầu Giấy',
            'Cau Giay',
            'Đống Đa',
            'Dong Da',
            'Hai Bà Trưng',
            'Hai Ba Trung',
            'Hoàng Mai',
            'Hoang Mai',
            'Thanh Xuân',
            'Thanh Xuan',
            'Nam Từ Liêm',
            'Nam Tu Liem',
            'Bắc Từ Liêm',
            'Bac Tu Liem',
            'Hà Đông',
            'Ha Dong',
        ];

        $normalized = trim($districtCode);

        if (is_numeric($normalized)) {
            $paddedCode = str_pad($normalized, 3, '0', STR_PAD_LEFT);
            return in_array($paddedCode, $innerDistrictCodes);
        }

        $cleanName = str_replace(['Quận ', 'quận '], '', $normalized);

        foreach ($innerDistrictNames as $districtName) {
            if (
                strcasecmp($cleanName, $districtName) === 0 ||
                stripos($cleanName, $districtName) !== false
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * ✅ LẤY QUẬN TỪ GPS
     */
    private function getDistrictFromCoordinates($latitude, $longitude)
    {
        try {
            $apiKey = config("services.goong.api_key");
            if (!$apiKey)
                return null;

            $cacheKey = "goong_district_" . round($latitude, 4) . "_" . round($longitude, 4);

            if (\Cache::has($cacheKey)) {
                return \Cache::get($cacheKey);
            }

            $url = "https://rsapi.goong.io/Geocode?latlng={$latitude},{$longitude}&api_key={$apiKey}";
            $response = \Http::timeout(10)->get($url);

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['results'][0]['compound']['district'])) {
                    $district = $data['results'][0]['compound']['district'];
                    \Cache::put($cacheKey, $district, now()->addHours(24));
                    return $district;
                }
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }
}