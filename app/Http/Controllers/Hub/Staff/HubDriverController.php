<?php

namespace App\Http\Controllers\Hub\Staff;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Driver\DriverProfile;
use App\Models\BankAccount;
use App\Models\Customer\Dashboard\Orders\Order;
use App\Models\Driver\Orders\OrderDelivery;
use App\Models\Hub\Hub;
use App\Models\Driver\Orders\OrderDeliveryIssue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class HubDriverController extends Controller
{
    /**
     * Danh sách tất cả driver của hub
     */
    public function index(Request $request)
    {
        // Lấy thông tin hub của user hiện tại
        $hub = Hub::where('user_id', auth()->id())->first();
        
        if (!$hub) {
            return back()->with('error', 'Bạn chưa được gán quản lý bưu cục nào');
        }

        $postOfficeId = $hub->post_office_id;

        $search = $request->get('search');
        $status = $request->get('status', 'all');
        $vehicleType = $request->get('vehicle_type');

        $drivers = User::where('role', 'driver')
            ->whereHas('driverProfile', function($q) use ($postOfficeId) {
                $q->where('post_office_id', $postOfficeId);
            })
            ->with(['driverProfile', 'userInfo'])
            ->when($search, function($q) use ($search) {
                $q->where(function($query) use ($search) {
                    $query->where('full_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($status !== 'all', function($q) use ($status) {
                $q->where('status', $status);
            })
            ->when($vehicleType, function($q) use ($vehicleType) {
                $q->whereHas('driverProfile', function($query) use ($vehicleType) {
                    $query->where('vehicle_type', $vehicleType);
                });
            })
            ->orderBy('last_seen_at', 'desc')
            ->paginate(20);

        // Lấy thống kê tổng quan
        $stats = [
            'total' => User::where('role', 'driver')
                ->whereHas('driverProfile', function($q) use ($postOfficeId) {
                    $q->where('post_office_id', $postOfficeId);
                })->count(),
            'active' => User::where('role', 'driver')
                ->where('status', 'active')
                ->whereHas('driverProfile', function($q) use ($postOfficeId) {
                    $q->where('post_office_id', $postOfficeId);
                })->count(),
            'online' => User::where('role', 'driver')
                ->where('last_seen_at', '>=', now()->subSeconds(90))
                ->whereHas('driverProfile', function($q) use ($postOfficeId) {
                    $q->where('post_office_id', $postOfficeId);
                })->count(),
            'blocked' => User::where('role', 'driver')
                ->where('status', 'blocked')
                ->whereHas('driverProfile', function($q) use ($postOfficeId) {
                    $q->where('post_office_id', $postOfficeId);
                })->count(),
        ];

        return view('hub.drivers.index', compact('drivers', 'stats', 'search', 'status', 'vehicleType'));
    }

    /**
     * Chi tiết driver
     */
    public function show($id)
    {
        $driver = User::where('role', 'driver')
            ->with(['driverProfile', 'userInfo'])
            ->findOrFail($id);

        // Kiểm tra driver có thuộc hub này không
        $hub = Hub::where('user_id', auth()->id())->first();
        
        if (!$hub || $driver->driverProfile->post_office_id != $hub->post_office_id) {
            abort(403, 'Bạn không có quyền xem thông tin driver này');
        }

        // Lấy tài khoản ngân hàng
        $bankAccounts = BankAccount::where('user_id', $driver->id)
            ->orderBy('is_primary', 'desc')
            ->get();

        // Lấy thống kê giao hàng
        $deliveryStats = $this->getDriverDeliveryStats($driver->id);

        // Lấy lịch sử giao hàng gần đây - THÔNG QUA BẢNG order_deliveries
        $recentDeliveries = OrderDelivery::where('delivery_driver_id', $driver->id)
            ->with(['order', 'images', 'issues'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return view('hub.drivers.show', compact(
            'driver',
            'bankAccounts',
            'deliveryStats',
            'recentDeliveries'
        ));
    }

    /**
     * Lấy thống kê giao hàng của driver - THÔNG QUA BẢNG order_deliveries
     */
    private function getDriverDeliveryStats($driverId)
    {
        $today = Carbon::today();
        $thisWeek = Carbon::now()->startOfWeek();
        $thisMonth = Carbon::now()->startOfMonth();

        return [
            'today' => [
                'total' => OrderDelivery::where('delivery_driver_id', $driverId)
                    ->whereDate('created_at', $today)
                    ->count(),
                'delivered' => OrderDelivery::where('delivery_driver_id', $driverId)
                    ->whereNotNull('actual_delivery_time')
                    ->whereDate('actual_delivery_time', $today)
                    ->count(),
                'failed' => OrderDeliveryIssue::whereHas('order.delivery', function($q) use ($driverId) {
                        $q->where('delivery_driver_id', $driverId);
                    })
                    ->whereDate('issue_time', $today)
                    ->count(),
            ],
            'week' => [
                'total' => OrderDelivery::where('delivery_driver_id', $driverId)
                    ->where('created_at', '>=', $thisWeek)
                    ->count(),
                'delivered' => OrderDelivery::where('delivery_driver_id', $driverId)
                    ->whereNotNull('actual_delivery_time')
                    ->where('actual_delivery_time', '>=', $thisWeek)
                    ->count(),
            ],
            'month' => [
                'total' => OrderDelivery::where('delivery_driver_id', $driverId)
                    ->where('created_at', '>=', $thisMonth)
                    ->count(),
                'delivered' => OrderDelivery::where('delivery_driver_id', $driverId)
                    ->whereNotNull('actual_delivery_time')
                    ->where('actual_delivery_time', '>=', $thisMonth)
                    ->count(),
                'cod_collected' => OrderDelivery::where('delivery_driver_id', $driverId)
                    ->whereNotNull('actual_delivery_time')
                    ->where('actual_delivery_time', '>=', $thisMonth)
                    ->sum('cod_collected_amount'),
            ],
            'all_time' => [
                'total' => OrderDelivery::where('delivery_driver_id', $driverId)->count(),
                'delivered' => OrderDelivery::where('delivery_driver_id', $driverId)
                    ->whereNotNull('actual_delivery_time')
                    ->count(),
                'success_rate' => $this->calculateSuccessRate($driverId),
            ]
        ];
    }

    /**
     * Tính tỷ lệ giao hàng thành công - THÔNG QUA BẢNG order_deliveries
     */
    private function calculateSuccessRate($driverId)
    {
        $total = OrderDelivery::where('delivery_driver_id', $driverId)->count();

        if ($total === 0) return 0;

        $delivered = OrderDelivery::where('delivery_driver_id', $driverId)
            ->whereNotNull('actual_delivery_time')
            ->count();

        return round(($delivered / $total) * 100, 2);
    }

    /**
     * Lịch sử giao hàng theo ngày - THÔNG QUA BẢNG order_deliveries
     */
    public function deliveryHistory(Request $request, $id)
    {
        $driver = User::where('role', 'driver')->findOrFail($id);

        // Kiểm tra quyền
        $hub = Hub::where('user_id', auth()->id())->first();
        
        if (!$hub || $driver->driverProfile->post_office_id != $hub->post_office_id) {
            abort(403, 'Bạn không có quyền xem thông tin driver này');
        }

        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        // Lấy dữ liệu giao hàng theo ngày thông qua bảng order_deliveries
        $dailyStats = OrderDelivery::where('delivery_driver_id', $driver->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN actual_delivery_time IS NOT NULL THEN 1 ELSE 0 END) as delivered'),
                DB::raw('COUNT(*) - SUM(CASE WHEN actual_delivery_time IS NOT NULL THEN 1 ELSE 0 END) as pending')
            )
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->get();

        return view('hub.drivers.delivery-history', compact(
            'driver',
            'dailyStats',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Cập nhật trạng thái driver (khóa/mở khóa)
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:active,blocked',
            'reason' => 'required_if:status,blocked|nullable|string|max:500'
        ]);

        $driver = User::where('role', 'driver')->findOrFail($id);

        // Kiểm tra quyền
        $hub = Hub::where('user_id', auth()->id())->first();
        
        if (!$hub || $driver->driverProfile->post_office_id != $hub->post_office_id) {
            abort(403, 'Bạn không có quyền cập nhật driver này');
        }

        $driver->status = $request->status;
        $driver->save();

        // Lưu log (nếu có package activity log)
        if (function_exists('activity')) {
            activity()
                ->performedOn($driver)
                ->causedBy(auth()->user())
                ->withProperties([
                    'status' => $request->status,
                    'reason' => $request->reason
                ])
                ->log($request->status === 'blocked' ? 'Khóa tài khoản driver' : 'Mở khóa tài khoản driver');
        }

        $message = $request->status === 'blocked' 
            ? 'Đã khóa tài khoản driver thành công' 
            : 'Đã mở khóa tài khoản driver thành công';

        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }

    /**
     * Xem vị trí hiện tại của driver trên bản đồ
     */
    public function location($id)
    {
        $driver = User::where('role', 'driver')
            ->with(['driverProfile', 'userInfo'])
            ->findOrFail($id);

        // Kiểm tra quyền
        $hub = Hub::where('user_id', auth()->id())->first();
        
        if (!$hub || $driver->driverProfile->post_office_id != $hub->post_office_id) {
            abort(403, 'Bạn không có quyền xem vị trí driver này');
        }

        // Lấy vị trí hiện tại từ userInfo
        $currentLocation = [
            'lat' => $driver->userInfo->latitude ?? null,
            'lng' => $driver->userInfo->longitude ?? null,
            'address' => $driver->userInfo->full_address ?? null,
            'last_updated' => $driver->last_seen_at
        ];

        // Lấy các đơn hàng đang giao - THÔNG QUA BẢNG order_deliveries
        $activeOrders = OrderDelivery::where('delivery_driver_id', $driver->id)
        ->whereNull('actual_delivery_time')
        ->whereNotNull('actual_delivery_start_time')
        ->with(['order'])
        ->get();

        $activeOrdersJson = $activeOrders->map(function($delivery) {
        $order = $delivery->order;
        return [
            'id' => $order->id,
            'lat' => $order->recipient_latitude,
            'lng' => $order->recipient_longitude,
            'recipient_name' => $order->recipient_name,
            'recipient_phone' => $order->recipient_phone,
            'recipient_address' => $order->recipient_full_address,
            'start_time' => $delivery->actual_delivery_start_time?->format('H:i d/m/Y')
        ];
        })->toJson();

        return view('hub.drivers.location', compact(
            'driver',
            'currentLocation',
            'activeOrders',
            'activeOrdersJson'
        ));
    }

    /**
     * Báo cáo tổng hợp của tất cả driver - THÔNG QUA BẢNG order_deliveries
     */
    public function report(Request $request)
    {
        // Lấy thông tin hub của user hiện tại
        $hub = Hub::where('user_id', auth()->id())->first();
        
        if (!$hub) {
            return back()->with('error', 'Bạn chưa được gán quản lý bưu cục nào');
        }

        $postOfficeId = $hub->post_office_id;
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        // Lấy thống kê theo driver
        $driverStats = User::where('role', 'driver')
            ->whereHas('driverProfile', function($q) use ($postOfficeId) {
                $q->where('post_office_id', $postOfficeId);
            })
            ->with('driverProfile')
            ->get()
            ->map(function($driver) use ($startDate, $endDate) {
                $total = OrderDelivery::where('delivery_driver_id', $driver->id)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->count();

                $delivered = OrderDelivery::where('delivery_driver_id', $driver->id)
                    ->whereNotNull('actual_delivery_time')
                    ->whereBetween('actual_delivery_time', [$startDate, $endDate])
                    ->count();

                $codCollected = OrderDelivery::where('delivery_driver_id', $driver->id)
                    ->whereNotNull('actual_delivery_time')
                    ->whereBetween('actual_delivery_time', [$startDate, $endDate])
                    ->sum('cod_collected_amount');

                return [
                    'driver' => $driver,
                    'total' => $total,
                    'delivered' => $delivered,
                    'success_rate' => $total > 0 ? round(($delivered / $total) * 100, 2) : 0,
                    'cod_collected' => $codCollected
                ];
            })
            ->sortByDesc('delivered');

        return view('hub.drivers.report', compact(
            'driverStats',
            'startDate',
            'endDate'
        ));
    }
}