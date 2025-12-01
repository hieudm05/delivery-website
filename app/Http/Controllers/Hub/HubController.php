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
    public function index()
    {
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

        $order = Order::with(['orderGroup', 'products'])->findOrFail($orderId);
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
    // Thêm vào HubController.php

/**
 * ✅ FORM GOM ĐơN VÀ PHÁT HÀNG LOẠT
 */
public function batchAssignForm(Request $request)
{
    $hub = $this->getCurrentHub();
    if (!$hub) {
        return redirect()->route('home')->with('error', 'Bạn chưa được phân bưu cục.');
    }

    // Lấy các đơn chưa phát
    $orders = Order::where('post_office_id', $hub->post_office_id)
        ->whereIn('status', [Order::STATUS_AT_HUB])
        ->whereNull('driver_id')
        ->with(['orderGroup', 'products'])
        ->orderBy('created_at', 'desc')
        ->get();

    // Gợi ý gom đơn theo khu vực
    $suggestedGroups = $this->suggestOrderGroupsByLocation($orders);

    // Lấy danh sách tài xế khả dụng
    $availableDrivers = $this->getAllAvailableDrivers($hub);

    return view('hub.approval.batch-assign', compact('hub', 'orders', 'suggestedGroups', 'availableDrivers'));
}

/**
 * ✅ API: LẤY DANH SÁCH TÀI XẾ PHÙ HỢP CHO NHIỀU ĐƠN
 */
public function getBatchAvailableDrivers(Request $request)
{
    $request->validate([
        'order_ids' => 'required|array',
        'order_ids.*' => 'exists:orders,id'
    ]);

    $hub = $this->getCurrentHub();
    if (!$hub) {
        return response()->json(['error' => 'Bạn chưa được phân bưu cục.'], 403);
    }

    $orders = Order::whereIn('id', $request->order_ids)
        ->where('post_office_id', $hub->post_office_id)
        ->get();

    if ($orders->isEmpty()) {
        return response()->json(['error' => 'Không tìm thấy đơn hàng.'], 404);
    }

    // Tính trọng tâm (centroid) của các đơn hàng
    $centroid = $this->calculateCentroid($orders);

    // Lấy tài xế phù hợp
    $drivers = $this->getDriversForBatch($hub, $orders, $centroid);

    return response()->json([
        'success' => true,
        'drivers' => $drivers,
        'total_orders' => $orders->count(),
        'centroid' => $centroid,
        'total_cod' => $orders->sum('cod_amount'),
        'total_weight' => $orders->sum(function($order) {
            return $order->products->sum('weight');
        })
    ]);
}

/**
 * ✅ XỬ LÝ PHÁT ĐƠN HÀNG LOẠT
 */
public function batchAssignOrders(Request $request)
{
    $request->validate([
        'order_ids' => 'required|array|min:1',
        'order_ids.*' => 'exists:orders,id',
        'driver_id' => 'required|exists:users,id',
        'note' => 'nullable|string|max:500',
    ]);

    $hub = $this->getCurrentHub();
    if (!$hub) {
        return response()->json(['error' => 'Bạn chưa được phân bưu cục.'], 403);
    }

    try {
        DB::beginTransaction();

        $orders = Order::whereIn('id', $request->order_ids)
            ->where('post_office_id', $hub->post_office_id)
            ->whereNull('driver_id')
            ->get();

        if ($orders->isEmpty()) {
            DB::rollBack();
            return response()->json(['error' => 'Không có đơn hàng hợp lệ để phát.'], 400);
        }

        $driver = User::find($request->driver_id);

        // Kiểm tra tài xế thuộc hub
        $driverProfile = DriverProfile::where('user_id', $driver->id)
            ->where('post_office_id', $hub->post_office_id)
            ->where('status', 'approved')
            ->first();

        if (!$driverProfile) {
            DB::rollBack();
            return response()->json(['error' => 'Tài xế không thuộc bưu cục này.'], 400);
        }

        // Gán đơn cho tài xế
        $successCount = 0;
        foreach ($orders as $order) {
            $order->driver_id = $driver->id;
            $order->status = Order::STATUS_SHIPPING;
            
            if ($request->note) {
                $order->note = $order->note 
                    ? $order->note . "\n[Hub - Gom đơn] " . $request->note 
                    : "[Hub - Gom đơn] " . $request->note;
            }
            
            $order->save();

            // Cập nhật group status nếu có
            if ($order->isPartOfGroup()) {
                $order->orderGroup->updateGroupStatus();
            }

            $successCount++;
        }

        Log::info('Batch orders assigned', [
            'hub_id' => $hub->id,
            'driver_id' => $driver->id,
            'order_count' => $successCount,
            'order_ids' => $orders->pluck('id')->toArray(),
            'assigned_by' => auth()->id(),
        ]);

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => "Đã phát thành công {$successCount} đơn hàng cho tài xế {$driver->full_name}",
            'assigned_count' => $successCount,
            'driver' => [
                'id' => $driver->id,
                'name' => $driver->full_name,
                'phone' => $driver->phone,
            ]
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Batch assign failed', [
            'error' => $e->getMessage(),
            'order_ids' => $request->order_ids,
        ]);

        return response()->json(['error' => 'Có lỗi xảy ra khi phát đơn hàng loạt.'], 500);
    }
}

/**
 * ✅ GỢI Ý GOM ĐƠN THEO KHU VỰC
 */
