<?php

namespace App\Http\Controllers\Customer\Dashboard\OrderManagent;

use App\Http\Controllers\Controller;
use App\Models\Customer\Dashboard\Orders\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        
        $query = Order::where('sender_id', Auth::id())
            ->with(['products', 'images'])
            ->when($status && $status !== 'all', function($q) use ($status) {
                return $q->where('status', $status);
            })
            ->when($search, function($q) use ($search) {
                return $q->where(function($query) use ($search) {
                    $query->where('id', 'LIKE', '%' . $search . '%')
                          ->orWhere('recipient_name', 'LIKE', '%' . $search . '%')
                          ->orWhere('recipient_phone', 'LIKE', '%' . $search . '%');
                });
            })
            ->latest();

        $orders = $query->paginate(12);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'html' => view('customer.dashboard.orderManagent._orders_list', compact('orders'))->render(),
                'pagination' => $orders->appends(['status' => $status, 'search' => $search])->links()->render()
            ]);
        }
        
        $statusCounts = Order::where('sender_id', Auth::id())
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return view('customer.dashboard.orderManagent.index', compact('orders', 'statusCounts'));
    } catch (\Exception $e) {
        \Log::error('Lỗi trong OrderManagentController::index: ' . $e->getMessage());
        if ($request->ajax()) {
            return response()->json(['success' => false, 'error' => 'Lỗi server: ' . $e->getMessage()], 500);
        }
        abort(500, 'Lỗi server');
    }
}

    /**
     * ✅ Chi tiết đơn hàng
     */
    public function show($id)
    {
        $order = Order::where('sender_id', Auth::id())
            ->with(['products', 'images', 'deliveryImages'])
            ->findOrFail($id);

        return view('customer.dashboard.orderManagent.show', compact('order'));
    }

    /**
     * ✅ Sửa đơn hàng (chỉ khi status = pending)
     */
    public function edit($id)
    {
        $order = Order::where('sender_id', Auth::id())
            ->where('status', 'pending')
            ->findOrFail($id);

        return view('customer.dashboard.orderManagent.edit', compact('order'));
    }

    /**
     * ✅ Update đơn hàng
     */
    public function update(Request $request, $id)
    {
        $order = Order::where('sender_id', Auth::id())
            ->where('status', 'pending')
            ->findOrFail($id);

        $validated = $request->validate([
            'recipient_name' => 'required|string|max:255',
            'recipient_phone' => 'required|string|regex:/^(0|\+84)[0-9]{9,10}$/',
            'recipient_full_address' => 'required|string|max:500',
            'delivery_time' => 'required|date|after:now',
            'note' => 'nullable|string|max:1000',
        ]);

        $order->update($validated);

        return redirect()->route('customer.orderManagent.show', $order->id)
            ->with('success', 'Cập nhật đơn hàng thành công!');
    }

    /**
     * ✅ Xóa đơn hàng (chỉ khi status = pending)
     */
    public function destroy($id)
    {
        $order = Order::where('sender_id', Auth::id())
            ->where('status', 'pending')
            ->findOrFail($id);

        // Xóa ảnh liên quan
        foreach ($order->images as $image) {
            \Storage::disk('public')->delete($image->image_path);
            $image->delete();
        }

        $order->delete();

        return redirect()->route('customer.orderManagent.index')
            ->with('success', 'Đã xóa đơn hàng!');
    }

    /**
     * ✅ API lấy ảnh giao hàng theo trạng thái (AJAX)
     */
    public function getDeliveryImages(Request $request, $orderId)
    {
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
    }
}