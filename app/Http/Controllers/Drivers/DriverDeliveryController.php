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
use Illuminate\Support\Facades\Auth;

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
        
        if($order->pickup_driver_id === Auth::id()){
            return redirect()->route('driver.delivery.index')->with('error',"Bạn không có quyền truy cập trang này");
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
            return back()
                ->with('error', 'Đơn hàng không ở trạng thái đang giao.')
                ->with('alert_type', 'error');
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
            'delivery_latitude.required' => 'Vui lòng lấy vị trí GPS hiện tại',
            'delivery_longitude.required' => 'Vui lòng lấy vị trí GPS hiện tại',
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

            // Kiểm tra tọa độ và tính khoảng cách
            $distanceWarning = null;
            if ($order->recipient_latitude && $order->recipient_longitude) {
                $distance = $this->calculateDistance(
                    $order->recipient_latitude,
                    $order->recipient_longitude,
                    $request->delivery_latitude,
                    $request->delivery_longitude
                );

                // Cảnh báo nếu > 2km
                if ($distance > 2) {
                    $distanceWarning = 'Vị trí giao hàng cách địa chỉ người nhận ' . round($distance, 2) . ' km';
                }

                // Chặn nếu > 10km (rõ ràng sai)
                if ($distance > 10) {
                    DB::rollBack();
                    return back()
                        ->withInput()
                        ->with('error', 'Vị trí giao hàng quá xa địa chỉ người nhận (' . round($distance, 2) . ' km). Vui lòng kiểm tra lại GPS!')
                        ->with('alert_type', 'error');
                }
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

            // Tạo thông báo thành công
            $successMessage = 'Đã giao hàng thành công đơn #' . $order->id;
            if ($codCollected > 0) {
                $successMessage .= '<br><strong>Đã thu COD: ' . number_format($codCollected) . ' đ</strong>';
            }
            if ($distanceWarning) {
                $successMessage .= '<br><small class="text-warning">⚠️ ' . $distanceWarning . '</small>';
            }

            return redirect()->route('driver.delivery.index')
                ->with('success', $successMessage)
                ->with('alert_type', 'success')
                ->with('alert_title', '✅ Giao hàng thành công!');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Delivery completion error', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
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
            'issue_latitude' => 'required|numeric',
            'issue_longitude' => 'required|numeric',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:5120',
            'image_notes' => 'nullable|array',
        ], [
            'issue_type.required' => 'Vui lòng chọn lý do giao hàng thất bại',
            'issue_note.required' => 'Vui lòng mô tả chi tiết lý do',
            'issue_latitude.required' => 'Vui lòng lấy vị trí GPS hiện tại',
            'issue_longitude.required' => 'Vui lòng lấy vị trí GPS hiện tại',
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

            return redirect()->route('driver.delivery.index')
                ->with('warning', 'Đã ghi nhận giao hàng thất bại đơn #' . $order->id . '<br>Lý do: ' . $issueLabels[$request->issue_type] . '<br>Đơn hàng đã được chuyển về bưu cục.')
                ->with('alert_type', 'warning')
                ->with('alert_title', '⚠️ Giao hàng thất bại');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Delivery failure report error', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
            
            return back()
                ->withInput()
                ->with('error', 'Có lỗi xảy ra khi ghi nhận thất bại. Vui lòng thử lại!')
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
}