private function suggestOrderGroupsByLocation($orders)
{
    if ($orders->isEmpty()) {
        return [];
    }

    $groups = [];
    $maxDistanceKm = 3; // Bán kính gom đơn: 3km

    foreach ($orders as $order) {
        if (!$order->recipient_latitude || !$order->recipient_longitude) {
            continue;
        }

        $foundGroup = false;

        // Tìm nhóm phù hợp
        foreach ($groups as $key => $group) {
            $distance = $this->calculateDistance(
                $order->recipient_latitude,
                $order->recipient_longitude,
                $group['centroid']['lat'],
                $group['centroid']['lng']
            );

            if ($distance <= $maxDistanceKm) {
                $groups[$key]['orders'][] = $order;
                $groups[$key]['centroid'] = $this->calculateCentroid(collect($groups[$key]['orders']));
                $foundGroup = true;
                break;
            }
        }

        // Tạo nhóm mới
        if (!$foundGroup) {
            $groups[] = [
                'orders' => [$order],
                'centroid' => [
                    'lat' => $order->recipient_latitude,
                    'lng' => $order->recipient_longitude
                ]
            ];
        }
    }

    // Chỉ giữ các nhóm có từ 2 đơn trở lên
    return array_values(array_filter($groups, function($group) {
        return count($group['orders']) >= 2;
    }));
}

/**
 * ✅ TÍNH TRỌNG TÂM CỦA CÁC ĐƠN HÀNG
 */
private function calculateCentroid($orders)
{
    $validOrders = $orders->filter(function($order) {
        return $order->recipient_latitude && $order->recipient_longitude;
    });

    if ($validOrders->isEmpty()) {
        return null;
    }

    $lat = $validOrders->avg('recipient_latitude');
    $lng = $validOrders->avg('recipient_longitude');

    return [
        'lat' => $lat,
        'lng' => $lng
    ];
}

/**
 * ✅ LẤY TÀI XẾ PHÙ HỢP CHO GOM ĐƠN
 */
private function getDriversForBatch($hub, $orders, $centroid)
{
    if (!$centroid) {
        return collect([]);
    }

    $maxDistance = 30; // km

    $drivers = User::query()
        ->where('role', 'driver')
        ->where('status', 'active')
        ->whereHas('userInfo', function ($q) use ($centroid, $maxDistance) {
            $q->whereRaw("
                (6371 * acos(
                    cos(radians(?)) * cos(radians(latitude)) *
                    cos(radians(longitude) - radians(?)) +
                    sin(radians(?)) * sin(radians(latitude))
                )) <= ?
            ", [$centroid['lat'], $centroid['lng'], $centroid['lat'], $maxDistance]);
        })
        ->with('userInfo')
        ->get()
        ->map(function ($driver) use ($hub, $centroid, $orders) {
            $userInfo = $driver->userInfo;
            
            // Kiểm tra thuộc hub
            $driverProfile = DriverProfile::where('user_id', $driver->id)
                ->where('post_office_id', $hub->post_office_id)
                ->where('status', 'approved')
                ->first();

            if (!$driverProfile || !$userInfo || !$userInfo->latitude || !$userInfo->longitude) {
                return null;
            }

            // ✅ KIỂM TRA: Tài xế có đang trong quá trình pickup không?
            $hasActivePickup = Order::where('pickup_driver_id', $driver->id)
                ->whereIn('status', [
                    Order::STATUS_PICKING_UP,
                    Order::STATUS_PICKED_UP
                ])
                ->exists();

            // ✅ KIỂM TRA: Tài xế có đang giao đơn nào không?
            $hasActiveDelivery = Order::where('driver_id', $driver->id)
                ->where('status', Order::STATUS_SHIPPING)
                ->exists();

            $distanceToCentroid = $this->calculateDistance(
                $userInfo->latitude,
                $userInfo->longitude,
                $centroid['lat'],
                $centroid['lng']
            );

            return [
                'id' => $driver->id,
                'name' => $driver->full_name,
                'phone' => $driver->phone,
                'avatar' => $driver->avatar_url,
                'is_online' => $driver->isOnline(),
                'distance_to_centroid' => round($distanceToCentroid, 2),
                'last_seen' => $driver->last_seen_at 
                    ? $driver->last_seen_at->diffForHumans() 
                    : 'Chưa cập nhật',
                'has_active_pickup' => $hasActivePickup,
                'has_active_delivery' => $hasActiveDelivery,
                'is_available' => !$hasActivePickup && !$hasActiveDelivery,
            ];
        })
        ->filter(function($driver) {
            // Chỉ lấy tài xế khả dụng (không đang pickup, không đang giao)
            return $driver && $driver['is_available'];
        })
        ->sortBy('distance_to_centroid')
        ->values();

    return $drivers;
}

/**
 * ✅ LẤY TẤT CẢ TÀI XẾ KHẢ DỤNG CỦA HUB
 */
private function getAllAvailableDrivers($hub)
{
    $drivers = User::query()
        ->where('role', 'driver')
        ->where('status', 'active')
        ->whereHas('driverProfile', function($q) use ($hub) {
            $q->where('post_office_id', $hub->post_office_id)
              ->where('status', 'approved');
        })
        ->with('userInfo')
        ->get()
        ->map(function ($driver) {
            return [
                'id' => $driver->id,
                'name' => $driver->full_name,
                'phone' => $driver->phone,
                'avatar' => $driver->avatar_url,
                'is_online' => $driver->isOnline(),
                'last_seen' => $driver->last_seen_at 
                    ? $driver->last_seen_at->diffForHumans() 
                    : 'Chưa cập nhật',
            ];
        })
        ->sortByDesc('is_online')
        ->values();

    return $drivers;
}
}