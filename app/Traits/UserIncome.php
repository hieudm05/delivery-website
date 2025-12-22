<?php

namespace App\Traits;

use App\Models\Customer\Dashboard\Orders\CodTransaction;
use App\Models\Customer\Dashboard\Orders\Order;
use App\Models\Driver\Orders\OrderReturn;
use App\Models\Hub\Hub;
use App\Models\SenderDebt;
use Illuminate\Support\Facades\DB;

/**
 * ✅ TRAIT: UserIncome
 * Quản lý thu nhập và dòng tiền cho tất cả các role
 */
trait UserIncome
{
    /**
     * ==========================================
     * DRIVER INCOME - Thu nhập tài xế
     * ==========================================
     */
    
    /**
     * Tổng commission chưa nhận (pending)
     */
    public function getDriverPendingCommission($startDate = null, $endDate = null)
    {
        $query = CodTransaction::where('driver_id', $this->id)
            ->where('driver_commission_status', 'pending');
        
        if ($startDate) $query->whereDate('created_at', '>=', $startDate);
        if ($endDate) $query->whereDate('created_at', '<=', $endDate);
        
        return $query->sum('driver_commission');
    }
    
    /**
     * Tổng commission đã nhận (paid)
     */
    public function getDriverPaidCommission($startDate = null, $endDate = null)
    {
        $query = CodTransaction::where('driver_id', $this->id)
            ->where('driver_commission_status', 'paid');
        
        if ($startDate) $query->whereDate('driver_paid_at', '>=', $startDate);
        if ($endDate) $query->whereDate('driver_paid_at', '<=', $endDate);
        
        return $query->sum('driver_commission');
    }
    
    /**
     * Tổng tiền driver phải nộp cho hub (chưa nộp)
     */
    public function getDriverMustPayToHub($startDate = null, $endDate = null)
    {
        $query = CodTransaction::where('driver_id', $this->id)
            ->where('shipper_payment_status', 'pending')
            ->whereHas('order', function($q) {
                $q->where('status', Order::STATUS_DELIVERED);
            });
        
        if ($startDate) $query->whereDate('created_at', '>=', $startDate);
        if ($endDate) $query->whereDate('created_at', '<=', $endDate);
        
        return $query->sum('total_collected');
    }
    
    /**
     * Tổng tiền driver đã nộp cho hub
     */
    public function getDriverPaidToHub($startDate = null, $endDate = null)
    {
        $query = CodTransaction::where('driver_id', $this->id)
            ->whereIn('shipper_payment_status', ['transferred', 'confirmed']);
        
        if ($startDate) $query->whereDate('shipper_transfer_time', '>=', $startDate);
        if ($endDate) $query->whereDate('shipper_transfer_time', '<=', $endDate);
        
        return $query->sum('total_collected');
    }
    
