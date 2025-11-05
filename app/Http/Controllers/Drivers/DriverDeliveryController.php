<?php

namespace App\Http\Controllers\Drivers;

use App\Http\Controllers\Controller;
use App\Models\Customer\Dashboard\Orders\Order;
use App\Models\Driver\Orders\OrderDelivery;
use App\Models\Driver\Orders\OrderDeliveryImage;
use App\Models\Driver\Orders\OrderDeliveryIssue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DriverDeliveryController extends Controller
{
    /**
     * Danh sách đơn hàng cần giao (đã về hub hoặc đang giao)
     */
    public function index(Request $request)
    {
        $status = $request->get('status', 'all');
        $search = $request->get('search');

        $orders = Order::query()
            ->whereIn('status', [Order::STATUS_AT_HUB, Order::STATUS_SHIPPING])
            ->when($status !== 'all', function($q) use ($status) {
                $q->where('status', $status);
            })
            ->when($search, function($q) use ($search) {
                $q->where(function($query) use ($search) {
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

            // Tạo bản ghi delivery
            OrderDelivery::create([
                'order_id' => $order->id,
                'delivery_driver_id' => auth()->id(),
                'actual_delivery_start_time' => now(),
            ]);

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
            return back()->with('error', 'Đơn hàng không ở trạng thái đang giao.');
        }

        // Validate dữ liệu
        $validator = Validator::make($request->all(), [
            'delivery_latitude' => 'required|numeric',
            'delivery_longitude' => 'required|numeric',
            'delivery_address' => 'nullable|string|max:500',
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
            'images.required' => 'Vui lòng chụp ít nhất 1 ảnh chứng từ giao hàng',
            'images.min' => 'Vui lòng chụp ít nhất 1 ảnh',
            'images.*.image' => 'File phải là ảnh',
            'images.*.max' => 'Kích thước ảnh tối đa 5MB',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            // Tính toán khoảng cách giao hàng
            $distance = $this->calculateDistance(
                $order->recipient_latitude,
                $order->recipient_longitude,
                $request->delivery_latitude,
                $request->delivery_longitude
            );

            // Cảnh báo nếu giao hàng quá xa địa chỉ người nhận (> 500m)
            if ($distance > 0.5) {
                DB::rollBack();
                return back()
                    ->withInput()
                    ->with('warning', 'Vị trí giao hàng cách địa chỉ người nhận ' . round($distance, 2) . ' km. Vui lòng kiểm tra lại.');
            }

            // Xử lý COD nếu có
            $codCollected = 0;
            $paymentDetails = $order->payment_details;
            
            if ($paymentDetails['has_cod'] && $paymentDetails['payer'] === 'recipient') {
                $codCollected = $paymentDetails['recipient_pays'];
            }

            // Cập nhật bản ghi delivery
            $delivery = $order->delivery;
            if (!$delivery) {
                // Tạo mới nếu chưa có (fallback)
                $delivery = OrderDelivery::create([
                    'order_id' => $order->id,
                    'delivery_driver_id' => auth()->id(),
                    'actual_delivery_start_time' => now(),
                ]);
            }

            $delivery->update([
                'actual_delivery_time' => now(),
                'delivery_latitude' => $request->delivery_latitude,
                'delivery_longitude' => $request->delivery_longitude,
                'delivery_address' => $request->delivery_address,
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

            return redirect()->route('driver.delivery.index')
                ->with('success', 'Đã giao hàng thành công đơn #' . $order->id . 
                    ($codCollected > 0 ? ' - Đã thu COD: ' . number_format($codCollected) . ' đ' : ''));

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
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
                ->with('error', 'Đơn hàng này không ở trạng thái đang giao.');
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
            'issue_latitude' => 'required|numeric',
            'issue_longitude' => 'required|numeric',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:5120',
            'image_notes' => 'nullable|array',
        ], [
            'issue_type.required' => 'Vui lòng chọn lý do giao hàng thất bại',
            'issue_note.required' => 'Vui lòng mô tả chi tiết lý do',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            // Tạo bản ghi issue
            OrderDeliveryIssue::create([
                'order_id' => $order->id,
                'issue_type' => $request->issue_type,
                'issue_note' => $request->issue_note,
                'issue_time' => now(),
                'reported_by' => auth()->id(),
                'issue_latitude' => $request->issue_latitude,
                'issue_longitude' => $request->issue_longitude,
            ]);

            // Cập nhật trạng thái order về hub
            $order->update([
                'status' => Order::STATUS_AT_HUB,
            ]);

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
                ->with('info', 'Đã ghi nhận giao hàng thất bại đơn #' . $order->id . '. Đơn hàng đã được chuyển về bưu cục.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
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
}