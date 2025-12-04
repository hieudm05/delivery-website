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
                'cod_collected_amount' => $codCollected,
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
    public function reportFailure(Request $request, $id)
    {
        $order = Order::with('delivery')->findOrFail($id);

        // Validate
        $validator = Validator::make($request->all(), [
            'issue_type' => 'required|in:recipient_not_home,wrong_address,refused_package,unable_to_contact,address_too_far,dangerous_area,other',
            'issue_note' => 'required|string|max:1000',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:5120',
            'image_notes' => 'nullable|array',
        ], [
            'issue_type.required' => 'Vui lòng chọn lý do giao hàng thất bại',
            'issue_note.required' => 'Vui lòng mô tả chi tiết lý do',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Vui lòng kiểm tra lại thông tin!')
                ->with('alert_type', 'error');
        }

        try {
            // DB::beginTransaction();

            // ✅ THÊM: Đánh dấu lần thử hiện tại là thất bại
            $delivery = OrderDelivery::getLatestAttempt($order->id);
            if ($delivery) {
                $delivery->update([
                    'is_successful' => false,
                    'actual_delivery_time' => now(),
                ]);
            }

            // ✅ SỬA: Tăng delivery_attempt_count TRƯỚC khi tạo issue
            $order->increment('delivery_attempt_count');
            $attemptCount = $order->delivery_attempt_count;

            // Lấy label lý do
            $issueLabels = [
                'recipient_not_home' => 'Người nhận không có nhà',
                'wrong_address' => 'Địa chỉ sai/không tìm thấy',
                'refused_package' => 'Người nhận từ chối nhận hàng',
                'unable_to_contact' => 'Không liên lạc được',
                'address_too_far' => 'Địa chỉ quá xa',
                'dangerous_area' => 'Khu vực nguy hiểm',
                'other' => 'Lý do khác',
            ];

            // ✅ KIỂM TRA: Nếu đã thất bại 3 lần → TẠO HOÀN HÀNG TỰ ĐỘNG
            if ($attemptCount >= 3) {
                // Tạo bản ghi issue
                $issue = OrderDeliveryIssue::create([
                    'order_id' => $order->id,
                    'issue_type' => $request->issue_type,
                    'issue_note' => $request->issue_note,
                    'issue_time' => now(),
                    'reported_by' => Auth::id(),
                    'resolution_action' => OrderDeliveryIssue::ACTION_RETURN, // ✅ Tự động hoàn
                ]);

                // ✅ TẠO HOÀN HÀNG TỰ ĐỘNG
                $orderReturn = OrderReturn::createFromOrder(
                    $order,
                    OrderReturn::REASON_AUTO_FAILED,
                    "Tự động hoàn hàng do giao thất bại {$attemptCount} lần. Lần cuối: {$issueLabels[$request->issue_type]}. Chi tiết: {$request->issue_note}",
                    auth()->id()
                );

                // ✅ Cập nhật issue với order_return_id
                $issue->update(['order_return_id' => $orderReturn->id]);

                // ✅ Cập nhật trạng thái order
                $order->update([
                    'status' => Order::STATUS_RETURNING, // Trạng thái hoàn hàng
                ]);

                $warningMessage = '🔴 <strong>Đơn hàng #' . $order->id . ' đã thất bại 3 lần!</strong><br>' .
                    'Hệ thống đã TỰ ĐỘNG tạo lệnh hoàn hàng.<br>' .
                    'Lý do lần cuối: ' . $issueLabels[$request->issue_type] . '<br>' .
                    'Vui lòng mang đơn hàng về bưu cục để hoàn trả.';

                $alertType = 'error';
                $alertTitle = '🔴 Tự động hoàn hàng';

            } else {
                // ✅ Chưa đủ 3 lần → Tạo issue bình thường
                OrderDeliveryIssue::create([
                    'order_id' => $order->id,
                    'issue_type' => $request->issue_type,
                    'issue_note' => $request->issue_note,
                    'issue_time' => now(),
                    'reported_by' => auth()->id(),
                    'resolution_action' => OrderDeliveryIssue::ACTION_PENDING,
                ]);

                // Cập nhật trạng thái order về hub
                $order->update([
                    'status' => Order::STATUS_AT_HUB,
                ]);

                $warningMessage = 'Đã ghi nhận giao hàng thất bại đơn #' . $order->id . ' (Lần ' . $attemptCount . '/3)<br>' .
                    'Lý do: ' . $issueLabels[$request->issue_type] . '<br>' .
                    'Đơn hàng đã được chuyển về bưu cục để thử lại.';

                $alertType = 'warning';
                $alertTitle = '⚠️ Giao hàng thất bại';
            }

            // Lưu ảnh nếu có
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $index => $image) {
                    $path = $image->store('delivery_failure/' . date('Y/m'), 'public');

                    OrderDeliveryImage::create([
                        'order_id' => $order->id,
                        'image_path' => $path,
                        'type' => OrderDeliveryImage::TYPE_DELIVERY_PROOF,
                        'note' => $request->image_notes[$index] ?? 'Ảnh giao hàng thất bại - ' . $request->issue_type,
                    ]);
                }
            }

            // Cập nhật group status
            if ($order->isPartOfGroup()) {
                $order->orderGroup->updateGroupStatus();
            }

            DB::commit();

            return redirect()->route('driver.delivery.index')
                ->with('warning', $warningMessage)
                ->with('alert_type', $alertType)
                ->with('alert_title', $alertTitle);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Có lỗi xảy ra: ' . $e->getMessage())
                ->with('alert_type', 'error');
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
}