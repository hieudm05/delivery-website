<?php

namespace App\Http\Controllers;

use App\Models\Customer\Dashboard\Orders\CodTransaction;
use App\Models\Customer\Dashboard\Orders\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

/**
 * ✅ CONTROLLER: IncomeController
 * Quản lý thu nhập cho tất cả các role
 */
class IncomeController extends Controller
{
    /**
     * Dashboard thu nhập - Tự động detect role
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Lấy khoảng thời gian từ request
        $startDate = $request->input('start_date', now()->startOfMonth());
        $endDate = $request->input('end_date', now()->endOfMonth());

        // Parse dates
        $startDate = Carbon::parse($startDate);
        $endDate = Carbon::parse($endDate);

        // Lấy báo cáo thu nhập
        $report = $user->getIncomeReport($startDate, $endDate);

        // ✅ DRIVER: Redirect to driver dashboard index (đã có view)
        if ($user->isDriver()) {
            return view('driver.income.index', compact('report', 'startDate', 'endDate'));
        }

        // ✅ CUSTOMER: Redirect to customer dashboard index (đã có view)
        if ($user->isCustomer()) {
            return view('customer.dashboard.income.index', compact('report', 'startDate', 'endDate'));
        }

        // ✅ HUB: Redirect to hub cashflow (đã có view)
        if ($user->isHub()) {
            // Hub cần thêm pending data
            $pendingFromDriver = $user->hubTransactions()
                ->where('shipper_payment_status', 'transferred')
                ->with(['driver', 'order'])
                ->get();

            $pendingToSender = $user->hubTransactions()
                ->where('sender_payment_status', 'pending')
                ->with(['sender', 'order'])
                ->get();

            $pendingCommission = $user->hubTransactions()
                ->where('driver_commission_status', 'pending')
                ->where('shipper_payment_status', 'confirmed')
                ->with(['driver', 'order'])
                ->get();

            return view('hub.income.cashflow', compact(
                'report',
                'startDate',
                'endDate',
                'pendingFromDriver',
                'pendingToSender',
                'pendingCommission'
            ));
        }
    }

    /**
     * API: Lấy dữ liệu thu nhập (JSON)
     */
    public function getIncomeData(Request $request)
    {
        $user = Auth::user();

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        if ($startDate)
            $startDate = Carbon::parse($startDate);
        if ($endDate)
            $endDate = Carbon::parse($endDate);

        $report = $user->getIncomeReport($startDate, $endDate);

        return response()->json([
            'success' => true,
            'data' => $report,
        ]);
    }

    /**
     * ==========================================
     * DRIVER SPECIFIC METHODS
     * ==========================================
     */

