<?php

namespace App\Http\Controllers\Drivers;

use App\Http\Controllers\Controller;
use App\Models\Customer\Dashboard\Orders\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DriverTrackingController extends Controller
{
    /**
     * Cập nhật vị trí driver theo thời gian thực
     * POST /driver/tracking/update
     */
    public function updateLocation(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'order_id' => 'nullable|exists:orders,id',
        ]);

        $driverId = auth()->id();
        $cacheKey = "driver_location_{$driverId}";

        $locationData = [
            'driver_id' => $driverId,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'timestamp' => now()->toIso8601String(),
            'order_id' => $request->order_id,
        ];

        // Lưu vào cache 5 phút
        Cache::put($cacheKey, $locationData, now()->addMinutes(5));

        // Nếu đang giao đơn cụ thể, lưu vào cache riêng
        if ($request->order_id) {
            $orderCacheKey = "order_tracking_{$request->order_id}";
            Cache::put($orderCacheKey, $locationData, now()->addMinutes(5));
        }

        return response()->json([
            'success' => true,
            'message' => 'Đã cập nhật vị trí',
            'data' => $locationData
        ]);
    }

    /**
     * Lấy vị trí hiện tại của driver
     * GET /driver/tracking/location
     */
    public function getLocation()
    {
        $driverId = auth()->id();
        $cacheKey = "driver_location_{$driverId}";

        $location = Cache::get($cacheKey);

        if (!$location) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy vị trí'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $location
        ]);
    }

    /**
     * Tracking đơn hàng (cho customer xem)
     * GET /tracking/{order_id}
     */
    public function trackOrder($orderId)
    {
        $order = Order::with(['orderGroup'])->find($orderId);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Đơn hàng không tồn tại'
            ], 404);
        }

        $result = [
            'order_id' => $order->id,
            'status' => $order->status,
            'status_label' => $order->status_label,
            'recipient' => [
                'name' => $order->recipient_name,
                'phone' => $order->recipient_phone,
                'address' => $order->recipient_full_address,
                'latitude' => $order->recipient_latitude,
                'longitude' => $order->recipient_longitude,
            ],
            'driver_location' => null,
            'estimated_arrival' => null,
        ];

        // Nếu đang giao hàng, lấy vị trí driver
        if ($order->status === Order::STATUS_SHIPPING && $order->delivery_driver_id) {
            $cacheKey = "order_tracking_{$orderId}";
            $driverLocation = Cache::get($cacheKey);

            if ($driverLocation) {
                $result['driver_location'] = [
                    'latitude' => $driverLocation['latitude'],
                    'longitude' => $driverLocation['longitude'],
                    'timestamp' => $driverLocation['timestamp'],
                ];

                // Tính khoảng cách và thời gian dự kiến
                $distance = $this->calculateDistance(
                    $driverLocation['latitude'],
                    $driverLocation['longitude'],
                    $order->recipient_latitude,
                    $order->recipient_longitude
                );

                $result['estimated_arrival'] = [
                    'distance_km' => round($distance, 2),
                    'estimated_minutes' => round(($distance / 30) * 60), // Giả sử 30km/h
                ];
            }
        }

        return response()->json([
            'success' => true,
            'data' => $result
        ]);
    }

    /**
     * Trang tracking cho customer (Web view)
     * GET /tracking/{order_id}/map
     */
    public function trackingMap($orderId)
    {
        $order = Order::with(['orderGroup'])->find($orderId);

        if (!$order) {
            abort(404, 'Đơn hàng không tồn tại');
        }

        return view('tracking.map', compact('order'));
    }

    /**
     * Lấy danh sách tất cả driver đang hoạt động (cho admin)
     * GET /admin/drivers/active
     */
    public function getActiveDrivers()
    {
        $drivers = [];
        $pattern = "driver_location_*";
        
        // Lấy tất cả keys matching pattern (simplified, cần implement theo cache driver)
        // Đây là ví dụ đơn giản, production nên dùng Redis với SCAN
        
        return response()->json([
            'success' => true,
            'data' => $drivers,
            'message' => 'Cần implement Redis SCAN để lấy all active drivers'
        ]);
    }

    /**
     * Tính khoảng cách giữa 2 điểm
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        if (!$lat1 || !$lon1 || !$lat2 || !$lon2) {
            return 0;
        }

        $earthRadius = 6371;
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