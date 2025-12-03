<?php

namespace App\Http\Controllers\Customer\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Customer\Dashboard\Orders\Order;
use App\Models\Customer\Dashboard\Orders\OrderGroup;
use App\Models\Customer\Dashboard\Orders\CodTransaction;
use App\Models\Driver\Orders\OrderReturn;
use App\Models\SenderDebt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardCustomerController extends Controller
{
    /**
     * ✅ DASHBOARD TỔNG QUAN - STATISTICS ĐẦY ĐỦ
     */
    public function index(Request $request)
    {
        $customerId = Auth::id();
        
        // Lấy khoảng thời gian filter
        $period = $request->get('period', '30days');
        $dateRange = $this->getDateRange($period);
        
        // ========== 1. THỐNG KÊ ĐỐN HÀNG ==========
        $orderStats = $this->getOrderStatistics($customerId, $dateRange);
        
        // ========== 2. THỐNG KÊ TÀI CHÍNH ==========
        $financialStats = $this->getFinancialStatistics($customerId, $dateRange);
        
        // ========== 3. THỐNG KÊ COD & PHÍ ==========
        $codStats = $this->getCodStatistics($customerId, $dateRange);
        
        // ========== 4. THỐNG KÊ CÔNG NỢ ==========
        $debtStats = $this->getDebtStatistics($customerId);
        
        // ========== 5. THỐNG KÊ HOÀN HÀNG ==========
        $returnStats = $this->getReturnStatistics($customerId, $dateRange);
        
        // ========== 6. THỐNG KÊ THEO THỜI GIAN ==========
        $timelineStats = $this->getTimelineStatistics($customerId, $dateRange);
        
        // ========== 7. TOP TUYẾN ĐƯỜNG ==========
        $topRoutes = $this->getTopRoutes($customerId, $dateRange);
        
        // ========== 8. ĐÁNH GIÁ HIỆU SUẤT ==========
        $performanceScore = $this->calculatePerformanceScore($orderStats, $returnStats);
        
        // ========== 9. ORDERS GẦN ĐÂY ==========
        $recentOrders = Order::where('sender_id', $customerId)
            ->with(['delivery', 'deliveryIssues', 'activeReturn'])
            ->latest()
            ->limit(5)
            ->get();
        
        // ========== 10. THÔNG BÁO/CẢNH BÁO ==========
        $alerts = $this->getAlerts($customerId, $debtStats, $codStats);
        
        return view('customer.dashboard.index', compact(
            'orderStats',
            'financialStats',
            'codStats',
            'debtStats',
            'returnStats',
            'timelineStats',
            'topRoutes',
            'performanceScore',
            'recentOrders',
            'alerts',
            'period',
            'dateRange'
        ));
    }

    /**
     * ========== HELPER METHODS ==========
     */

    /**
     * 1. Thống kê đơn hàng chi tiết
     */
    private function getOrderStatistics($customerId, $dateRange)
    {
        $baseQuery = Order::where('sender_id', $customerId)
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']]);
        
        $total = $baseQuery->count();
        $pending = (clone $baseQuery)->where('status', Order::STATUS_PENDING)->count();
        $confirmed = (clone $baseQuery)->where('status', Order::STATUS_CONFIRMED)->count();
        $pickingUp = (clone $baseQuery)->where('status', Order::STATUS_PICKING_UP)->count();
        $pickedUp = (clone $baseQuery)->where('status', Order::STATUS_PICKED_UP)->count();
        $atHub = (clone $baseQuery)->where('status', Order::STATUS_AT_HUB)->count();
        $shipping = (clone $baseQuery)->where('status', Order::STATUS_SHIPPING)->count();
        $delivered = (clone $baseQuery)->where('status', Order::STATUS_DELIVERED)->count();
        $returned = (clone $baseQuery)->where('status', Order::STATUS_RETURNED)->count();
        $cancelled = (clone $baseQuery)->where('status', Order::STATUS_CANCELLED)->count();
        
        // Tính success rate
        $completed = $delivered + $returned;
        $successRate = $total > 0 ? round(($delivered / $total) * 100, 1) : 0;
        $completionRate = $total > 0 ? round(($completed / $total) * 100, 1) : 0;
        
        // Thời gian xử lý trung bình
        $avgProcessingTime = Order::where('sender_id', $customerId)
            ->where('status', Order::STATUS_DELIVERED)
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, updated_at)) as avg_hours')
            ->value('avg_hours');
        
        // Đơn có group vs standalone
        $groupOrders = OrderGroup::where('user_id', $customerId)
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->count();
        
        $standaloneOrders = (clone $baseQuery)->whereNull('order_group_id')->count();
        
        return [
            'total' => $total,
            'by_status' => [
                'pending' => $pending,
                'confirmed' => $confirmed,
                'picking_up' => $pickingUp,
                'picked_up' => $pickedUp,
                'at_hub' => $atHub,
                'shipping' => $shipping,
                'delivered' => $delivered,
                'returned' => $returned,
                'cancelled' => $cancelled,
            ],
            'in_progress' => $pending + $confirmed + $pickingUp + $pickedUp + $atHub + $shipping,
            'completed' => $completed,
            'success_rate' => $successRate,
            'completion_rate' => $completionRate,
            'avg_processing_hours' => round($avgProcessingTime ?? 0, 1),
            'group_orders' => $groupOrders,
            'standalone_orders' => $standaloneOrders,
            'total_recipients' => Order::where('sender_id', $customerId)
                ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
                ->distinct('recipient_phone')
                ->count('recipient_phone'),
        ];
    }

    /**
     * 2. Thống kê tài chính tổng quan
     */
    private function getFinancialStatistics($customerId, $dateRange)
    {
        $transactions = CodTransaction::where('sender_id', $customerId)
            ->whereHas('order', function($q) use ($dateRange) {
                $q->whereBetween('created_at', [$dateRange['start'], $dateRange['end']]);
            });
        
        $totalRevenue = (clone $transactions)->sum('cod_amount');
        $totalExpenses = (clone $transactions)->sum(DB::raw('shipping_fee + cod_fee'));
        $totalReceived = (clone $transactions)
            ->where('sender_payment_status', 'completed')
            ->sum('sender_receive_amount');
        $totalPending = (clone $transactions)
            ->where('sender_payment_status', 'pending')
            ->sum('sender_receive_amount');
        
        // Phí đã trả
        $feePaid = (clone $transactions)
            ->whereNotNull('sender_fee_paid_at')
            ->sum('sender_fee_paid');
        
        // Phí chờ thanh toán
        $feePending = (clone $transactions)
            ->whereNull('sender_fee_paid_at')
            ->where('sender_fee_paid', '>', 0)
            ->sum('sender_fee_paid');
        
        // Nợ đã trừ
        $debtDeducted = (clone $transactions)->sum('sender_debt_deducted');
        
        // Lợi nhuận ròng
        $netProfit = $totalReceived - $debtDeducted;
        
        // ROI
        $roi = $totalExpenses > 0 ? round((($totalRevenue - $totalExpenses) / $totalExpenses) * 100, 1) : 0;
        
        return [
            'total_revenue' => $totalRevenue,
            'total_expenses' => $totalExpenses,
            'total_received' => $totalReceived,
            'total_pending' => $totalPending,
            'fee_paid' => $feePaid,
            'fee_pending' => $feePending,
            'debt_deducted' => $debtDeducted,
            'net_profit' => $netProfit,
            'roi_percent' => $roi,
            'avg_order_value' => $transactions->count() > 0 
                ? round($totalRevenue / $transactions->count()) 
                : 0,
        ];
    }

    /**
     * 3. Thống kê COD chi tiết
     */
    private function getCodStatistics($customerId, $dateRange)
    {
        $transactions = CodTransaction::where('sender_id', $customerId)
            ->whereHas('order', function($q) use ($dateRange) {
                $q->whereBetween('created_at', [$dateRange['start'], $dateRange['end']]);
            });
        
        $withCod = (clone $transactions)->where('cod_amount', '>', 0)->count();
        $withoutCod = (clone $transactions)->where('cod_amount', 0)->count();
        
        $codCompleted = (clone $transactions)
            ->where('sender_payment_status', 'completed')
            ->count();
        
        $codPending = (clone $transactions)
            ->where('sender_payment_status', 'pending')
            ->count();
        
        return [
            'total_transactions' => $transactions->count(),
            'with_cod' => $withCod,
            'without_cod' => $withoutCod,
            'cod_completed' => $codCompleted,
            'cod_pending' => $codPending,
            'cod_success_rate' => $withCod > 0 
                ? round(($codCompleted / $withCod) * 100, 1) 
                : 0,
            'avg_cod_value' => $withCod > 0 
                ? round($transactions->where('cod_amount', '>', 0)->avg('cod_amount')) 
                : 0,
            'max_cod_value' => (clone $transactions)->max('cod_amount'),
            'total_cod_fee' => (clone $transactions)->sum('cod_fee'),
        ];
    }

    /**
     * 4. Thống kê công nợ
     */
    private function getDebtStatistics($customerId)
    {
        $hubIds = CodTransaction::where('sender_id', $customerId)
            ->distinct()
            ->pluck('hub_id');
        
        $debtByHub = [];
        $totalDebt = 0;
        $totalPaid = 0;
        
        foreach ($hubIds as $hubId) {
            if (!$hubId) continue;
            
            $unpaid = SenderDebt::getTotalUnpaidDebt($customerId, $hubId);
            $paid = SenderDebt::where('sender_id', $customerId)
                ->where('hub_id', $hubId)
                ->where('type', 'payment')
                ->sum('amount');
            
            if ($unpaid > 0 || $paid > 0) {
                $hub = \App\Models\User::find($hubId);
                $debtByHub[] = [
                    'hub_id' => $hubId,
                    'hub_name' => $hub ? $hub->full_name : 'Hub #' . $hubId,
                    'unpaid' => $unpaid,
                    'paid' => $paid,
                ];
                $totalDebt += $unpaid;
                $totalPaid += $paid;
            }
        }
        
        // Tổng nợ đã bị trừ tự động
        $autoDeducted = CodTransaction::where('sender_id', $customerId)
            ->sum('sender_debt_deducted');
        
        return [
            'total_unpaid' => $totalDebt,
            'total_paid' => $totalPaid,
            'auto_deducted' => $autoDeducted,
            'by_hub' => $debtByHub,
            'has_debt' => $totalDebt > 0,
            'debt_ratio' => ($totalDebt + $totalPaid) > 0 
                ? round(($totalDebt / ($totalDebt + $totalPaid)) * 100, 1) 
                : 0,
        ];
    }

    /**
     * 5. Thống kê hoàn hàng
     */
    private function getReturnStatistics($customerId, $dateRange)
    {
        $returns = OrderReturn::whereHas('order', function($q) use ($customerId, $dateRange) {
            $q->where('sender_id', $customerId)
              ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']]);
        });
        
        $total = (clone $returns)->count();
        $pending = (clone $returns)->where('status', OrderReturn::STATUS_PENDING)->count();
        $returning = (clone $returns)->where('status', OrderReturn::STATUS_RETURNING)->count();
        $completed = (clone $returns)->where('status', OrderReturn::STATUS_COMPLETED)->count();
        $cancelled = (clone $returns)->where('status', OrderReturn::STATUS_CANCELLED)->count();
        
        // Lý do hoàn hàng
        $reasonBreakdown = (clone $returns)
            ->selectRaw('reason_type, COUNT(*) as count')
            ->groupBy('reason_type')
            ->pluck('count', 'reason_type')
            ->toArray();
        
        // Tổng phí hoàn
        $totalReturnFee = (clone $returns)->sum('return_fee');
        $avgReturnFee = $total > 0 ? round($totalReturnFee / $total) : 0;
        
        // Return rate
        $totalOrders = Order::where('sender_id', $customerId)
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->count();
        $returnRate = $totalOrders > 0 ? round(($total / $totalOrders) * 100, 1) : 0;
        
        return [
            'total' => $total,
            'by_status' => [
                'pending' => $pending,
                'returning' => $returning,
                'completed' => $completed,
                'cancelled' => $cancelled,
            ],
            'reason_breakdown' => $reasonBreakdown,
            'total_return_fee' => $totalReturnFee,
            'avg_return_fee' => $avgReturnFee,
            'return_rate' => $returnRate,
            'success_rate' => $total > 0 
                ? round(($completed / $total) * 100, 1) 
                : 0,
        ];
    }

    /**
     * 6. Thống kê theo timeline
     */
    private function getTimelineStatistics($customerId, $dateRange)
    {
        $orders = Order::where('sender_id', $customerId)
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        $revenue = CodTransaction::where('sender_id', $customerId)
            ->whereHas('order', function($q) use ($dateRange) {
                $q->whereBetween('created_at', [$dateRange['start'], $dateRange['end']]);
            })
            ->selectRaw('DATE(created_at) as date, SUM(cod_amount) as amount')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        return [
            'orders' => $orders->pluck('count', 'date')->toArray(),
            'revenue' => $revenue->pluck('amount', 'date')->toArray(),
        ];
    }

    /**
     * 7. Top tuyến đường
     */
    private function getTopRoutes($customerId, $dateRange)
    {
        return Order::where('sender_id', $customerId)
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->select([
                DB::raw('CONCAT(province_code, "-", district_code) as route'),
                DB::raw('COUNT(*) as order_count'),
                DB::raw('SUM(shipping_fee) as total_shipping'),
                DB::raw('AVG(shipping_fee) as avg_shipping'),
            ])
            ->groupBy('route')
            ->orderByDesc('order_count')
            ->limit(10)
            ->get()
            ->map(function($item) {
                return [
                    'destination' => $this->getLocationName($item->route),
                    'order_count' => $item->order_count,
                    'total_shipping' => $item->total_shipping,
                    'avg_shipping' => round($item->avg_shipping),
                ];
            });
    }

    /**
     * 8. Tính điểm hiệu suất
     */
    private function calculatePerformanceScore($orderStats, $returnStats)
    {
        $score = 100;
        
        // Trừ điểm nếu success rate thấp
        if ($orderStats['success_rate'] < 80) {
            $score -= (80 - $orderStats['success_rate']) * 0.5;
        }
        
        // Trừ điểm nếu return rate cao
        if ($returnStats['return_rate'] > 10) {
            $score -= ($returnStats['return_rate'] - 10) * 2;
        }
        
        // Trừ điểm nếu cancelled nhiều
        if ($orderStats['total'] > 0) {
            $cancelRate = ($orderStats['by_status']['cancelled'] / $orderStats['total']) * 100;
            if ($cancelRate > 5) {
                $score -= ($cancelRate - 5) * 1.5;
            }
        }
        
        $score = max(0, min(100, $score));
        
        $rating = match(true) {
            $score >= 90 => ['level' => 'excellent', 'label' => 'Xuất sắc', 'color' => 'success'],
            $score >= 70 => ['level' => 'good', 'label' => 'Tốt', 'color' => 'info'],
            $score >= 50 => ['level' => 'average', 'label' => 'Trung bình', 'color' => 'warning'],
            default => ['level' => 'poor', 'label' => 'Cần cải thiện', 'color' => 'danger'],
        };
        
        return array_merge(['score' => round($score, 1)], $rating);
    }

    /**
     * 9. Alerts/Warnings
     */
    private function getAlerts($customerId, $debtStats, $codStats)
    {
        $alerts = [];
        
        // Cảnh báo nợ
        if ($debtStats['has_debt']) {
            $alerts[] = [
                'type' => 'danger',
                'icon' => 'exclamation-triangle',
                'title' => 'Bạn đang có công nợ',
                'message' => 'Tổng nợ: ' . number_format($debtStats['total_unpaid']) . 'đ',
                'action' => route('customer.income.debt'),
                'action_label' => 'Xem chi tiết',
            ];
        }
        
        // Cảnh báo COD chờ thanh toán
        if ($codStats['cod_pending'] > 5) {
            $alerts[] = [
                'type' => 'warning',
                'icon' => 'clock',
                'title' => 'COD chờ nhận',
                'message' => 'Bạn có ' . $codStats['cod_pending'] . ' giao dịch COD chờ nhận tiền',
                'action' => route('customer.cod.index', ['tab' => 'waiting_cod']),
                'action_label' => 'Kiểm tra ngay',
            ];
        }
        
        return $alerts;
    }

    /**
     * ========== UTILITY METHODS ==========
     */

    private function getDateRange($period)
    {
        return match($period) {
            'today' => [
                'start' => now()->startOfDay(),
                'end' => now()->endOfDay(),
            ],
            '7days' => [
                'start' => now()->subDays(7)->startOfDay(),
                'end' => now()->endOfDay(),
            ],
            '30days' => [
                'start' => now()->subDays(30)->startOfDay(),
                'end' => now()->endOfDay(),
            ],
            'this_month' => [
                'start' => now()->startOfMonth(),
                'end' => now()->endOfMonth(),
            ],
            'last_month' => [
                'start' => now()->subMonth()->startOfMonth(),
                'end' => now()->subMonth()->endOfMonth(),
            ],
            default => [
                'start' => now()->subDays(30)->startOfDay(),
                'end' => now()->endOfDay(),
            ],
        };
    }

    private function getLocationName($routeCode)
    {
        // TODO: Map province/district code to name
        return $routeCode;
    }
}