    /**
     * Chi tiết commission của driver
     */
    public function driverCommissionDetail(Request $request)
    {
        $user = Auth::user();

        if (!$user->isDriver()) {
            abort(403);
        }

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        if ($startDate)
            $startDate = Carbon::parse($startDate);
        if ($endDate)
            $endDate = Carbon::parse($endDate);

        // Lấy danh sách transactions
        $transactions = $user->driverTransactions()
            ->with(['order', 'hub'])
            ->when($startDate, fn($q) => $q->whereDate('created_at', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('created_at', '<=', $endDate))
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $report = $user->getDriverIncomeReport($startDate, $endDate);

        return view('driver.income.commission', compact('transactions', 'report', 'startDate', 'endDate'));
    }

    /**
     * Lịch sử nộp tiền của driver
     */
    public function driverPaymentHistory(Request $request)
    {
        $user = Auth::user();

        if (!$user->isDriver()) {
            abort(403);
        }

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $payments = $user->driverTransactions()
            ->whereIn('shipper_payment_status', ['transferred', 'confirmed'])
            ->with(['order', 'hub', 'shipperBankAccount'])
            ->when($startDate, fn($q) => $q->whereDate('shipper_transfer_time', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('shipper_transfer_time', '<=', $endDate))
            ->orderBy('shipper_transfer_time', 'desc')
            ->paginate(20);

        return view('driver.income.payment-history', compact('payments', 'startDate', 'endDate'));
    }

    /**
     * ==========================================
     * CUSTOMER SPECIFIC METHODS
     * ==========================================
     */

    /**
     * Chi tiết COD của customer
     */
    public function customerCodDetail(Request $request)
    {
        $user = Auth::user();

        if (!$user->isCustomer()) {
            abort(403);
        }

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $status = $request->input('status'); // pending, completed

        $transactions = $user->senderTransactions()
            ->with(['order', 'hub'])
            ->when($status, fn($q) => $q->where('sender_payment_status', $status))
            ->when($startDate, fn($q) => $q->whereDate('created_at', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('created_at', '<=', $endDate))
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $report = $user->getCustomerIncomeReport($startDate, $endDate);

        return view('customer.income.cod-detail', compact('transactions', 'report', 'startDate', 'endDate', 'status'));
    }

    /**
     * Lịch sử nợ của customer
     */
    public function customerDebtHistory(Request $request)
    {
        $user = Auth::user();

        if (!$user->isCustomer()) {
            abort(403);
        }

        $hubId = $request->input('hub_id');

        $debts = $user->senderDebts()
            ->with(['hub', 'order'])
            ->when($hubId, fn($q) => $q->where('hub_id', $hubId))
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $totalDebt = $user->getCustomerTotalDebt($hubId);

        return view('customer.income.debt-history', compact('debts', 'totalDebt', 'hubId'));
    }

    /**
     * ==========================================
     * HUB SPECIFIC METHODS
     * ==========================================
     */

    /**
     * Dashboard cashflow của hub
     * ⚠️ NOTE: Method này giờ redirect sang index() vì đã merge logic
     * Giữ lại để tương thích với routes cũ
     */
    public function hubCashflow(Request $request)
    {
        // Redirect về index, sẽ tự động detect hub và render đúng view
        return $this->index($request);
    }

    /**
     * Chi tiết giao dịch hub
     */
    public function hubTransactionDetail(Request $request)
    {
        $user = Auth::user();

        if (!$user->isHub()) {
            abort(403);
        }

        $type = $request->input('type'); // received, paid_sender, paid_driver, paid_system
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $query = $user->hubTransactions()->with(['order', 'driver', 'sender']);

        // Filter theo type
        switch ($type) {
            case 'received':
                $query->whereIn('shipper_payment_status', ['transferred', 'confirmed']);
                break;
            case 'paid_sender':
                $query->where('sender_payment_status', 'completed');
                break;
            case 'paid_driver':
                $query->where('driver_commission_status', 'paid');
                break;
            case 'paid_system':
                $query->whereIn('hub_system_status', ['transferred', 'confirmed']);
                break;
        }

        if ($startDate)
            $query->whereDate('created_at', '>=', Carbon::parse($startDate));
        if ($endDate)
            $query->whereDate('created_at', '<=', Carbon::parse($endDate));

        $transactions = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('hub.income.transaction-detail', compact('transactions', 'type', 'startDate', 'endDate'));
    }

    /**
     * ==========================================
     * ADMIN SPECIFIC METHODS
     * ==========================================
     */

    /**
     * Tổng quan thu nhập toàn hệ thống
     * ⚠️ NOTE: Method này giờ redirect sang index() vì đã merge logic
     * Giữ lại để tương thích với routes cũ
     */
    public function adminSystemOverview(Request $request)
    {
        // Redirect về index, sẽ tự động detect admin và render đúng view
        return $this->index($request);
    }

    /**
     * Chi tiết platform fee
     */
    public function adminPlatformFeeDetail(Request $request)
    {
        $user = Auth::user();

        if (!$user->isAdmin()) {
            abort(403);
        }

        $status = $request->input('status'); // pending, transferred, confirmed
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $query = CodTransaction::with(['order', 'hub']);

        if ($status) {
            $query->where('hub_system_status', $status);
        }

        if ($startDate)
            $query->whereDate('created_at', '>=', Carbon::parse($startDate));
        if ($endDate)
            $query->whereDate('created_at', '<=', Carbon::parse($endDate));

        $transactions = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.income.platform-fee', compact('transactions', 'status', 'startDate', 'endDate'));
    }

    /**
     * ==========================================
     * EXPORT METHODS
     * ==========================================
     */

    /**
     * Export báo cáo thu nhập ra Excel
     */
    public function exportIncome(Request $request)
    {
        $user = Auth::user();

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        if ($startDate)
            $startDate = Carbon::parse($startDate);
        if ($endDate)
            $endDate = Carbon::parse($endDate);

        $report = $user->getIncomeReport($startDate, $endDate);

        // TODO: Implement Excel export
        // return Excel::download(new IncomeExport($report), 'income-report.xlsx');

        return back()->with('success', 'Chức năng export đang được phát triển');
    }
    /**
     * Chi tiết thu nhập của một hub cụ thể (Admin only)
     */
    public function hubDetail(Request $request, $hubId)
    {
        $user = Auth::user();

        if (!$user->isAdmin()) {
            abort(403, 'Bạn không có quyền truy cập');
        }

        // Lấy thông tin hub
        $hub = \App\Models\User::where('id', $hubId)
            ->where('role', 'hub')
            ->firstOrFail();

        // Parse dates
        $startDate = $request->input('start_date')
            ? Carbon::parse($request->input('start_date'))
            : now()->startOfMonth();

        $endDate = $request->input('end_date')
            ? Carbon::parse($request->input('end_date'))
            : now()->endOfMonth();

        // Lấy báo cáo chi tiết của hub
        $report = $hub->getHubIncomeReport($startDate, $endDate);

         if (!isset($report['statistics'])) {
        $report['statistics'] = [];
    }
// Đếm số đơn theo trạng thái
    $orderStats = Order::whereIn('id', function($query) use ($hubId, $startDate, $endDate) {
        $query->select('order_id')
            ->from('cod_transactions')
            ->where('hub_id', $hubId)
            ->whereBetween('created_at', [$startDate, $endDate]);
    })
    ->selectRaw('
        COUNT(*) as total_orders,
        SUM(CASE WHEN status = "delivered" THEN 1 ELSE 0 END) as delivered_orders,
        SUM(CASE WHEN status = "failed" THEN 1 ELSE 0 END) as failed_orders,
        SUM(CASE WHEN status IN ("in_transit", "picked_up") THEN 1 ELSE 0 END) as in_transit_orders,
        SUM(CASE WHEN status IN ("pending", "pickup_scheduled") THEN 1 ELSE 0 END) as pending_orders
    ')
    ->first();
    $report['statistics']['total_orders'] = $orderStats->total_orders ?? 0;
    $report['statistics']['delivered_orders'] = $orderStats->delivered_orders ?? 0;
    $report['statistics']['failed_orders'] = $orderStats->failed_orders ?? 0;
    $report['statistics']['in_transit_orders'] = $orderStats->in_transit_orders ?? 0;
    $report['statistics']['pending_orders'] = $orderStats->pending_orders ?? 0;
    $report['statistics']['other_orders'] = $orderStats->total_orders 
        - $orderStats->delivered_orders 
        - $orderStats->failed_orders 
        - $orderStats->in_transit_orders 
        - $orderStats->pending_orders;

    // Tính toán các chỉ số bổ sung
    $report['total_revenue'] = $report['net_income'] * 2.5;
        $report['hub_profit'] = $report['net_income'];
        $report['platform_fee'] = $report['net_income'] * 0.67; // 40% of revenue = 67% of hub profit

        // Platform Fee breakdown
        $platformFeeData = CodTransaction::where('hub_id', $hubId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('
            SUM(hub_system_amount
                                ) as total_platform_fee,
            SUM(CASE WHEN hub_system_status = "pending" THEN hub_system_amount
                                 ELSE 0 END) as pending,
            SUM(CASE WHEN hub_system_status = "transferred" THEN hub_system_amount
                                 ELSE 0 END) as transferred,
            SUM(CASE WHEN hub_system_status = "confirmed" THEN hub_system_amount
                                 ELSE 0 END) as received
        ')
            ->first();

        $report['platform_fee_pending'] = $platformFeeData->pending ?? 0;
        $report['platform_fee_received'] = $platformFeeData->received ?? 0;

        // Payment status breakdown
        $paymentStatus = CodTransaction::where('hub_id', $hubId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('
            SUM(CASE WHEN shipper_payment_status IN ("transferred", "confirmed") THEN cod_amount ELSE 0 END) as cod_collected,
            SUM(CASE WHEN shipper_payment_status = "pending" THEN cod_amount ELSE 0 END) as cod_pending,
            SUM(CASE WHEN sender_payment_status = "completed" THEN cod_amount ELSE 0 END) as sender_paid,
            SUM(CASE WHEN sender_payment_status IN ("pending", "processing") THEN cod_amount ELSE 0 END) as sender_pending,
            SUM(CASE WHEN driver_commission_status = "paid" THEN driver_commission ELSE 0 END) as driver_commission_paid,
            SUM(CASE WHEN driver_commission_status = "pending" THEN driver_commission ELSE 0 END) as driver_commission_pending
        ')
            ->first();

        $report['payment_status'] = [
            'cod_collected' => $paymentStatus->cod_collected ?? 0,
            'cod_pending' => $paymentStatus->cod_pending ?? 0,
            'sender_paid' => $paymentStatus->sender_paid ?? 0,
            'sender_pending' => $paymentStatus->sender_pending ?? 0,
            'driver_commission_paid' => $paymentStatus->driver_commission_paid ?? 0,
            'driver_commission_pending' => $paymentStatus->driver_commission_pending ?? 0,
        ];

        // Top drivers của hub này
       $topDrivers = CodTransaction::where('cod_transactions.hub_id', $hubId)
        ->whereBetween('cod_transactions.created_at', [$startDate, $endDate])
        ->join('users', 'cod_transactions.driver_id', '=', 'users.id')
        ->selectRaw('
            users.id,
            users.full_name as name,
            COUNT(cod_transactions.id) as total_orders,
            SUM(CASE WHEN cod_transactions.order_id IN (
                SELECT id FROM orders WHERE status = "delivered"
            ) THEN 1 ELSE 0 END) as delivered_orders
        ')
        ->groupBy('users.id', 'users.full_name')
        ->orderByDesc('total_orders')
        ->limit(10)
        ->get()
        ->toArray();


        // Recent transactions
        $recentTransactions = CodTransaction::where('hub_id', $hubId)
            ->with(['order', 'sender', 'driver'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Chart data - Revenue over time
       $revenueByDate = CodTransaction::where('hub_id', $hubId)
        ->whereBetween('created_at', [$startDate, $endDate])
        ->selectRaw('
            DATE(created_at) as date,
            SUM(cod_amount) as revenue,
            SUM(hub_system_amount) as platform_fee,
            SUM(cod_amount - hub_system_amount - driver_commission) as hub_profit
        ')
        ->groupBy('date')
        ->orderBy('date')
        ->get();


        $chartData = [
            'revenue' => [
                'dates' => $revenueByDate->pluck('date')->map(fn($d) => Carbon::parse($d)->format('d/m'))->toArray(),
                'revenue' => $revenueByDate->pluck('revenue')->toArray(),
                'platform_fee' => $revenueByDate->pluck('platform_fee')->toArray(),
                'hub_profit' => $revenueByDate->pluck('hub_profit')->toArray(),
            ],
            'order_status' => [
                'labels' => ['Đã giao', 'Thất bại', 'Đang giao', 'Chờ lấy', 'Khác'],
                'values' => [
                    $report['statistics']['delivered_orders'] ?? 0,
                    $report['statistics']['failed_orders'] ?? 0,
                    $report['statistics']['in_transit_orders'] ?? 0,
                    $report['statistics']['pending_orders'] ?? 0,
                    $report['statistics']['other_orders'] ?? 0,
                ]
            ]
        ];

        return view('admin.income.hub-detail', compact(
            'hub',
            'report',
            'startDate',
            'endDate',
            'topDrivers',
            'recentTransactions',
            'chartData'
        ));
    }
}