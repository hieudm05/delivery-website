<?php

namespace App\Http\Controllers\Customer\Dashboard\OrderManagent;

use App\Http\Controllers\Controller;
use App\Models\Customer\Dashboard\Orders\Order;
use App\Models\Hub\Hub;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class OrderManagentController extends Controller
{
    /**
     * ✅ Danh sách đơn hàng với phân trang và filter
     */
    public function index(Request $request)
    {
        try {
            $status = $request->query('status', 'all');
            $search = $request->query('search', '');

            // Validate status - Thêm 'failed' vào danh sách hợp lệ
            $validStatuses = array_merge(Order::STATUSES, ['failed']);
            if ($status !== 'all' && !in_array($status, $validStatuses)) {
                $status = 'all';
            }

            $query = Order::where('sender_id', Auth::id())
                ->with(['products', 'images', 'orderGroup', 'deliveryIssues', 'delivery'])
                ->when($status === 'failed', function ($q) {
                    // Lấy đơn có vấn đề giao hàng hoặc đã hủy do giao thất bại
                    return $q->where(function ($query) {
                        $query->whereHas('deliveryIssues')
                            ->orWhere('status', Order::STATUS_CANCELLED);
                    });
                })
                ->when($status !== 'all' && $status !== 'failed', function ($q) use ($status) {
                    return $q->withStatus($status);
                })
                ->search($search)
                ->latest();

            $orders = $query->paginate(12)->appends([
                'status' => $status,
                'search' => $search
            ]);

            // ✅ Xử lý AJAX request
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'html' => view('customer.dashboard.orderManagent._orders_list', compact('orders'))->render(),
                    'pagination' => $orders->links()->render()
                ]);
            }

            // ✅ Đếm số lượng theo từng trạng thái
            $statusCounts = $this->getStatusCounts();

            return view('customer.dashboard.orderManagent.index', compact('orders', 'statusCounts'));

        } catch (\Exception $e) {
            Log::error('Lỗi trong OrderManagentController::index: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Có lỗi xảy ra, vui lòng thử lại sau.'
                ], 500);
            }

            return back()->with('error', 'Có lỗi xảy ra, vui lòng thử lại sau.');
        }
    }

    /**
     * ✅ Chi tiết đơn hàng
     */
    public function show($id)
    {
        try {
            $order = Order::where('sender_id', Auth::id())
                ->with([
                    'products',
                    'images',
                    'deliveryImages',
                    'orderGroup',
                    'deliveryIssues.reporter',
                    'delivery.driver'
                ])
                ->findOrFail($id);

            // Lấy tọa độ để hiển thị trên map
            $mapData = $this->prepareMapData($order);
            return view('customer.dashboard.orderManagent.show', compact('order', 'mapData'));

        } catch (\Exception $e) {
            Log::error('Lỗi trong OrderManagentController::show: ' . $e->getMessage());
            return back()->with('error', 'Không tìm thấy đơn hàng.');
        }
    }

    /**
     * ✅ Sửa đơn hàng (chỉ khi có thể edit)
     */
    public function edit($id)
    {
        try {
            $order = Order::where('sender_id', Auth::id())
                ->findOrFail($id);

            if (!$order->canEdit()) {
                return back()->with('error', 'Không thể chỉnh sửa đơn hàng ở trạng thái hiện tại.');
            }

            return view('customer.dashboard.orderManagent.edit', compact('order'));

        } catch (\Exception $e) {
            Log::error('Lỗi trong OrderManagentController::edit: ' . $e->getMessage());
            return back()->with('error', 'Không tìm thấy đơn hàng.');
        }
    }

    /**
     * ✅ Update đơn hàng
     */
    public function update(Request $request, $id)
    {
        try {
            $order = Order::where('sender_id', Auth::id())
                ->findOrFail($id);

            if (!$order->canEdit()) {
                return back()->with('error', 'Không thể chỉnh sửa đơn hàng ở trạng thái hiện tại.');
            }

            $validated = $request->validate([
                'recipient_name' => 'required|string|max:255',
                'recipient_phone' => 'required|string|regex:/^(0|\+84)[0-9]{9,10}$/',
                'recipient_full_address' => 'required|string|max:500',
                'delivery_time' => 'required|date|after:now',
                'note' => 'nullable|string|max:1000',
            ], [
                'recipient_name.required' => 'Vui lòng nhập tên người nhận',
                'recipient_phone.required' => 'Vui lòng nhập số điện thoại người nhận',
                'recipient_phone.regex' => 'Số điện thoại không hợp lệ',
                'recipient_full_address.required' => 'Vui lòng nhập địa chỉ đầy đủ',
                'delivery_time.required' => 'Vui lòng chọn thời gian giao hàng',
                'delivery_time.after' => 'Thời gian giao hàng phải sau thời điểm hiện tại',
            ]);

            $order->update($validated);

            return redirect()->route('customer.orderManagent.show', $order->id)
                ->with('success', 'Cập nhật đơn hàng thành công!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->validator)->withInput();
        } catch (\Exception $e) {
            Log::error('Lỗi trong OrderManagentController::update: ' . $e->getMessage());
            return back()->with('error', 'Có lỗi xảy ra, vui lòng thử lại sau.');
        }
    }

    /**
     * ✅ Hủy đơn hàng
     */
    public function cancel($id)
    {
        try {
            $order = Order::where('sender_id', Auth::id())
                ->findOrFail($id);

            if (!$order->canCancel()) {
                return back()->with('error', 'Không thể hủy đơn hàng ở trạng thái hiện tại.');
            }

            $order->update(['status' => Order::STATUS_CANCELLED]);

            // Cập nhật trạng thái order group nếu có
            if ($order->isPartOfGroup()) {
                $order->orderGroup->updateGroupStatus();
            }

            return back()->with('success', 'Đã hủy đơn hàng thành công!');

        } catch (\Exception $e) {
            Log::error('Lỗi trong OrderManagentController::cancel: ' . $e->getMessage());
            return back()->with('error', 'Có lỗi xảy ra, vui lòng thử lại sau.');
        }
    }

    /**
     * ✅ Xóa đơn hàng (chỉ khi status = pending)
     */
    public function destroy($id)
    {
        try {
            $order = Order::where('sender_id', Auth::id())
                ->where('status', Order::STATUS_PENDING)
                ->findOrFail($id);

            // Xóa ảnh liên quan
            foreach ($order->images as $image) {
                if (\Storage::disk('public')->exists($image->image_path)) {
                    \Storage::disk('public')->delete($image->image_path);
                }
                $image->delete();
            }

            // Xóa sản phẩm
            $order->products()->delete();

            $order->delete();

            return redirect()->route('customer.orderManagent.index')
                ->with('success', 'Đã xóa đơn hàng thành công!');

        } catch (\Exception $e) {
            Log::error('Lỗi trong OrderManagentController::destroy: ' . $e->getMessage());
            return back()->with('error', 'Không thể xóa đơn hàng này.');
        }
    }

    /**
     * ✅ API lấy ảnh giao hàng theo trạng thái (AJAX)
     */
    public function getDeliveryImages(Request $request, $orderId)
    {
        try {
            $order = Order::where('sender_id', Auth::id())->findOrFail($orderId);

            $images = $order->deliveryImages()
                ->when($request->status, function ($q) use ($request) {
                    return $q->where('status', $request->status);
                })
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'images' => $images->map(function ($img) {
                    return [
                        'id' => $img->id,
                        'url' => asset('storage/' . $img->image_path),
                        'note' => $img->note,
                        'location' => $img->location,
                        'created_at' => $img->created_at->format('H:i d/m/Y')
                    ];
                })
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi trong OrderManagentController::getDeliveryImages: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Không thể tải ảnh giao hàng.'
            ], 500);
        }
    }

    /**
     * ✅ API lấy vị trí đơn hàng cho map (AJAX)
     */
    public function getOrderLocation($orderId)
    {
        try {
            $order = Order::where('sender_id', Auth::id())
                ->with(['delivery', 'deliveryIssues'])
                ->findOrFail($orderId);

            $locations = [];

            // Vị trí người gửi
            if ($order->sender_latitude && $order->sender_longitude) {
                $locations[] = [
                    'type' => 'sender',
                    'lat' => (float) $order->sender_latitude,
                    'lng' => (float) $order->sender_longitude,
                    'label' => 'Điểm lấy hàng',
                    'address' => $order->sender_address,
                    'icon' => 'pickup'
                ];
            }

            // Vị trí người nhận
            if ($order->recipient_latitude && $order->recipient_longitude) {
                $locations[] = [
                    'type' => 'recipient',
                    'lat' => (float) $order->recipient_latitude,
                    'lng' => (float) $order->recipient_longitude,
                    'label' => 'Điểm giao hàng',
                    'address' => $order->recipient_full_address,
                    'icon' => 'delivery'
                ];
            }

            // Vị trí giao hàng thực tế (nếu đã giao)
            if ($order->delivery && $order->delivery->delivery_latitude && $order->delivery->delivery_longitude) {
                $locations[] = [
                    'type' => 'actual_delivery',
                    'lat' => (float) $order->delivery->delivery_latitude,
                    'lng' => (float) $order->delivery->delivery_longitude,
                    'label' => 'Vị trí giao hàng thực tế',
                    'address' => $order->delivery->delivery_address,
                    'time' => $order->delivery->actual_delivery_time?->format('H:i d/m/Y'),
                    'icon' => 'success'
                ];
            }

            // Vị trí các sự cố (nếu có)
            foreach ($order->deliveryIssues as $issue) {
                if ($issue->issue_latitude && $issue->issue_longitude) {
                    $locations[] = [
                        'type' => 'issue',
                        'lat' => (float) $issue->issue_latitude,
                        'lng' => (float) $issue->issue_longitude,
                        'label' => 'Vị trí báo cáo sự cố',
                        'issue_type' => $issue->issue_type,
                        'issue_note' => $issue->issue_note,
                        'time' => $issue->issue_time?->format('H:i d/m/Y'),
                        'icon' => 'warning'
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'locations' => $locations,
                'order_status' => $order->status,
                'has_issues' => $order->deliveryIssues->count() > 0
            ]);

        } catch (\Exception $e) {
            Log::error('Lỗi trong OrderManagentController::getOrderLocation: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Không thể tải vị trí đơn hàng.'
            ], 500);
        }
    }

    /**
     * ✅ Chuẩn bị dữ liệu map cho view
     */
    /**
     * ✅ Chuẩn bị dữ liệu map cho view
     */
   private function prepareMapData($order)
{
    $locations = [];

    // ✅ 1. Điểm lấy hàng - Ưu tiên tọa độ thực tế nếu đã lấy hàng
    if ($order->actual_pickup_time && $order->pickup_latitude && $order->pickup_longitude) {
        // Đã lấy hàng thành công -> dùng tọa độ thực tế
        $locations['sender'] = [
            'lat' => (float) $order->pickup_latitude,
            'lng' => (float) $order->pickup_longitude,
            'address' => $order->sender_address,
            'is_actual' => true, // Đánh dấu đây là vị trí thực tế
        ];
    } elseif ($order->sender_latitude && $order->sender_longitude) {
        // Chưa lấy hàng -> dùng tọa độ dự kiến
        $locations['sender'] = [
            'lat' => (float) $order->sender_latitude,
            'lng' => (float) $order->sender_longitude,
            'address' => $order->sender_address,
            'is_actual' => false,
        ];
    }

    // ✅ 2. Điểm giao hàng
    if ($order->recipient_latitude && $order->recipient_longitude) {
        $locations['recipient'] = [
            'lat' => (float) $order->recipient_latitude,
            'lng' => (float) $order->recipient_longitude,
            'address' => $order->recipient_full_address
        ];
    }

    // ✅ 3. Vị trí bưu cục (nếu đơn đang ở hub)
    if ($order->status === Order::STATUS_AT_HUB && $order->post_office_id) {
        // Lấy thông tin hub từ database
        $hub = Hub::where('post_office_id', $order->post_office_id)->first();
        if ($hub && $hub->hub_latitude && $hub->hub_longitude) {
            $locations['hub'] = [
                'lat' => (float) $hub->hub_latitude,
                'lng' => (float) $hub->hub_longitude,
                'address' => $hub->hub_address ?? 'Bưu cục trung tâm',
            ];
        }
    }

    // ✅ 4. Vị trí giao hàng thực tế
    if ($order->delivery && $order->delivery->delivery_latitude && $order->delivery->delivery_longitude) {
        $locations['actual_delivery'] = [
            'lat' => (float) $order->delivery->delivery_latitude,
            'lng' => (float) $order->delivery->delivery_longitude,
            'address' => $order->delivery->delivery_address ?: $order->recipient_full_address,
            'time' => $order->delivery->actual_delivery_time?->format('H:i d/m/Y')
        ];
    }

    // ✅ 5. Vị trí sự cố
    $issues = [];
    foreach ($order->deliveryIssues as $issue) {
        if ($issue->issue_latitude && $issue->issue_longitude) {
            $issues[] = [
                'lat' => (float) $issue->issue_latitude,
                'lng' => (float) $issue->issue_longitude,
                'type' => $issue->issue_type,
                'note' => $issue->issue_note,
                'time' => $issue->issue_time?->format('H:i d/m/Y')
            ];
        }
    }

    // ✅ 6. Lấy tracking points
    $trackingPoints = [];
    if (method_exists($order, 'getTrackingTimeline')) {
        $timeline = $order->getTrackingTimeline();
        foreach ($timeline as $item) {
            if (isset($item['lat']) && isset($item['lng']) && $item['lat'] && $item['lng']) {
                $trackingPoints[] = [
                    'lat' => (float) $item['lat'],
                    'lng' => (float) $item['lng'],
                    'status' => $item['status'] ?? '',
                    'status_label' => $item['status_label'] ?? '',
                    'address' => $item['address'] ?? '',
                    'note' => $item['note'] ?? '',
                    'time' => $item['time']->format('H:i d/m/Y'),
                    'timestamp' => $item['time']->timestamp,
                    'icon' => $item['icon'] ?? 'circle',
                    'color' => $item['color'] ?? '#6c757d',
                    'type' => $item['type'] ?? 'tracking'
                ];
            }
        }
    }

    return [
        'locations' => $locations,
        'issues' => $issues,
        'tracking_points' => $trackingPoints,
        'has_locations' => count($locations) > 0,
        'is_in_transit' => method_exists($order, 'isInTransit') ? $order->isInTransit() : false,
        'last_update' => now()->timestamp
    ];
}

    /**
     * ✅ Đếm số lượng đơn hàng theo trạng thái
     */
    private function getStatusCounts()
    {
        $userId = Auth::id();

        $counts = Order::where('sender_id', $userId)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Đếm đơn giao thất bại (có issues hoặc cancelled)
        $failedCount = Order::where('sender_id', $userId)
            ->where(function ($q) {
                $q->whereHas('deliveryIssues')
                    ->orWhere('status', Order::STATUS_CANCELLED);
            })
            ->count();

        return [
            'pending' => $counts['pending'] ?? 0,
            'confirmed' => $counts['confirmed'] ?? 0,
            'picking_up' => $counts['picking_up'] ?? 0,
            'picked_up' => $counts['picked_up'] ?? 0,
            'at_hub' => $counts['at_hub'] ?? 0,
            'shipping' => $counts['shipping'] ?? 0,
            'delivered' => $counts['delivered'] ?? 0,
            'cancelled' => $counts['cancelled'] ?? 0,
            'failed' => $failedCount,
        ];
    }
    public function getTrackingUpdates(Request $request, $orderId)
    {
        try {
            $order = Order::where('sender_id', Auth::id())
                ->with(['delivery', 'deliveryIssues'])
                ->findOrFail($orderId);

            $lastUpdate = $request->query('last_update', 0);

            // Lấy các tracking mới sau timestamp
            $newTrackings = $order->getTrackingUpdatesSince($lastUpdate);

            // Chuyển đổi thành tracking points có tọa độ
            $trackingPoints = array_values(array_filter(array_map(function ($item) {
                if (!isset($item['lat']) || !isset($item['lng']) || !$item['lat'] || !$item['lng']) {
                    return null;
                }

                return [
                    'lat' => $item['lat'],
                    'lng' => $item['lng'],
                    'status' => $item['status'],
                    'status_label' => $item['status_label'],
                    'address' => $item['address'],
                    'note' => $item['note'],
                    'time' => $item['time']->format('H:i d/m/Y'),
                    'timestamp' => $item['time']->timestamp,
                    'icon' => $item['icon'],
                    'color' => $item['color'],
                    'type' => $item['type'],
                    'details' => $item['details'] ?? null,
                ];
            }, $newTrackings)));

            return response()->json([
                'success' => true,
                'has_updates' => count($trackingPoints) > 0,
                'trackings' => $trackingPoints,
                'current_status' => $order->status,
                'status_label' => $order->status_label,
                'is_in_transit' => $order->isInTransit(),
                'last_check' => now()->timestamp,
            ]);

        } catch (\Exception $e) {
            Log::error('Lỗi trong OrderManagentController::getTrackingUpdates: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Không thể lấy tracking updates.'
            ], 500);
        }
    }
}