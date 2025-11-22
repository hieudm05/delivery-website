<?php

namespace App\Http\Controllers\Drivers;

use App\Http\Controllers\Controller;
use App\Models\Driver\DriverProfile;
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
                'actual_pickup_start_time' => now() // Thêm field này vào migration nếu cần
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
     * Xác nhận đã lấy hàng thành công
     */
    public function confirmPickup(Request $request, $id)
    {
        $request->validate([
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

            // Cập nhật thông tin đơn hàng
            $order->update([
                'status' => 'picked_up',
                'actual_pickup_time' => now(),
                'actual_packages' => $request->actual_packages,
                'actual_weight' => $request->actual_weight,
                'pickup_note' => $request->note,
                'pickup_driver_id' => Auth::id(), // ID của người lấy hàng
            ]);

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

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Đã xác nhận lấy hàng thành công',
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
     * Báo cáo lỗi khi lấy hàng (shop không có hàng, địa chỉ sai, ...)
     */
    public function reportIssue(Request $request, $id)
    {
        $request->validate([
            'issue_type' => 'required|in:shop_closed,wrong_address,no_goods,customer_cancel,other',
            'issue_note' => 'required|string|max:500',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        DB::beginTransaction();
        try {
            $order = Order::findOrFail($id);

            if (!in_array($order->status, ['confirmed', 'picking_up'])) {
                throw new \Exception('Không thể báo cáo lỗi cho đơn hàng này');
            }

            // Cập nhật trạng thái về confirmed và ghi chú vấn đề
            $order->update([
                'status' => 'confirmed', // Trả về trạng thái chờ xử lý
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

            // TODO: Gửi thông báo cho bưu cục và người gửi

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
    // ✅ Xử lý đầu vào: chấp nhận 1 ID, mảng, hoặc JSON string
    $orderIds = $request->order_ids;
    // Nếu là JSON (ví dụ: "[1,2,3]") → decode thành mảng
    if (is_string($orderIds)) {
        $decoded = json_decode($orderIds, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $orderIds = $decoded;
        } else {
            $orderIds = [$orderIds]; // chỉ 1 đơn
        }
    }

    // Nếu vẫn không phải mảng, thì bọc lại
    if (!is_array($orderIds)) {
        $orderIds = [$orderIds];
    }

    // Loại bỏ phần tử rỗng/null
    $orderIds = array_filter($orderIds);

    // ✅ Validate cơ bản
    $request->validate([
        'note'   => 'nullable|string|max:500',
    ]);

    if (empty($orderIds)) {
        return response()->json([
            'success' => false,
            'message' => 'Không có đơn hàng nào được chọn.',
        ], 400);
    }

    DB::beginTransaction();
    try {
        // ✅ Chỉ lấy các đơn hợp lệ
        $orders = Order::whereIn('id', $orderIds)
            ->where('status', 'picked_up')
            ->get();

        if ($orders->count() !== count($orderIds)) {
            throw new \Exception('Một số đơn hàng không hợp lệ hoặc chưa được lấy.');
        }

        foreach ($orders as $order) {
            $order->update([
                'status'             => 'at_hub',
                'current_hub_id'     =>  $request->post_office_id ? $request->post_office_id : $order->current_hub_id,
                'hub_transfer_time'  => now(),
                'hub_transfer_note'  => $request->note,
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
   // Lấy bưu cục tài xế
    public function location()
{
    try {
        $driver = Auth::user();

        // Lấy thông tin bưu cục gắn với tài xế
        $profile = DriverProfile::where('user_id', $driver->id)
            ->select('post_office_name', 'post_office_address', 'post_office_lat', 'post_office_lng')
            ->first();

        // Nếu chưa có hồ sơ tài xế
        if (!$profile) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy hồ sơ tài xế'
            ], 404);
        }

        // Nếu thiếu thông tin tọa độ
        if (empty($profile->post_office_lat) || empty($profile->post_office_lng)) {
            return response()->json([
                'success' => false,
                'message' => 'Chưa có thông tin tọa độ bưu cục'
            ], 404);
        }

        // Trả về dữ liệu bưu cục
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

}