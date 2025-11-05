<?php

namespace App\Http\Controllers\Drivers;

use App\Http\Controllers\Controller;
use App\Models\Customer\Dashboard\Orders\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DriverStatisticsController extends Controller
{
    /**
     * Trang dashboard thống kê của driver
     */
    public function dashboard()
    {
        $driverId = auth()->id();
        
        // Thống kê tổng quan
        $stats = $this->getOverviewStats($driverId);
        
        // Thống kê theo ngày
        $dailyStats = $this->getDailyStats($driverId);
        
        // Đơn hàng gần đây
        $recentOrders = Order::where('delivery_driver_id', $driverId)
            ->orderBy('actual_delivery_time', 'desc')
            ->take(10)
            ->get();
        
        return view('driver.dashboard', compact('stats', 'dailyStats', 'recentOrders'));
    }

    /**
     * API: Thống kê tổng quan
     */
    public function getOverviewStats($driverId)
    {
        return [
            // Hôm nay
            'today' => [
                'total' => Order::where('delivery_driver_id', $driverId)
                    ->whereDate('actual_delivery_time', today())
                    ->count(),
                'completed' => Order::where('delivery_driver_id', $driverId)
                    ->where('status', Order::STATUS_DELIVERED)
                    ->whereDate('actual_delivery_time', today())
                    ->count(),
                'failed' => Order::where('delivery_driver_id', $driverId)
                    ->whereNotNull('delivery_issue_time')
                    ->whereDate('delivery_issue_time', today())
                    ->count(),
                'cod_collected' => Order::where('delivery_driver_id', $driverId)
                    ->where('status', Order::STATUS_DELIVERED)
                    ->whereDate('actual_delivery_time', today())
                    ->sum('cod_collected_amount'),
            ],
            
            // Tuần này
            'week' => [
                'total' => Order::where('delivery_driver_id', $driverId)
                    ->whereBetween('actual_delivery_time', [now()->startOfWeek(), now()->endOfWeek()])
                    ->count(),
                'completed' => Order::where('delivery_driver_id', $driverId)
                    ->where('status', Order::STATUS_DELIVERED)
                    ->whereBetween('actual_delivery_time', [now()->startOfWeek(), now()->endOfWeek()])
                    ->count(),
                'cod_collected' => Order::where('delivery_driver_id', $driverId)
                    ->where('status', Order::STATUS_DELIVERED)
                    ->whereBetween('actual_delivery_time', [now()->startOfWeek(), now()->endOfWeek()])
                    ->sum('cod_collected_amount'),
            ],
            
            // Tháng này
            'month' => [
                'total' => Order::where('delivery_driver_id', $driverId)
                    ->whereMonth('actual_delivery_time', now()->month)
                    ->whereYear('actual_delivery_time', now()->year)
                    ->count(),
                'completed' => Order::where('delivery_driver_id', $driverId)
                    ->where('status', Order::STATUS_DELIVERED)
                    ->whereMonth('actual_delivery_time', now()->month)
                    ->whereYear('actual_delivery_time', now()->year)
                    ->count(),
                'cod_collected' => Order::where('delivery_driver_id', $driverId)
                    ->where('status', Order::STATUS_DELIVERED)
                    ->whereMonth('actual_delivery_time', now()->month)
                    ->whereYear('actual_delivery_time', now()->year)
                    ->sum('cod_collected_amount'),
            ],
            
            // Tổng cộng
            'total' => [
                'all_orders' => Order::where('delivery_driver_id', $driverId)->count(),
                'completed' => Order::where('delivery_driver_id', $driverId)
                    ->where('status', Order::STATUS_DELIVERED)
                    ->count(),
                'failed' => Order::where('delivery_driver_id', $driverId)
                    ->whereNotNull('delivery_issue_time')
                    ->count(),
                'success_rate' => $this->calculateSuccessRate($driverId),
            ]
        ];
    }

    /**
     * Thống kê theo từng ngày (7 ngày gần nhất)
     */
    public function getDailyStats($driverId)
    {
        $stats = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            
            $stats[] = [
                'date' => $date->format('Y-m-d'),
                'date_label' => $date->format('d/m'),
                'completed' => Order::where('delivery_driver_id', $driverId)
                    ->where('status', Order::STATUS_DELIVERED)
                    ->whereDate('actual_delivery_time', $date)
                    ->count(),
                'failed' => Order::where('delivery_driver_id', $driverId)
                    ->whereNotNull('delivery_issue_time')
                    ->whereDate('delivery_issue_time', $date)
                    ->count(),
                'cod_collected' => Order::where('delivery_driver_id', $driverId)
                    ->where('status', Order::STATUS_DELIVERED)
                    ->whereDate('actual_delivery_time', $date)
                    ->sum('cod_collected_amount'),
            ];
        }
        
        return $stats;
    }

    /**
     * Tính tỷ lệ giao hàng thành công
     */
    private function calculateSuccessRate($driverId)
    {
        $total = Order::where('delivery_driver_id', $driverId)->count();
        
        if ($total === 0) {
            return 0;
        }
        
        $success = Order::where('delivery_driver_id', $driverId)
            ->where('status', Order::STATUS_DELIVERED)
            ->count();
        
        return round(($success / $total) * 100, 2);
    }

    /**
     * API: Thống kê chi tiết theo khoảng thời gian
     */
    public function getStatsByPeriod(Request $request)
    {
        $request->validate([
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
        ]);

        $driverId = auth()->id();
        $fromDate = $request->from_date;
        $toDate = $request->to_date;

        $orders = Order::where('delivery_driver_id', $driverId)
            ->whereBetween('actual_delivery_time', [$fromDate, $toDate])
            ->get();

        $stats = [
            'period' => [
                'from' => $fromDate,
                'to' => $toDate,
            ],
            'total_orders' => $orders->count(),
            'completed' => $orders->where('status', Order::STATUS_DELIVERED)->count(),
            'failed' => $orders->whereNotNull('delivery_issue_time')->count(),
            'cod_collected' => $orders->where('status', Order::STATUS_DELIVERED)->sum('cod_collected_amount'),
            'success_rate' => $orders->count() > 0 
                ? round(($orders->where('status', Order::STATUS_DELIVERED)->count() / $orders->count()) * 100, 2)
                : 0,
            
            // Thống kê theo lý do thất bại
            'failure_reasons' => Order::where('delivery_driver_id', $driverId)
                ->whereBetween('delivery_issue_time', [$fromDate, $toDate])
                ->whereNotNull('delivery_issue_type')
                ->select('delivery_issue_type', DB::raw('count(*) as count'))
                ->groupBy('delivery_issue_type')
                ->get()
                ->mapWithKeys(function($item) {
                    return [$item->delivery_issue_type => $item->count];
                }),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Lịch sử giao hàng
     */
    public function deliveryHistory(Request $request)
    {
        $driverId = auth()->id();
        $status = $request->get('status', 'all');
        $perPage = $request->get('per_page', 20);

        $orders = Order::where('delivery_driver_id', $driverId)
            ->when($status === 'completed', function($q) {
                $q->where('status', Order::STATUS_DELIVERED);
            })
            ->when($status === 'failed', function($q) {
                $q->whereNotNull('delivery_issue_time');
            })
            ->with(['orderGroup'])
            ->orderBy('actual_delivery_time', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $orders->map(function($order) {
                return [
                    'id' => $order->id,
                    'recipient_name' => $order->recipient_name,
                    'recipient_phone' => $order->recipient_phone,
                    'recipient_address' => $order->recipient_full_address,
                    'status' => $order->status,
                    'status_label' => $order->status_label,
                    'actual_delivery_time' => $order->actual_delivery_time?->format('Y-m-d H:i:s'),
                    'cod_collected' => $order->cod_collected_amount,
                    'delivery_issue_type' => $order->delivery_issue_type,
                    'delivery_issue_note' => $order->delivery_issue_note,
                ];
            }),
            'pagination' => [
                'total' => $orders->total(),
                'per_page' => $orders->perPage(),
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
            ]
        ]);
    }
}