    /**
     * Báo cáo thu nhập driver chi tiết
     */
    public function getDriverIncomeReport($startDate = null, $endDate = null)
    {
        $pendingCommission = $this->getDriverPendingCommission($startDate, $endDate);
        $paidCommission = $this->getDriverPaidCommission($startDate, $endDate);
        $mustPayToHub = $this->getDriverMustPayToHub($startDate, $endDate);
        $paidToHub = $this->getDriverPaidToHub($startDate, $endDate);
        
        // Số đơn đã giao
        $deliveredOrders = Order::where('driver_id', $this->id)
            ->where('status', Order::STATUS_DELIVERED)
            ->when($startDate, fn($q) => $q->whereDate('updated_at', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('updated_at', '<=', $endDate))
            ->count();
        
        // Số đơn hoàn thành
        $completedReturns = OrderReturn::where('return_driver_id', $this->id)
            ->where('status', OrderReturn::STATUS_COMPLETED)
            ->when($startDate, fn($q) => $q->whereDate('completed_at', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('completed_at', '<=', $endDate))
            ->count();
        
        return [
            'role' => 'driver',
            'income' => [
                'pending_commission' => $pendingCommission,
                'paid_commission' => $paidCommission,
                'total_commission' => $pendingCommission + $paidCommission,
            ],
            'payment' => [
                'must_pay_to_hub' => $mustPayToHub,
                'paid_to_hub' => $paidToHub,
                'remaining_to_pay' => $mustPayToHub,
            ],
            'net_income' => $paidCommission, // Thực nhận
            'statistics' => [
                'delivered_orders' => $deliveredOrders,
                'completed_returns' => $completedReturns,
                'total_orders' => $deliveredOrders + $completedReturns,
                'avg_commission_per_order' => $deliveredOrders > 0 
                    ? ($pendingCommission + $paidCommission) / $deliveredOrders 
                    : 0,
            ],
        ];
    }
    
    /**
     * ==========================================
     * CUSTOMER INCOME - Thu chi người gửi
     * ==========================================
     */
    
    /**
     * Tổng COD đã nhận
     */
    public function getCustomerReceivedCod($startDate = null, $endDate = null)
    {
        $query = CodTransaction::where('sender_id', $this->id)
            ->where('sender_payment_status', 'completed');
        
        if ($startDate) $query->whereDate('sender_transfer_time', '>=', $startDate);
        if ($endDate) $query->whereDate('sender_transfer_time', '<=', $endDate);
        
        return $query->sum('sender_receive_amount');
    }
    
    /**
     * Tổng COD chờ nhận
     */
    public function getCustomerPendingCod($startDate = null, $endDate = null)
    {
        $query = CodTransaction::where('sender_id', $this->id)
            ->where('sender_payment_status', 'pending');
        
        if ($startDate) $query->whereDate('created_at', '>=', $startDate);
        if ($endDate) $query->whereDate('created_at', '<=', $endDate);
        
        return $query->sum('sender_receive_amount');
    }
    
    /**
     * Tổng nợ hiện tại (tất cả hub)
     */
    public function getCustomerTotalDebt($hubId = null)
    {
        $query = SenderDebt::where('sender_id', $this->id)
            ->where('type', 'debt')
            ->where('status', 'unpaid');
        
        if ($hubId) $query->where('hub_id', $hubId);
        
        return $query->sum('amount');
    }
    
    /**
     * Tổng phí đã trả (shipping + platform + cod fee)
     */
    public function getCustomerPaidFees($startDate = null, $endDate = null)
    {
        $query = CodTransaction::where('sender_id', $this->id);
        
        if ($startDate) $query->whereDate('created_at', '>=', $startDate);
        if ($endDate) $query->whereDate('created_at', '<=', $endDate);
        
        return $query->sum(DB::raw('sender_fee_paid'));
    }
    
    /**
     * Tổng nợ đã trả
     */
    public function getCustomerPaidDebt($startDate = null, $endDate = null)
    {
        $query = CodTransaction::where('sender_id', $this->id)
            ->where('sender_debt_deducted', '>', 0);
        
        if ($startDate) $query->whereDate('created_at', '>=', $startDate);
        if ($endDate) $query->whereDate('created_at', '<=', $endDate);
        
        return $query->sum('sender_debt_deducted');
    }
    
    /**
     * Báo cáo thu chi customer
     */
    public function getCustomerIncomeReport($startDate = null, $endDate = null)
    {
        $receivedCod = $this->getCustomerReceivedCod($startDate, $endDate);
        $pendingCod = $this->getCustomerPendingCod($startDate, $endDate);
        $totalDebt = $this->getCustomerTotalDebt();
        $paidFees = $this->getCustomerPaidFees($startDate, $endDate);
        $paidDebt = $this->getCustomerPaidDebt($startDate, $endDate);
        
        // Số đơn đã tạo
        $totalOrders = Order::where('sender_id', $this->id)
            ->when($startDate, fn($q) => $q->whereDate('created_at', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('created_at', '<=', $endDate))
            ->count();
        
        // Số đơn giao thành công
        $deliveredOrders = Order::where('sender_id', $this->id)
            ->where('status', Order::STATUS_DELIVERED)
            ->when($startDate, fn($q) => $q->whereDate('updated_at', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('updated_at', '<=', $endDate))
            ->count();
        
        return [
            'role' => 'customer',
            'income' => [
                'received_cod' => $receivedCod,
                'pending_cod' => $pendingCod,
                'total_cod' => $receivedCod + $pendingCod,
            ],
            'expenses' => [
                'paid_fees' => $paidFees,
                'paid_debt' => $paidDebt,
                'total_expenses' => $paidFees + $paidDebt,
            ],
            'debt' => [
                'current_debt' => $totalDebt,
                'paid_debt' => $paidDebt,
            ],
            'net_income' => $receivedCod - $paidFees - $paidDebt,
            'statistics' => [
                'total_orders' => $totalOrders,
                'delivered_orders' => $deliveredOrders,
                'delivery_rate' => $totalOrders > 0 
                    ? round(($deliveredOrders / $totalOrders) * 100, 2) 
                    : 0,
                'avg_cod_per_order' => $deliveredOrders > 0 
                    ? ($receivedCod + $pendingCod) / $deliveredOrders 
                    : 0,
            ],
        ];
    }
    
    /**
     * ==========================================
     * HUB INCOME - Thu nhập bưu cục
     * ==========================================
     */
    
    /**
     * Tổng tiền Hub nhận từ Driver (chờ xác nhận + đã xác nhận)
     */
    public function getHubReceivedFromDriver($startDate = null, $endDate = null, $status = null)
    {
        $query = CodTransaction::where('hub_id', $this->id);
        
        if ($status) {
            $query->where('shipper_payment_status', $status);
        } else {
            $query->whereIn('shipper_payment_status', ['transferred', 'confirmed']);
        }
        
        if ($startDate) $query->whereDate('hub_confirm_time', '>=', $startDate);
        if ($endDate) $query->whereDate('hub_confirm_time', '<=', $endDate);
        
        return $query->sum('total_collected');
    }
    
    /**
     * Tổng Hub profit
     */
    public function getHubProfit($startDate = null, $endDate = null)
    {
        $query = CodTransaction::where('hub_id', $this->id)
            ->where('shipper_payment_status', 'confirmed');
        
        if ($startDate) $query->whereDate('hub_confirm_time', '>=', $startDate);
        if ($endDate) $query->whereDate('hub_confirm_time', '<=', $endDate);
        
        return $query->sum('hub_profit');
    }
    
    /**
     * Tổng tiền Hub phải trả Sender
     */
    public function getHubMustPaySender($startDate = null, $endDate = null)
    {
        $query = CodTransaction::where('hub_id', $this->id)
            ->where('sender_payment_status', 'pending');
        
        if ($startDate) $query->whereDate('created_at', '>=', $startDate);
        if ($endDate) $query->whereDate('created_at', '<=', $endDate);
        
        return $query->sum('sender_receive_amount');
    }
    
    /**
     * Tổng tiền Hub đã trả Sender
     */
    public function getHubPaidToSender($startDate = null, $endDate = null)
    {
        $query = CodTransaction::where('hub_id', $this->id)
            ->where('sender_payment_status', 'completed');
        
        if ($startDate) $query->whereDate('sender_transfer_time', '>=', $startDate);
        if ($endDate) $query->whereDate('sender_transfer_time', '<=', $endDate);
        
        return $query->sum('sender_receive_amount');
    }
    
    /**
     * Tổng commission Hub phải trả Driver
     */
    public function getHubMustPayDriver($startDate = null, $endDate = null)
    {
        $query = CodTransaction::where('hub_id', $this->id)
            ->where('driver_commission_status', 'pending')
            ->where('shipper_payment_status', 'confirmed');
        
        if ($startDate) $query->whereDate('created_at', '>=', $startDate);
        if ($endDate) $query->whereDate('created_at', '<=', $endDate);
        
        return $query->sum('driver_commission');
    }
    
    /**
     * Tổng commission Hub đã trả Driver
     */
    public function getHubPaidToDriver($startDate = null, $endDate = null)
    {
        $query = CodTransaction::where('hub_id', $this->id)
            ->where('driver_commission_status', 'paid');
        
        if ($startDate) $query->whereDate('driver_paid_at', '>=', $startDate);
        if ($endDate) $query->whereDate('driver_paid_at', '<=', $endDate);
        
        return $query->sum('driver_commission');
    }
    
    /**
     * Tổng tiền Hub phải nộp Admin
     */
    public function getHubMustPaySystem($startDate = null, $endDate = null)
    {
        $query = CodTransaction::where('hub_id', $this->id)
            ->where('hub_system_status', 'pending');
        
        if ($startDate) $query->whereDate('created_at', '>=', $startDate);
        if ($endDate) $query->whereDate('created_at', '<=', $endDate);
        
        return $query->sum('hub_system_amount');
    }
    
    /**
     * Tổng tiền Hub đã nộp Admin
     */
    public function getHubPaidToSystem($startDate = null, $endDate = null)
    {
        $query = CodTransaction::where('hub_id', $this->id)
            ->whereIn('hub_system_status', ['transferred', 'confirmed']);
        
        if ($startDate) $query->whereDate('hub_system_transfer_time', '>=', $startDate);
        if ($endDate) $query->whereDate('hub_system_transfer_time', '<=', $endDate);
        
        return $query->sum('hub_system_amount');
    }
    
    /**
     * Báo cáo thu nhập Hub
     */
    /**
 * ✅ THÊM VÀO User Model hoặc Trait HasIncomeReport
 * Tính thu nhập Hub (bao gồm cả tiền trả nợ)
 */
    /**
 * ✅ FIX: Báo cáo thu nhập Hub (LOGIC ĐÚNG)
 * THAY THẾ method getHubIncomeReport() hiện tại trong UserIncome trait
 */
public function getHubIncomeReport($startDate = null, $endDate = null)
{
    if (!$this->isHub()) {
        return null;
    }

    $startDate = $startDate ?? now()->startOfMonth();
    $endDate = $endDate ?? now()->endOfMonth();

    // ========== 1. THU NHẬP TỪ COD TRANSACTIONS ==========
    $transactions = CodTransaction::where('hub_id', $this->id)
        ->whereBetween('created_at', [$startDate, $endDate])
        ->get();

    $grossIncome = $transactions->where('shipper_payment_status', 'confirmed')->sum('total_collected');
    $receivedFromDriver = $transactions->where('shipper_payment_status', 'confirmed')->sum('total_collected');

    // Chi phí
    $paidToSender = $transactions->where('sender_payment_status', 'completed')->sum('sender_receive_amount');
    $mustPaySender = $transactions->where('sender_payment_status', 'pending')->sum('sender_receive_amount');

    $paidToDriver = $transactions->where('driver_commission_status', 'paid')->sum('driver_commission');
    $mustPayDriver = $transactions->where('driver_commission_status', 'pending')->sum('driver_commission');

    $paidToSystem = $transactions->where('hub_system_status', 'confirmed')->sum('hub_system_amount');
    $mustPaySystem = $transactions->where('hub_system_status', 'pending')->sum('hub_system_amount');

    $totalExpenses = $paidToSender + $paidToDriver + $paidToSystem;

    // Lợi nhuận từ COD (đã bao gồm cả tiền trừ nợ tự động)
    $hubProfitFromCod = $transactions->sum('hub_profit');

    // ========== 2. ✅ THU NHẬP TỪ TRẢ NỢ THẬT SỰ ==========
    // QUAN TRỌNG: Đây là tiền customer CHUYỂN KHOẢN trực tiếp để trả nợ
    // KHÔNG PHẢI tiền trừ tự động từ COD đơn mới
    
    $debtPaymentsReceived = CodTransaction::where('hub_id', $this->id)
        ->whereBetween('sender_debt_confirmed_at', [$startDate, $endDate]) // ✅ Dùng thời gian XÁC NHẬN
        ->where('sender_debt_payment_status', 'completed') // ✅ Đã xác nhận
        ->whereNotNull('sender_debt_payment_method') // ✅ Có phương thức thanh toán (bank_transfer/cash)
        ->sum('sender_debt_deducted'); // Số tiền đã trả

    // Tiền trả nợ đang chờ xác nhận
    $debtPaymentsPending = CodTransaction::where('hub_id', $this->id)
        ->where('sender_debt_payment_status', 'pending') // Chờ xác nhận
        ->whereNotNull('sender_debt_payment_method')
        ->sum('sender_debt_deducted');

    // ========== 3. ✅ TỔNG HỢP LỢI NHUẬN ==========
    // Lợi nhuận thực tế = Lợi nhuận từ COD + Tiền trả nợ đã xác nhận
    $netIncome = $hubProfitFromCod + $debtPaymentsReceived;

    // ========== 4. PENDING PAYMENTS ==========
    $pendingFromDriver = $transactions->where('shipper_payment_status', 'transferred')->count();
    $pendingToSender = $transactions->where('sender_payment_status', 'pending')->count();
    $pendingCommission = $transactions->where('driver_commission_status', 'pending')
        ->where('shipper_payment_status', 'confirmed')->count();

    // ========== 5. STATISTICS ==========
    $totalOrders = $transactions->count();
    $avgProfitPerOrder = $totalOrders > 0 ? $netIncome / $totalOrders : 0;

    return [
        'period' => [
            'start' => $startDate->format('Y-m-d'),
            'end' => $endDate->format('Y-m-d'),
        ],
        
        'income' => [
            'gross_income' => $grossIncome,
            'received_from_driver' => $receivedFromDriver,
        ],
        
        'expenses' => [
            'paid_to_sender' => $paidToSender,
            'must_pay_sender' => $mustPaySender,
            'paid_to_driver' => $paidToDriver,
            'must_pay_driver' => $mustPayDriver,
            'paid_to_system' => $paidToSystem,
            'must_pay_system' => $mustPaySystem,
            'total_expenses' => $totalExpenses,
        ],
        
        // ✅ LỢI NHUẬN CHI TIẾT
        'net_income' => $netIncome, // Tổng lợi nhuận
        'hub_profit_from_cod' => $hubProfitFromCod, // Từ COD đơn hàng
        'debt_payments_received' => $debtPaymentsReceived, // Tiền trả nợ đã xác nhận
        'debt_payments_pending' => $debtPaymentsPending, // Tiền trả nợ chờ xác nhận
        
        'pending_payments' => [
            'from_driver' => $pendingFromDriver,
            'to_sender' => $pendingToSender,
            'commission' => $pendingCommission,
            'debt_confirmation' => CodTransaction::where('hub_id', $this->id)
                ->where('sender_debt_payment_status', 'pending')
                ->whereNotNull('sender_debt_payment_method')
                ->count(), // ✅ Số đơn trả nợ chờ xác nhận
            'total_pending' => $mustPaySender + $mustPayDriver + $mustPaySystem,
        ],
        
        'statistics' => [
            'total_orders' => $totalOrders,
            'avg_profit_per_order' => round($avgProfitPerOrder),
            'total_debt_paid_orders' => CodTransaction::where('hub_id', $this->id)
                ->whereBetween('sender_debt_confirmed_at', [$startDate, $endDate])
                ->where('sender_debt_payment_status', 'completed')
                ->count(), // ✅ Số đơn đã trả nợ
        ],
    ];
}
    
    /**
     * ==========================================
     * ADMIN INCOME - Thu nhập hệ thống
     * ==========================================
     */
    
    /**
     * Tổng admin profit chờ nhận
     */
    public function getAdminPendingProfit($startDate = null, $endDate = null)
    {
        $query = CodTransaction::where('hub_system_status', 'pending');
        
        if ($startDate) $query->whereDate('created_at', '>=', $startDate);
        if ($endDate) $query->whereDate('created_at', '<=', $endDate);
        
        return $query->sum('admin_profit');
    }
    
    /**
     * Tổng admin profit đã nhận
     */
    public function getAdminReceivedProfit($startDate = null, $endDate = null)
    {
        $query = CodTransaction::whereIn('hub_system_status', ['transferred', 'confirmed']);
        
        if ($startDate) $query->whereDate('system_confirm_time', '>=', $startDate);
        if ($endDate) $query->whereDate('system_confirm_time', '<=', $endDate);
        
        return $query->sum('admin_profit');
    }
    
    /**
     * Báo cáo thu nhập Admin
     */
    public function getAdminIncomeReport($startDate = null, $endDate = null)
    {
        $pendingProfit = $this->getAdminPendingProfit($startDate, $endDate);
        $receivedProfit = $this->getAdminReceivedProfit($startDate, $endDate);
        
        // Thống kê tổng đơn hàng trong hệ thống
        $totalOrders = Order::when($startDate, fn($q) => $q->whereDate('created_at', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('created_at', '<=', $endDate))
            ->count();
        
        $deliveredOrders = Order::where('status', Order::STATUS_DELIVERED)
            ->when($startDate, fn($q) => $q->whereDate('updated_at', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('updated_at', '<=', $endDate))
            ->count();
        
        return [
            'role' => 'admin',
            'income' => [
                'pending_profit' => $pendingProfit,
                'received_profit' => $receivedProfit,
                'total_profit' => $pendingProfit + $receivedProfit,
            ],
            'net_income' => $receivedProfit,
            'statistics' => [
                'total_orders' => $totalOrders,
                'delivered_orders' => $deliveredOrders,
                'avg_profit_per_order' => $deliveredOrders > 0 
                    ? ($pendingProfit + $receivedProfit) / $deliveredOrders 
                    : 0,
            ],
        ];
    }
    
    /**
     * ==========================================
     * UNIVERSAL METHOD - Tự động detect role
     * ==========================================
     */
    
    /**
     * Lấy báo cáo thu nhập theo role
     */
    public function getIncomeReport($startDate = null, $endDate = null)
    {
        return match($this->role) {
            'driver' => $this->getDriverIncomeReport($startDate, $endDate),
            'customer' => $this->getCustomerIncomeReport($startDate, $endDate),
            'hub' => $this->getHubIncomeReport($startDate, $endDate),
            'admin' => $this->getAdminIncomeReport($startDate, $endDate),
            default => [
                'role' => $this->role,
                'error' => 'Role không hỗ trợ báo cáo thu nhập',
            ],
        };
    }
}