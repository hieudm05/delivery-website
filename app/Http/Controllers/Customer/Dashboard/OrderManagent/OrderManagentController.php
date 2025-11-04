<?php

namespace App\Http\Controllers\Customer\Dashboard\OrderManagent;

use App\Http\Controllers\Controller;
use App\Models\Customer\Dashboard\Orders\Order;
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
            
            // Validate status
            if ($status !== 'all' && !in_array($status, Order::STATUSES)) {
                $status = 'all';
            }
            
            $query = Order::where('sender_id', Auth::id())
                ->with(['products', 'images', 'orderGroup'])
                ->withStatus($status)
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
                ->with(['products', 'images', 'deliveryImages', 'orderGroup'])
                ->findOrFail($id);

            return view('customer.dashboard.orderManagent.show', compact('order'));
            
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
                ->when($request->status, function($q) use ($request) {
                    return $q->where('status', $request->status);
                })
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'images' => $images->map(function($img) {
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
     * ✅ Đếm số lượng đơn hàng theo trạng thái
     */
    private function getStatusCounts()
    {
        $counts = Order::where('sender_id', Auth::id())
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // ✅ Đảm bảo TẤT CẢ trạng thái đều có giá trị, tránh lỗi undefined key
        return [
            'pending' => $counts['pending'] ?? 0,
            'confirmed' => $counts['confirmed'] ?? 0,
            'picking_up' => $counts['picking_up'] ?? 0,
            'picked_up' => $counts['picked_up'] ?? 0,
            'at_hub' => $counts['at_hub'] ?? 0,
            'shipping' => $counts['shipping'] ?? 0,
            'delivered' => $counts['delivered'] ?? 0,
            'cancelled' => $counts['cancelled'] ?? 0,
        ];
    }
}