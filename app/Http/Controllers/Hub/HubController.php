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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class HubController extends Controller
{
    public function index () {
        return view('hub.index');
    }
    
    public function approval()
    {
        $hub = $this->getCurrentHub();
        if (!$hub) {
            return redirect()->route('home')->with('error', 'Bạn chưa được phân bưu cục.');
        }

        $orders = Order::where('post_office_id', $hub->post_office_id)
            ->whereIn('status', [Order::STATUS_AT_HUB, Order::STATUS_AT_HUB])
            ->whereNull('driver_id')
            ->with(['orderGroup'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('hub.approval.approval', compact('hub', 'orders'));
    }

    public function assignOrderForm($orderId)
    {
        $hub = $this->getCurrentHub();
        if (!$hub) {
            return redirect()->route('home')->with('error', 'Bạn chưa được phân bưu cục.');
        }

        $order = Order::with(['orderGroup','products'])->findOrFail($orderId);
        if ($order->post_office_id != $hub->post_office_id) {
            return redirect()->route('hub.index')->with('error', 'Đơn hàng không thuộc bưu cục của bạn.');
        }

        $availableDrivers = $this->getAvailableDrivers($hub, $order);

        return view('hub.approval.assign-order', compact('order', 'availableDrivers', 'hub'));
    }

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

        if ($order->post_office_id != $hub->post_office_id) {
            return response()->json(['error' => 'Đơn hàng không thuộc bưu cục của bạn.'], 403);
        }

        if ($order->driver_id) {
            return response()->json(['error' => 'Đơn hàng đã được gán cho tài xế khác.'], 400);
        }

        $driver = User::find($request->driver_id);

        if (!$this->isDriverEligible($driver, $hub, $order)) {
            return response()->json(['error' => 'Tài xế không đủ điều kiện nhận đơn này.'], 400);
        }

        try {
            DB::beginTransaction();

            $order->driver_id = $driver->id;
            $order->status = Order::STATUS_SHIPPING;
            $order->note = $request->note ? $order->note . "\n[Hub] " . $request->note : $order->note;
            $order->save();

            if ($order->isPartOfGroup()) {
                $order->orderGroup->updateGroupStatus();
            }

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
     * ✅ TRANG DANH SÁCH ĐƠN HÀNG CỦA HUB (CÓ FILTER & SEARCH)
     */
    public function orders(Request $request)
    {
        $hub = $this->getCurrentHub();
        if (!$hub) {
            return redirect()->route('home')->with('error', 'Bạn chưa được phân bưu cục.');
        }

        $status = $request->query('status', 'all');
        $search = $request->query('search', '');

        // Validate status
        $validStatuses = Order::STATUSES;
        if ($status !== 'all' && !in_array($status, $validStatuses)) {
            $status = 'all';
        }

        $query = Order::where('post_office_id', $hub->post_office_id)
            ->with(['products', 'images', 'orderGroup', 'delivery', 'deliveryIssues'])
            ->when($status !== 'all', function ($q) use ($status) {
                return $q->where('status', $status);
            })
            ->when($search, function ($q) use ($search) {
                return $q->where(function ($query) use ($search) {
                    $query->where('id', 'like', "%{$search}%")
                        ->orWhere('recipient_name', 'like', "%{$search}%")
                        ->orWhere('recipient_phone', 'like', "%{$search}%")
                        ->orWhere('sender_name', 'like', "%{$search}%")
                        ->orWhere('sender_phone', 'like', "%{$search}%");
                });
            })
            ->latest();

        $orders = $query->paginate(15)->appends([
            'status' => $status,
            'search' => $search
        ]);

        // Đếm số lượng theo trạng thái
        $statusCounts = $this->getHubStatusCounts($hub);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'html' => view('hub.orders._orders_list', compact('orders'))->render(),
                'pagination' => $orders->links()->render()
            ]);
        }

        return view('hub.orders.index', compact('hub', 'orders', 'statusCounts'));
    }

    /**
     * ✅ XEM CHI TIẾT ĐƠN HÀNG VỚI TIMELINE & BẢN ĐỒ
     */
    public function showOrder($orderId)
    {
        $hub = $this->getCurrentHub();
        if (!$hub) {
            return redirect()->route('home')->with('error', 'Bạn chưa được phân bưu cục.');
        }

        $order = Order::where('post_office_id', $hub->post_office_id)
            ->with([
                'products',
                'images',
                'deliveryImages',
                'orderGroup',
                'delivery.driver',
                'deliveryIssues.reporter'
            ])
            ->findOrFail($orderId);

        // Chuẩn bị dữ liệu map
        $mapData = $this->prepareMapData($order, $hub);

        return view('hub.orders.show', compact('hub', 'order', 'mapData'));
    }

    /**
     * ✅ API: LẤY TRACKING UPDATES THEO THỜI GIAN THỰC
     */
    public function getTrackingUpdates(Request $request, $orderId)
    {
        try {
            $hub = $this->getCurrentHub();
            if (!$hub) {
                return response()->json(['error' => 'Bạn chưa được phân bưu cục.'], 403);
            }

            $order = Order::where('post_office_id', $hub->post_office_id)
                ->with(['delivery', 'deliveryIssues'])
                ->findOrFail($orderId);

            $lastUpdate = $request->query('last_update', 0);

            // Lấy tracking mới
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
            Log::error('Hub tracking updates error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Không thể lấy tracking updates.'
            ], 500);
        }
    }

    /**
     * ✅ API: LẤY VỊ TRÍ ĐƠN HÀNG CHO MAP
     */
    public function getOrderLocation($orderId)
    {
        try {
            $hub = $this->getCurrentHub();
            if (!$hub) {
                return response()->json(['error' => 'Bạn chưa được phân bưu cục.'], 403);
            }

            $order = Order::where('post_office_id', $hub->post_office_id)
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

            // Vị trí bưu cục
            if ($hub->hasCoordinates()) {
                $locations[] = [
                    'type' => 'hub',
                    'lat' => (float) $hub->hub_latitude,
                    'lng' => (float) $hub->hub_longitude,
                    'label' => 'Bưu cục',
                    'address' => $hub->hub_address ?? 'Bưu cục trung tâm',
                    'icon' => 'hub'
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

            // Vị trí giao hàng thực tế
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

            // Vị trí các sự cố
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
            Log::error('Hub order location error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Không thể tải vị trí đơn hàng.'
            ], 500);
        }
    }

    /**
     * ✅ CHUẨN BỊ DỮ LIỆU MAP
     */
    private function prepareMapData($order, $hub)
    {
        $locations = [];

        // Điểm lấy hàng
        if ($order->actual_pickup_time && $order->pickup_latitude && $order->pickup_longitude) {
            $pickupAddress = $this->getAddressFromCoordinates(
                $order->pickup_latitude, 
                $order->pickup_longitude
            ) ?? 'Vị trí lấy hàng thực tế';
            
            $locations['sender'] = [
                'lat' => (float) $order->pickup_latitude,
                'lng' => (float) $order->pickup_longitude,
                'address' => $pickupAddress,
                'is_actual' => true,
            ];
        } elseif ($order->sender_latitude && $order->sender_longitude) {
            $locations['sender'] = [
                'lat' => (float) $order->sender_latitude,
                'lng' => (float) $order->sender_longitude,
                'address' => 'Điểm lấy hàng dự kiến',
                'is_actual' => false,
            ];
        }

        // Vị trí bưu cục
        if ($hub->hasCoordinates()) {
            $locations['hub'] = [
                'lat' => (float) $hub->hub_latitude,
                'lng' => (float) $hub->hub_longitude,
                'address' => $hub->hub_address ?? 'Bưu cục trung tâm',
            ];
        }

        // Điểm giao hàng
        if ($order->recipient_latitude && $order->recipient_longitude) {
            $locations['recipient'] = [
                'lat' => (float) $order->recipient_latitude,
                'lng' => (float) $order->recipient_longitude,
                'address' => $order->recipient_full_address
            ];
        }

        // Vị trí giao hàng thực tế
        if ($order->delivery && $order->delivery->delivery_latitude && $order->delivery->delivery_longitude) {
            $locations['actual_delivery'] = [
                'lat' => (float) $order->delivery->delivery_latitude,
                'lng' => (float) $order->delivery->delivery_longitude,
                'address' => $order->delivery->delivery_address ?: $order->recipient_full_address,
                'time' => $order->delivery->actual_delivery_time?->format('H:i d/m/Y')
            ];
        }

        // Vị trí sự cố
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

        // Tracking points
        $trackingPoints = [];
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

        return [
            'locations' => $locations,
            'issues' => $issues,
            'tracking_points' => $trackingPoints,
            'has_locations' => count($locations) > 0,
            'is_in_transit' => $order->isInTransit(),
            'last_update' => now()->timestamp
        ];
    }

    /**
     * ✅ ĐẾM SỐ LƯỢNG ĐƠN THEO TRẠNG THÁI
     */
    private function getHubStatusCounts($hub)
{
    // ✅ Lấy đếm theo status thực tế
    $counts = Order::where('post_office_id', $hub->post_office_id)
        ->selectRaw('status, COUNT(*) as count')
        ->groupBy('status')
        ->pluck('count', 'status')
        ->toArray();

    return [
        'pending' => $counts['pending'] ?? 0,
        'confirmed' => $counts['confirmed'] ?? 0,
        'picking_up' => $counts['picking_up'] ?? 0,
        'picked_up' => $counts['picked_up'] ?? 0,
        'at_hub' => $counts['at_hub'] ?? 0,
        'shipping' => $counts['shipping'] ?? 0,
        'delivered' => $counts['delivered'] ?? 0,
        'cancelled' => $counts['cancelled'] ?? 0,
        'returning' => $counts['returning'] ?? 0,   // ← PHẢI CÓ
        'returned' => $counts['returned'] ?? 0,     // ← PHẢI CÓ
    ];
    }

    private function getAvailableDrivers(Hub $hub, Order $order)
    {
        $maxDistance = 20;

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
            ->filter(fn($d) => $d['belongs_to_hub'])
            ->values();

        $onlineDrivers = $drivers->where('is_online', true)->sortBy('distance_to_order')->values();

        if ($onlineDrivers->isEmpty()) {
            $offlineDrivers = $drivers
                ->where('is_online', false)
                ->sortBy(function ($d) {
                    return $d['distance_to_hub'] + ($d['distance_to_order'] * 0.5);
                })
                ->values();

            return $offlineDrivers;
        }

        return $onlineDrivers;
    }

    private function isDriverEligible(User $driver, Hub $hub, Order $order)
    {
        $driverProfile = DriverProfile::where('user_id', $driver->id)
            ->where('post_office_id', $hub->post_office_id)
            ->where('status', 'approved')
            ->first();

        if (!$driverProfile) {
            return false;
        }

        $userInfo = $driver->userInfo;
        if (!$userInfo || !$userInfo->latitude || !$userInfo->longitude) {
            return false;
        }

        $maxDistance = 20;
        $distanceToHub = $hub->hasCoordinates()
            ? $this->calculateDistance($userInfo->latitude, $userInfo->longitude, $hub->hub_latitude, $hub->hub_longitude)
            : INF;

        $distanceToOrder = ($order->recipient_latitude && $order->recipient_longitude)
            ? $this->calculateDistance($userInfo->latitude, $userInfo->longitude, $order->recipient_latitude, $order->recipient_longitude)
            : INF;

        return ($distanceToHub <= $maxDistance) || ($distanceToOrder <= $maxDistance);
    }

    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371;

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

    private function getCurrentHub()
    {
        return Hub::where('user_id', auth()->id())->first();
    }

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

    private function getAddressFromCoordinates($latitude, $longitude)
    {
        try {
            $apiKey = env('GOONG_API_KEY');
            
            if (!$apiKey) {
                return null;
            }
            
            $cacheKey = "geocode_{$latitude}_{$longitude}";
            
            if (Cache::has($cacheKey)) {
                return Cache::get($cacheKey);
            }
            
            $url = "https://rsapi.goong.io/Geocode?latlng={$latitude},{$longitude}&api_key={$apiKey}";
            
            $response = Http::timeout(5)->get($url);
            
            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['results'][0]['formatted_address'])) {
                    $address = $data['results'][0]['formatted_address'];
                    Cache::put($cacheKey, $address, now()->addHours(24));
                    return $address;
                }
            }
            
            return null;
            
        } catch (\Exception $e) {
            Log::error('Reverse geocoding error: ' . $e->getMessage());
            return null;
        }
    }
}