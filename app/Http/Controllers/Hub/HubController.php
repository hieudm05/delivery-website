<?php

namespace App\Http\Controllers\Hub;

use App\Http\Controllers\Controller;
use App\Models\Customer\Dashboard\Orders\Order;
use App\Models\User;
use App\Models\Driver\DriverProfile;
use App\Models\Hub\Hub;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HubController extends Controller
{
    public function index()
    {
        $hub = $this->getCurrentHub();
        if (!$hub) {
            return redirect()->route('home')->with('error', 'Bạn chưa được phân bưu cục.');
        }

        // Lấy danh sách đơn hàng tại hub này cần giao
        $orders = Order::where('post_office_id', $hub->post_office_id)
            ->whereIn('status', [Order::STATUS_AT_HUB, Order::STATUS_AT_HUB])
            ->whereNull('driver_id')
            ->with(['orderGroup'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        // $orders = Order::all();

        return view('hub.index', compact('hub', 'orders'));
    }

    /**
     * Hiển thị form phát đơn
     */
    public function assignOrderForm($orderId)
    {
        $hub = $this->getCurrentHub();
        if (!$hub) {
            return redirect()->route('home')->with('error', 'Bạn chưa được phân bưu cục.');
        }

        $order = Order::with(['orderGroup'])->findOrFail($orderId);
        // Kiểm tra đơn có thuộc hub này không
        if ($order->post_office_id != $hub->post_office_id) {
            return redirect()->route('hub.index')->with('error', 'Đơn hàng không thuộc bưu cục của bạn.');
        }

        // Lấy danh sách tài xế phù hợp
        $availableDrivers = $this->getAvailableDrivers($hub, $order);

        return view('hub.assign-order', compact('order', 'availableDrivers', 'hub'));
    }

    /**
     * Xử lý phát đơn cho tài xế
     */
    public function assignOrder(Request $request, $orderId)
    {
        $request->validate([
            'driver_id' => 'required|exists:users,id',
            'note' => 'nullable|string|max:500',
        ]);

        $hub = $this->getCurrentHub();
        
        if (!$hub) {
            return response()->json(['error' => 'Bạn chưa được phân bưu cục.'], 403);
        }

        $order = Order::findOrFail($orderId);

        // Kiểm tra đơn có thuộc hub này không
        if ($order->post_office_id != $hub->post_office_id) {
            return response()->json(['error' => 'Đơn hàng không thuộc bưu cục của bạn.'], 403);
        }

        // Kiểm tra đơn chưa được gán
        if ($order->driver_id) {
            return response()->json(['error' => 'Đơn hàng đã được gán cho tài xế khác.'], 400);
        }

        $driver = User::find($request->driver_id);

        // Kiểm tra tài xế có hợp lệ không
        if (!$this->isDriverEligible($driver, $hub, $order)) {
            return response()->json(['error' => 'Tài xế không đủ điều kiện nhận đơn này.'], 400);
        }

        try {
            DB::beginTransaction();

            // Gán tài xế cho đơn hàng
            $order->driver_id = $driver->id;
            $order->status = Order::STATUS_SHIPPING;
            $order->note = $request->note ? $order->note . "\n[Hub] " . $request->note : $order->note;
            $order->save();

            // Cập nhật status của OrderGroup nếu có
            if ($order->isPartOfGroup()) {
                $order->orderGroup->updateGroupStatus();
            }

            // Log hoạt động
            Log::info('Order assigned to driver', [
                'order_id' => $order->id,
                'driver_id' => $driver->id,
                'hub_id' => $hub->id,
                'assigned_by' => auth()->id(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Đã phát đơn #{$order->id} cho tài xế {$driver->full_name}",
                'order' => $order,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to assign order', [
                'error' => $e->getMessage(),
                'order_id' => $orderId,
                'driver_id' => $request->driver_id,
            ]);

            return response()->json(['error' => 'Có lỗi xảy ra khi phát đơn.'], 500);
        }
    }

    /**
     * Lấy danh sách tài xế có thể nhận đơn
     */
    // private function getAvailableDrivers(Hub $hub, Order $order)
    // {
    //     $maxDistance = 20; // km

    //     $drivers = User::query()
    //         ->where('role', 'driver')
    //         ->where('status', 'active')
    //         // ✅ Loại bỏ tài xế đã nhận đơn
    //         ->when($order->pickup_driver_id, function ($q) use ($order) {
    //             $q->where('id', '!=', $order->pickup_driver_id);
    //         })
    //         // ✅ Lọc tài xế có userInfo gần hub hoặc gần điểm giao
    //         ->whereHas('userInfo', function ($q) use ($hub, $order, $maxDistance) {
    //             if ($hub->hasCoordinates() && $order->recipient_latitude && $order->recipient_longitude) {
    //                 $q->whereRaw("
    //                     (6371 * acos(
    //                         cos(radians(?)) * cos(radians(latitude)) *
    //                         cos(radians(longitude) - radians(?)) +
    //                         sin(radians(?)) * sin(radians(latitude))
    //                     )) <= ?
    //                     OR
    //                     (6371 * acos(
    //                         cos(radians(?)) * cos(radians(latitude)) *
    //                         cos(radians(longitude) - radians(?)) +
    //                         sin(radians(?)) * sin(radians(latitude))
    //                     )) <= ?
    //                 ", [
    //                     $hub->hub_latitude,
    //                     $hub->hub_longitude,
    //                     $hub->hub_latitude,
    //                     $maxDistance,
    //                     $order->recipient_latitude,
    //                     $order->recipient_longitude,
    //                     $order->recipient_latitude,
    //                     $maxDistance
    //                 ]);
    //             }
    //         })
    //         ->with('userInfo')
    //         ->get()
    //         // ✅ Lọc các tài xế đang online và thuộc bưu cục
    //         ->filter(function ($driver) use ($hub) {
    //             if (!$driver->isOnline()) {
    //                 return false;
    //             }

    //             $driverProfile = DriverProfile::where('user_id', $driver->id)
    //                 ->where('post_office_id', $hub->post_office_id)
    //                 ->where('status', 'approved')
    //                 ->first();

    //             return $driverProfile !== null;
    //         })
    //         // ✅ Tính toán khoảng cách thực tế
    //         ->map(function ($driver) use ($hub, $order) {
    //             $userInfo = $driver->userInfo;
    //             $distanceToHub = 0;
    //             $distanceToOrder = 0;

    //             if ($userInfo && $userInfo->latitude && $userInfo->longitude) {
    //                 if ($hub->hasCoordinates()) {
    //                     $distanceToHub = $this->calculateDistance(
    //                         $userInfo->latitude,
    //                         $userInfo->longitude,
    //                         $hub->hub_latitude,
    //                         $hub->hub_longitude
    //                     );
    //                 }

    //                 if ($order->recipient_latitude && $order->recipient_longitude) {
    //                     $distanceToOrder = $this->calculateDistance(
    //                         $userInfo->latitude,
    //                         $userInfo->longitude,
    //                         $order->recipient_latitude,
    //                         $order->recipient_longitude
    //                     );
    //                 }
    //             }

    //             return [
    //                 'id' => $driver->id,
    //                 'name' => $driver->full_name,
    //                 'phone' => $driver->phone,
    //                 'avatar' => $driver->avatar_url,
    //                 'is_online' => true,
    //                 'distance_to_hub' => round($distanceToHub, 2),
    //                 'distance_to_order' => round($distanceToOrder, 2),
    //                 'last_seen' => $driver->last_seen_at
    //                     ? $driver->last_seen_at->diffForHumans()
    //                     : 'Chưa cập nhật',
    //             ];
    //         })
    //         ->sortBy('distance_to_order')
    //         ->values();

    //     return $drivers;
    // }

    private function getAvailableDrivers(Hub $hub, Order $order)
{
    $maxDistance = 20; // km

    $drivers = User::query()
        ->where('role', 'driver')
        ->where('status', 'active')
        ->when($order->pickup_driver_id, function ($q) use ($order) {
            $q->where('id', '!=', $order->pickup_driver_id);
        })
        ->whereHas('userInfo', function ($q) use ($hub, $order, $maxDistance) {
            if ($hub->hasCoordinates() && $order->recipient_latitude && $order->recipient_longitude) {
                $q->whereRaw("
                    (6371 * acos(
                        cos(radians(?)) * cos(radians(latitude)) *
                        cos(radians(longitude) - radians(?)) +
                        sin(radians(?)) * sin(radians(latitude))
                    )) <= ?
                    OR
                    (6371 * acos(
                        cos(radians(?)) * cos(radians(latitude)) *
                        cos(radians(longitude) - radians(?)) +
                        sin(radians(?)) * sin(radians(latitude))
                    )) <= ?
                ", [
                    $hub->hub_latitude,
                    $hub->hub_longitude,
                    $hub->hub_latitude,
                    $maxDistance,
                    $order->recipient_latitude,
                    $order->recipient_longitude,
                    $order->recipient_latitude,
                    $maxDistance
                ]);
            }
        })
        ->with('userInfo')
        ->get()
        ->map(function ($driver) use ($hub, $order) {
            $userInfo = $driver->userInfo;
            $distanceToHub = 0;
            $distanceToOrder = 0;

            if ($userInfo && $userInfo->latitude && $userInfo->longitude) {
                if ($hub->hasCoordinates()) {
                    $distanceToHub = $this->calculateDistance(
                        $userInfo->latitude,
                        $userInfo->longitude,
                        $hub->hub_latitude,
                        $hub->hub_longitude
                    );
                }

                if ($order->recipient_latitude && $order->recipient_longitude) {
                    $distanceToOrder = $this->calculateDistance(
                        $userInfo->latitude,
                        $userInfo->longitude,
                        $order->recipient_latitude,
                        $order->recipient_longitude
                    );
                }
            }

            $driverProfile = DriverProfile::where('user_id', $driver->id)
                ->where('post_office_id', $hub->post_office_id)
                ->where('status', 'approved')
                ->first();

            return [
                'id' => $driver->id,
                'name' => $driver->full_name,
                'phone' => $driver->phone,
                'avatar' => $driver->avatar_url,
                'is_online' => $driver->isOnline(),
                'belongs_to_hub' => $driverProfile !== null,
                'distance_to_hub' => round($distanceToHub, 2),
                'distance_to_order' => round($distanceToOrder, 2),
                'last_seen' => $driver->last_seen_at
                    ? $driver->last_seen_at->diffForHumans()
                    : 'Chưa cập nhật',
            ];
        })
        // ✅ Lọc ra tài xế thuộc bưu cục
        ->filter(fn($d) => $d['belongs_to_hub'])
        ->values();

    // ✅ Ưu tiên tài xế online gần đơn hàng nhất
    $onlineDrivers = $drivers->where('is_online', true)->sortBy('distance_to_order')->values();

    // ✅ Nếu không có tài xế online, chọn tài xế offline gần hub hoặc nơi gửi nhất
    if ($onlineDrivers->isEmpty()) {
        $offlineDrivers = $drivers
            ->where('is_online', false)
            ->sortBy(function ($d) {
                // Ưu tiên tài xế gần hub, nếu bằng nhau thì gần nơi giao
                return $d['distance_to_hub'] + ($d['distance_to_order'] * 0.5);
            })
            ->values();

        return $offlineDrivers;
    }

    return $onlineDrivers;
}



    /**
     * Kiểm tra tài xế có đủ điều kiện nhận đơn không
     */
   private function isDriverEligible(User $driver, Hub $hub, Order $order)
{
    // 1. Lấy thông tin tài xế
    $driverProfile = DriverProfile::where('user_id', $driver->id)
        ->where('post_office_id', $hub->post_office_id)
        ->where('status', 'approved')
        ->first();

    if (!$driverProfile) {
        return false;
    }

    // 2. Kiểm tra vị trí
    $userInfo = $driver->userInfo;
    if (!$userInfo || !$userInfo->latitude || !$userInfo->longitude) {
        return false;
    }

    $maxDistance = 20; // km
    $distanceToHub = $hub->hasCoordinates()
        ? $this->calculateDistance($userInfo->latitude, $userInfo->longitude, $hub->hub_latitude, $hub->hub_longitude)
        : INF;

    $distanceToOrder = ($order->recipient_latitude && $order->recipient_longitude)
        ? $this->calculateDistance($userInfo->latitude, $userInfo->longitude, $order->recipient_latitude, $order->recipient_longitude)
        : INF;

    // ✅ Chấp nhận nếu gần hub hoặc gần đơn hàng, DÙ offline
    return ($distanceToHub <= $maxDistance) || ($distanceToOrder <= $maxDistance);
}

    /**
     * Tính khoảng cách giữa 2 điểm (Haversine formula)
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // km

        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lon1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($lon2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(
            pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)
        ));

        return $angle * $earthRadius;
    }

    /**
     * Lấy thông tin hub hiện tại của user đang đăng nhập
     */
    private function getCurrentHub()
    {
        return Hub::where('user_id', auth()->id())->first();
    }

    /**
     * API: Lấy danh sách tài xế có thể nhận đơn
     */
    public function getAvailableDriversApi($orderId)
    {
        $hub = $this->getCurrentHub();
        
        if (!$hub) {
            return response()->json(['error' => 'Bạn chưa được phân bưu cục.'], 403);
        }

        $order = Order::findOrFail($orderId);

        if ($order->post_office_id !== $hub->post_office_id) {
            return response()->json(['error' => 'Đơn hàng không thuộc bưu cục của bạn.'], 403);
        }

        $drivers = $this->getAvailableDrivers($hub, $order);

        return response()->json([
            'success' => true,
            'drivers' => $drivers,
            'total' => $drivers->count(),
        ]);
    }
}