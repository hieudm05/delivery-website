<?php

namespace App\Http\Controllers\Admin\Orders;

use App\Http\Controllers\Controller;
use App\Models\Customer\Dashboard\Orders\Order;
use App\Models\Customer\Dashboard\Orders\OrderGroup;
use App\Models\Hub\Hub;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AdminOrderTrackingController extends Controller
{
    /**
     * ✅ TRANG DASHBOARD TRACKING - TỔNG QUAN TẤT CẢ ĐƠN
     */
    public function index(Request $request)
    {
        $status = $request->query('status', 'all');
        $search = $request->query('search', '');
        $hub_id = $request->query('hub_id', 'all');
        $date_from = $request->query('date_from', '');
        $date_to = $request->query('date_to', '');

        // Validate status
        $validStatuses = Order::STATUSES;
        if ($status !== 'all' && !in_array($status, $validStatuses)) {
            $status = 'all';
        }

        // Query builder
        $query = Order::with([
            'products', 
            'images', 
            'orderGroup', 
            'delivery', 
            'deliveryIssues'
        ])
        ->when($status !== 'all', function ($q) use ($status) {
            return $q->where('status', $status);
        })
        ->when($hub_id !== 'all', function ($q) use ($hub_id) {
            return $q->where('post_office_id', $hub_id);
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
        ->when($date_from, function ($q) use ($date_from) {
            return $q->whereDate('created_at', '>=', $date_from);
        })
        ->when($date_to, function ($q) use ($date_to) {
            return $q->whereDate('created_at', '<=', $date_to);
        })
        ->latest();

        $orders = $query->paginate(20)->appends([
            'status' => $status,
            'search' => $search,
            'hub_id' => $hub_id,
            'date_from' => $date_from,
            'date_to' => $date_to
        ]);

        // Thống kê
        $statistics = $this->getStatistics();
        
        // Danh sách Hub
        $hubs = Hub::with('user')->get();

        // AJAX request
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.orders.tracking._orders_list', compact('orders'))->render(),
                'pagination' => $orders->links()->render()
            ]);
        }

        return view('admin.orders.tracking.index', compact(
            'orders', 
            'statistics', 
            'hubs'
        ));
    }

    /**
     * ✅ TRANG BẢN ĐỒ TỔNG QUAN - REAL-TIME MAP
     */
    public function mapView()
    {
        // Lấy tất cả đơn đang vận chuyển
        $activeOrders = Order::whereIn('status', [
            Order::STATUS_PICKING_UP,
            Order::STATUS_PICKED_UP,
            Order::STATUS_AT_HUB,
            Order::STATUS_SHIPPING
        ])
        ->with(['delivery', 'deliveryIssues'])
        ->get();

        // Chuẩn bị dữ liệu cho map
        $mapData = $this->prepareOverviewMapData($activeOrders);

        // Thống kê
        $statistics = [
            'total_active' => $activeOrders->count(),
            'picking_up' => $activeOrders->where('status', Order::STATUS_PICKING_UP)->count(),
            'at_hub' => $activeOrders->where('status', Order::STATUS_AT_HUB)->count(),
            'shipping' => $activeOrders->where('status', Order::STATUS_SHIPPING)->count(),
            'with_issues' => $activeOrders->filter(fn($o) => $o->hasDeliveryIssues())->count(),
        ];

        return view('admin.orders.tracking.map', compact('mapData', 'statistics'));
    }

    /**
     * ✅ CHI TIẾT ĐƠN HÀNG
     */
    public function show($id)
    {
        $order = Order::with([
            'products',
            'images',
            'deliveryImages',
            'orderGroup',
            'delivery.driver',
            'deliveryIssues.reporter'
        ])->findOrFail($id);

        // Lấy hub
        $hub = null;
        if ($order->post_office_id) {
            $hub = Hub::where('post_office_id', $order->post_office_id)->first();
        }

        // Chuẩn bị dữ liệu map
        $mapData = $this->prepareOrderMapData($order, $hub);

        return view('admin.orders.tracking.show', compact('order', 'mapData', 'hub'));
    }

    /**
     * ✅ API: LẤY TRACKING UPDATES
     */
    public function getTrackingUpdates(Request $request, $orderId)
    {
        try {
            $order = Order::with(['delivery', 'deliveryIssues'])
                ->findOrFail($orderId);

            $lastUpdate = $request->query('last_update', 0);

            $newTrackings = $order->getTrackingUpdatesSince($lastUpdate);

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
            Log::error('Admin tracking updates error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Không thể lấy tracking updates.'
            ], 500);
        }
    }

    /**
     * ✅ API: LẤY TẤT CẢ ĐƠN ĐANG VẬN CHUYỂN (CHO MAP)
     */
    public function getActiveOrdersForMap()
    {
        try {
            $orders = Order::whereIn('status', [
                Order::STATUS_PICKING_UP,
                Order::STATUS_PICKED_UP,
                Order::STATUS_AT_HUB,
                Order::STATUS_SHIPPING
            ])
            ->with(['delivery', 'deliveryIssues'])
            ->get();

            $ordersData = $orders->map(function ($order) {
                $latestLocation = $this->getLatestOrderLocation($order);
                
                if (!$latestLocation) {
                    return null;
                }

                return [
                    'id' => $order->id,
                    'status' => $order->status,
                    'status_label' => $order->status_label,
                    'lat' => $latestLocation['lat'],
                    'lng' => $latestLocation['lng'],
                    'address' => $latestLocation['address'],
                    'sender_name' => $order->sender_name,
                    'recipient_name' => $order->recipient_name,
                    'has_issues' => $order->hasDeliveryIssues(),
                    'driver_id' => $order->driver_id,
                    'pickup_time' => $order->pickup_time->format('H:i d/m'),
                    'delivery_time' => $order->delivery_time->format('H:i d/m'),
                ];
            })->filter()->values();

            return response()->json([
                'success' => true,
                'orders' => $ordersData,
                'total' => $ordersData->count(),
                'timestamp' => now()->timestamp
            ]);

        } catch (\Exception $e) {
            Log::error('Get active orders error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Không thể lấy dữ liệu đơn hàng.'
            ], 500);
        }
    }

    /**
     * ✅ API: XEM VỊ TRÍ ĐƠN HÀNG
     */
    public function getOrderLocation($orderId)
    {
        try {
            $order = Order::with(['delivery', 'deliveryIssues'])
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

            // Vị trí hub
            if ($order->post_office_id) {
                $hub = Hub::where('post_office_id', $order->post_office_id)->first();
                if ($hub && $hub->hasCoordinates()) {
                    $locations[] = [
                        'type' => 'hub',
                        'lat' => (float) $hub->hub_latitude,
                        'lng' => (float) $hub->hub_longitude,
                        'label' => 'Bưu cục',
                        'address' => $hub->hub_address ?? 'Bưu cục trung tâm',
                        'icon' => 'hub'
                    ];
                }
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
            Log::error('Get order location error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Không thể tải vị trí đơn hàng.'
            ], 500);
        }
    }

    /**
     * ✅ THỐNG KÊ TỔNG QUAN
     */
    private function getStatistics()
    {
        $today = now()->startOfDay();
        
        return [
            // Thống kê theo trạng thái
            'status_counts' => Order::selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray(),
            
            // Thống kê hôm nay
            'today' => [
                'total' => Order::whereDate('created_at', $today)->count(),
                'delivered' => Order::where('status', Order::STATUS_DELIVERED)
                    ->whereDate('updated_at', $today)
                    ->count(),
                'in_transit' => Order::whereIn('status', [
                    Order::STATUS_PICKING_UP,
                    Order::STATUS_PICKED_UP,
                    Order::STATUS_AT_HUB,
                    Order::STATUS_SHIPPING
                ])->count(),
               'with_issues' => Order::whereHas('deliveryIssues', function ($q) use ($today) {
                    $q->whereDate('issue_time', $today);
                })->count(),

            ],
            
            // Thống kê theo hub
            'by_hub' => Order::join('hubs', 'orders.post_office_id', '=', 'hubs.post_office_id')
                ->select('hubs.id', 'hubs.hub_address', DB::raw('COUNT(*) as total'))
                ->groupBy('hubs.id', 'hubs.hub_address')
                ->orderByDesc('total')
                ->limit(10)
                ->get(),
            
            // Revenue
            'revenue' => [
                'today' => Order::where('status', Order::STATUS_DELIVERED)
                    ->whereDate('updated_at', $today)
                    ->sum('shipping_fee'),
                'month' => Order::where('status', Order::STATUS_DELIVERED)
                    ->whereMonth('updated_at', now()->month)
                    ->sum('shipping_fee'),
            ],
            
            // COD
            'cod' => [
                'pending' => Order::whereIn('status', [
                    Order::STATUS_PICKING_UP,
                    Order::STATUS_PICKED_UP,
                    Order::STATUS_AT_HUB,
                    Order::STATUS_SHIPPING
                ])
                ->where('cod_amount', '>', 0)
                ->sum('cod_amount'),
                'collected' => Order::where('status', Order::STATUS_DELIVERED)
                    ->whereDate('updated_at', $today)
                    ->sum('cod_amount'),
            ]
        ];
    }

    /**
     * ✅ CHUẨN BỊ DỮ LIỆU MAP TỔNG QUAN
     */
    private function prepareOverviewMapData($orders)
    {
        $markers = [];
        
        foreach ($orders as $order) {
            $location = $this->getLatestOrderLocation($order);
            
            if ($location) {
                $markers[] = [
                    'id' => $order->id,
                    'lat' => $location['lat'],
                    'lng' => $location['lng'],
                    'status' => $order->status,
                    'status_label' => $order->status_label,
                    'address' => $location['address'],
                    'sender_name' => $order->sender_name,
                    'recipient_name' => $order->recipient_name,
                    'has_issues' => $order->hasDeliveryIssues(),
                    'driver_id' => $order->driver_id,
                ];
            }
        }

        return [
            'markers' => $markers,
            'hubs' => Hub::whereNotNull('hub_latitude')
                ->whereNotNull('hub_longitude')
                ->get()
                ->map(fn($hub) => [
                    'id' => $hub->id,
                    'lat' => (float) $hub->hub_latitude,
                    'lng' => (float) $hub->hub_longitude,
                    'address' => $hub->hub_address ?? 'Bưu cục',
                ]),
        ];
    }

    /**
     * ✅ CHUẨN BỊ DỮ LIỆU MAP CHO ĐƠN HÀNG
     */
    private function prepareOrderMapData($order, $hub)
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
        if ($hub && $hub->hasCoordinates()) {
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
     * ✅ LẤY VỊ TRÍ MỚI NHẤT CỦA ĐƠN HÀNG
     */
    private function getLatestOrderLocation($order)
    {
        // Priority: actual delivery > recipient > hub > pickup > sender
        
        if ($order->delivery && $order->delivery->delivery_latitude && $order->delivery->delivery_longitude) {
            return [
                'lat' => (float) $order->delivery->delivery_latitude,
                'lng' => (float) $order->delivery->delivery_longitude,
                'address' => $order->delivery->delivery_address ?: $order->recipient_full_address,
            ];
        }

        if ($order->status === Order::STATUS_SHIPPING && $order->recipient_latitude && $order->recipient_longitude) {
            return [
                'lat' => (float) $order->recipient_latitude,
                'lng' => (float) $order->recipient_longitude,
                'address' => $order->recipient_full_address,
            ];
        }

        if ($order->status === Order::STATUS_AT_HUB && $order->post_office_id) {
            $hub = Hub::where('post_office_id', $order->post_office_id)->first();
            if ($hub && $hub->hasCoordinates()) {
                return [
                    'lat' => (float) $hub->hub_latitude,
                    'lng' => (float) $hub->hub_longitude,
                    'address' => $hub->hub_address ?? 'Bưu cục',
                ];
            }
        }

        if ($order->pickup_latitude && $order->pickup_longitude) {
            return [
                'lat' => (float) $order->pickup_latitude,
                'lng' => (float) $order->pickup_longitude,
                'address' => $this->getAddressFromCoordinates(
                    $order->pickup_latitude,
                    $order->pickup_longitude
                ) ?? 'Vị trí lấy hàng',
            ];
        }

        if ($order->sender_latitude && $order->sender_longitude) {
            return [
                'lat' => (float) $order->sender_latitude,
                'lng' => (float) $order->sender_longitude,
                'address' => $order->sender_address,
            ];
        }

        return null;
    }

    /**
     * ✅ REVERSE GEOCODING
     */
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