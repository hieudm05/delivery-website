<?php

namespace App\Http\Controllers;

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
        
        // ✅ ADMIN: Redirect to admin system overview (đã có view)
        if ($user->isAdmin()) {
            // Admin cần thống kê hub
            $hubStats = \App\Models\User::where('role', 'hub')
                ->where('status', 'active')
                ->get()
                ->map(function($hub) use ($startDate, $endDate) {
                    $hubReport = $hub->getHubIncomeReport($startDate, $endDate);
                    return [
                        'hub_id' => $hub->id,
                        'hub_name' => $hub->full_name,
                        'profit' => $hubReport['net_income'],
                        'orders' => $hubReport['statistics']['total_orders'],
                    ];
                })
                ->sortByDesc('profit');
            
            return view('admin.income.system-overview', compact('report', 'hubStats', 'startDate', 'endDate'));
        }
        
        // Default: không có quyền
        abort(403, 'Bạn không có quyền truy cập trang này');
    }
    
    /**
     * API: Lấy dữ liệu thu nhập (JSON)
     */
    public function getIncomeData(Request $request)
    {
        $user = Auth::user();
        
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        
        if ($startDate) $startDate = Carbon::parse($startDate);
        if ($endDate) $endDate = Carbon::parse($endDate);
        
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
        
        if ($startDate) $startDate = Carbon::parse($startDate);
        if ($endDate) $endDate = Carbon::parse($endDate);
        
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
        
        if ($startDate) $query->whereDate('created_at', '>=', Carbon::parse($startDate));
        if ($endDate) $query->whereDate('created_at', '<=', Carbon::parse($endDate));
        
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
        
        $query = \App\Models\Customer\Dashboard\Orders\CodTransaction::with(['order', 'hub']);
        
        if ($status) {
            $query->where('hub_system_status', $status);
        }
        
        if ($startDate) $query->whereDate('created_at', '>=', Carbon::parse($startDate));
        if ($endDate) $query->whereDate('created_at', '<=', Carbon::parse($endDate));
        
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
        
        if ($startDate) $startDate = Carbon::parse($startDate);
        if ($endDate) $endDate = Carbon::parse($endDate);
        
        $report = $user->getIncomeReport($startDate, $endDate);
        
        // TODO: Implement Excel export
        // return Excel::download(new IncomeExport($report), 'income-report.xlsx');
        
        return back()->with('success', 'Chức năng export đang được phát triển');
    }
}