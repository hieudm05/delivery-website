<?php
namespace App\Models\Customer\Dashboard\Orders;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\BankAccount;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CodTransaction extends Model
{
    protected $appends = ['is_returned_order'];
    protected $fillable = [
        'order_id', 'driver_id', 'sender_id', 'hub_id',
        'cod_amount', 'shipping_fee', 'platform_fee', 'cod_fee',
        'sender_receive_amount', 'sender_debt_deducted', 'payer_shipping', 
        'total_collected', 'driver_commission', 'hub_profit', 'admin_profit',
        'driver_commission_status', 'driver_paid_at',
        
        // Driver -> Hub
        'shipper_payment_status', 'shipper_transfer_time',
        'shipper_transfer_method', 'shipper_transfer_proof',
        'shipper_bank_account_id', 'shipper_note',
        
        // Hub confirm from Driver
        'hub_confirm_time', 'hub_confirm_by', 'hub_confirm_note',
        
        // Hub -> Sender
        'sender_payment_status', 'sender_transfer_time',
        'sender_transfer_method', 'sender_transfer_proof',
        'sender_bank_account_id', 'sender_transfer_by', 'sender_transfer_note',
        
        // Hub -> System (Admin Profit)
        'hub_system_status', 'hub_system_amount',
        'hub_system_transfer_time', 'hub_system_method',
        'hub_system_proof', 'hub_system_transfer_by',
        'hub_system_note',
        
        // Admin confirm from Hub
        'system_confirm_time', 'system_confirm_by', 'system_confirm_note',
        
        // Sender/Recipient platform fee
        'sender_fee_paid', 'sender_fee_paid_at', 'sender_fee_payment_method',
        'recipient_fee_paid', 'recipient_fee_paid_at', 'recipient_fee_payment_method',

        'sender_fee_status',
        'sender_fee_payment_proof',
        'sender_fee_confirmed_by',
        'sender_fee_confirmed_at',
        'sender_fee_rejection_reason',
        'debt_processed',

        'sender_debt_payment_method',
        'sender_debt_payment_proof',
        'sender_debt_paid_at',
        'sender_debt_payment_status',
        'sender_debt_confirmed_at',
        'sender_debt_confirmed_by',
        'sender_debt_rejection_reason',
        'sender_debt_rejected_at',
        'sender_debt_rejected_by',
        
        'created_by', 'updated_by',
    ];

    protected $casts = [
        'cod_amount' => 'decimal:2',
        'shipping_fee' => 'decimal:2',
        'platform_fee' => 'decimal:2',
        'cod_fee' => 'decimal:2',
        'sender_receive_amount' => 'decimal:2',
        'sender_debt_deducted' => 'decimal:2',
        'total_collected' => 'decimal:2',
        'driver_commission' => 'decimal:2',
        'hub_profit' => 'decimal:2',
        'admin_profit' => 'decimal:2',
        'hub_system_amount' => 'decimal:2',
        'sender_fee_paid' => 'decimal:2',
        'recipient_fee_paid' => 'decimal:2',
        
        'driver_paid_at' => 'datetime',
        'shipper_transfer_time' => 'datetime',
        'hub_confirm_time' => 'datetime',
        'sender_transfer_time' => 'datetime',
        'hub_system_transfer_time' => 'datetime',
        'system_confirm_time' => 'datetime',
        'sender_fee_paid_at' => 'datetime',
        'recipient_fee_paid_at' => 'datetime',
        'sender_fee_confirmed_at' => 'datetime',
        'debt_processed' => 'boolean',

        'sender_debt_paid_at' => 'datetime',
        'sender_debt_confirmed_at' => 'datetime',
        'sender_debt_rejected_at' => 'datetime',
    ];

    // ============ RELATIONSHIPS ============
    
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function hub()
    {
        return $this->belongsTo(User::class, 'hub_id');
    }

    public function shipperBankAccount()
    {
        return $this->belongsTo(BankAccount::class, 'shipper_bank_account_id');
    }

    public function senderBankAccount()
    {
        return $this->belongsTo(BankAccount::class, 'sender_bank_account_id');
    }

    public function hubConfirmer()
    {
        return $this->belongsTo(User::class, 'hub_confirm_by');
    }

    public function senderTransferer()
    {
        return $this->belongsTo(User::class, 'sender_transfer_by');
    }

    public function systemConfirmer()
    {
        return $this->belongsTo(User::class, 'system_confirm_by');
    }

    // ============ SCOPES ============
    
    public function scopePendingShipperPayment($q)
    {
        return $q->where('shipper_payment_status', 'pending');
    }

    public function scopeWaitingHubConfirm($q)
    {
        return $q->where('shipper_payment_status', 'transferred');
    }

    public function scopePendingSenderPayment($q)
    {
        return $q->where('shipper_payment_status', 'confirmed')
                 ->where('sender_payment_status', 'pending');
    }

    public function scopePendingSystemPayment($q)
    {
        return $q->where('hub_system_status', 'pending');
    }

    public function scopeWaitingSystemConfirm($q)
    {
        return $q->where('hub_system_status', 'transferred');
    }

    public function scopeByDriver($q, $driverId)
    {
        return $q->where('driver_id', $driverId);
    }

    public function scopeBySender($q, $senderId)
    {
        return $q->where('sender_id', $senderId);
    }

    public function scopeByHub($q, $hubId)
    {
        return $q->where('hub_id', $hubId);
    }

    // ============ MAIN CALCULATION METHOD ============
    
    /**
     * TẠO TRANSACTION TỪ ORDER
     * 
     * LOGIC PHÂN PHỐI DÒNG TIỀN:
     * 
     * 1. Xác định tiền Driver thu được từ người nhận:
     *    - Nếu payer = "recipient": total_collected = cod_amount + shipping_fee
     *    - Nếu payer = "sender": total_collected = cod_amount
     * 
     * 2. Tính Driver Commission (từ shipping_fee):
     *    - driver_commission = shipping_fee * 50% (min: 5K, max: 50K)
     * 
     * 3. Tính tiền Sender nhận (từ COD):
     *    - sender_receive_before_debt = cod_amount - cod_fee
     *    - Trừ nợ (nếu có): sender_receive_amount = sender_receive_before_debt - debt
     * 
     * 4. Tính phần còn lại để chia Hub & Admin:
     *    - hub_received_total = total_collected (tiền Driver nộp)
     *    - hub_must_pay = sender_receive_amount + driver_commission
     *    - remaining_profit = hub_received_total - hub_must_pay
     *    - hub_profit = remaining_profit * 60%
     *    - admin_profit = remaining_profit * 40%
     */
   public static function createFromOrder(Order $order)
{
    // ================== 1. BASIC ==================
    $senderId = $order->sender_id ?? $order->orderGroup?->user_id;
    if (!$senderId) {
        throw new \Exception("Order #{$order->id} thiếu sender_id");
    }

    $hub = \App\Models\Hub\Hub::where('post_office_id', $order->post_office_id)->first();
    if (!$hub) {
        throw new \Exception("Không tìm thấy hub");
    }

    $hubId = $hub->user_id;

    $codAmount   = (float) ($order->cod_amount ?? 0);
    $shippingFee = (float) ($order->shipping_fee ?? 0);
    $codFee      = (float) ($order->cod_fee ?? 0);
    $senderTotal = (float) ($order->sender_total ?? 0);
    $payer       = $order->payer ?? 'sender';

    // ================== 2. TOTAL COLLECTED (FROM RECIPIENT ONLY) ==================
    if ($payer === 'recipient') {
        $totalCollected = $codAmount + $shippingFee;
    } else {
        $totalCollected = $codAmount;
    }

    if ($codAmount == 0) {
        $totalCollected = 0;
    }

    // ================== 3. DRIVER COMMISSION FUNC ==================
    $calcDriverCommission = function ($baseAmount) {
        $rate = config('delivery.driver_commission_rate', 0.5);
        $min  = config('delivery.min_driver_commission', 5000);
        $max  = config('delivery.max_driver_commission', 50000);

        return max($min, min($baseAmount * $rate, $max));
    };

    // ================== 4. OLD DEBT ==================
    $senderOldDebt = config('delivery.debt.auto_deduct', true)
        ? self::getSenderDebtWithHub($senderId, $hubId)
        : 0;

    // ================== 5. INIT ==================
    $senderReceiveAmount = 0;
    $driverCommission = 0;
    $hubProfit = 0;
    $adminProfit = 0;

    $needCreateDebt = false;
    $newDebt = 0;

    // ================== 6. CASE HANDLING ==================

    // 🟢 CASE 1 — ĐƠN CÓ COD
    if ($codAmount > 0) {

        $senderReceiveBeforeDebt = $codAmount - $senderTotal;
        $senderReceiveAmount = max(0, $senderReceiveBeforeDebt - $senderOldDebt);

        $driverCommission = $calcDriverCommission($shippingFee);

        $hubMustPay = $senderReceiveAmount + $driverCommission;
        $remaining = $totalCollected - $hubMustPay;

        $hubProfit = round($remaining * config('delivery.hub_profit_share', 0.6));
        $adminProfit = round($remaining * config('delivery.admin_profit_share', 0.4));

    }

    // 🟢 CASE 2 — KHÔNG COD, SENDER TRẢ PHÍ SHIP
    elseif ($codAmount == 0 && $payer === 'sender' && $senderTotal > 0) {

        // tạo nợ để sender trả
        $needCreateDebt = true;
        $newDebt = $senderTotal;

        // coi sender_total là DOANH THU VẬN HÀNH
        $virtualRevenue = $senderTotal;

        $driverCommission = $calcDriverCommission($virtualRevenue);
        $hubProfit = $virtualRevenue - $driverCommission;
        $adminProfit = 0;

        $senderReceiveAmount = 0;
    }

    // 🔴 CASE 3 — PHÍ HOÀN / PHẠT
    else {

        if ($senderTotal > 0) {
            $needCreateDebt = true;
            $newDebt = $senderTotal;
        }

        $driverCommission = 0;
        $hubProfit = $newDebt;
        $adminProfit = 0;
    }

    // ================== 7. CASH FLOW CHECK (ONLY WHEN COLLECTED) ==================
    if ($totalCollected > 0) {
        $distributed =
            $senderReceiveAmount +
            $driverCommission +
            $hubProfit +
            $adminProfit;

        if (abs($totalCollected - $distributed) > 0.01) {
            throw new \Exception("❌ Lệch dòng tiền đơn #{$order->id}");
        }
    }

    // ================== 8. CREATE TRANSACTION ==================
    $transaction = self::create([
        'order_id' => $order->id,
        'driver_id' => $order->driver_id,
        'sender_id' => $senderId,
        'hub_id' => $hubId,

        'cod_amount' => $codAmount,
        'shipping_fee' => $shippingFee,
        'cod_fee' => $codFee,
        'payer_shipping' => $payer,

        'total_collected' => $totalCollected,
        'sender_receive_amount' => $senderReceiveAmount,
        'sender_debt_deducted' => $senderOldDebt,
        'driver_commission' => $driverCommission,
        'hub_profit' => $hubProfit,
        'admin_profit' => $adminProfit,
        'hub_system_amount' => $adminProfit,

        'driver_commission_status' => $driverCommission > 0 ? 'pending' : 'not_applicable',
        'shipper_payment_status' => $totalCollected > 0 ? 'pending' : 'not_applicable',
        'sender_payment_status' => $senderReceiveAmount > 0 ? 'not_ready' : 'not_applicable',
        'hub_system_status' => 'not_ready',

        'sender_fee_paid' => $payer === 'sender' ? $senderTotal : 0,
        'recipient_fee_paid' => $payer === 'recipient' ? $shippingFee : 0,

        'created_by' => Auth::id() ?? $senderId,
    ]);

    // ================== 9. CREATE DEBT ==================
    if ($needCreateDebt && $newDebt > 0) {
        self::createDebt(
            $senderId,
            $hubId,
            $newDebt,
            $order->id,
            $codAmount == 0
                ? "Phí đơn không COD #{$order->id}"
                : "Nợ đơn #{$order->id}"
        );
    }

    if ($senderOldDebt > 0 && !$needCreateDebt) {
        self::recordDebtDeduction($senderId, $hubId, $order->id, $senderOldDebt);
    }

    return $transaction;
    }



    // ============ HELPER METHODS ============
    
    private static function getSenderDebtWithHub($senderId, $hubId)
    {
        $debt = \App\Models\SenderDebt::where('sender_id', $senderId)
            ->where('hub_id', $hubId)
            ->where('type', 'debt')
            ->where('status', 'unpaid')
            ->sum('amount');
        
        return (float)$debt;
    }

    private static function recordDebtDeduction($senderId, $hubId, $orderId, $amount)
    {
        if ($amount > 0) {
            \App\Models\SenderDebt::recordDeduction($senderId, $hubId, $orderId, $amount);
        }
    }

    private static function createDebt($senderId, $hubId, $amount, $orderId, $note)
    {
        \App\Models\SenderDebt::createDebt($senderId, $hubId, $amount, $orderId, $note);
    }

    // ============ PAYMENT FLOW METHODS ============
    
    /**
     * Bước 1: Driver chuyển tiền cho Hub
     */
    public function driverTransferToHub($method, $bankAccountId = null, $proof = null, $note = null)
    {
        if ($this->shipper_payment_status !== 'pending') {
            throw new \Exception('Giao dịch không ở trạng thái chờ chuyển tiền');
        }

        return $this->update([
            'shipper_payment_status' => 'transferred',
            'shipper_transfer_time' => now(),
            'shipper_transfer_method' => $method,
            'shipper_bank_account_id' => $bankAccountId,
            'shipper_transfer_proof' => $proof,
            'shipper_note' => $note,
        ]);
    }

    /**
     * Bước 2: Hub xác nhận đã nhận tiền từ Driver
     */
    public function hubConfirmReceived($hubAdminId, $note = null)
    {
        if ($this->shipper_payment_status !== 'transferred') {
            throw new \Exception('Driver chưa chuyển tiền');
        }

        return $this->update([
            'shipper_payment_status' => 'confirmed',
            'hub_confirm_time' => now(),
            'hub_confirm_by' => $hubAdminId,
            'hub_confirm_note' => $note,
            
            'sender_payment_status' => $this->sender_receive_amount > 0 ? 'pending' : 'not_applicable',
            'hub_system_status' => 'pending',
        ]);
    }

    /**
     * Bước 3: Hub trả tiền COD cho Sender
     */
    public function hubTransferToSender($hubAdminId, $method, $bankAccountId = null, $proof = null, $note = null)
    {
        if ($this->sender_payment_status !== 'pending') {
            throw new \Exception('Chưa sẵn sàng trả tiền cho sender');
        }

        return $this->update([
            'sender_payment_status' => 'completed',
            'sender_transfer_time' => now(),
            'sender_transfer_method' => $method,
            'sender_bank_account_id' => $bankAccountId,
            'sender_transfer_proof' => $proof,
            'sender_transfer_by' => $hubAdminId,
            'sender_transfer_note' => $note,
        ]);
    }

    /**
     * Bước 3.5: Hub trả commission cho Driver
     */
    public function payDriverCommission($hubAdminId, $note = null)
    {
        if ($this->driver_commission_status !== 'pending') {
            throw new \Exception('Commission đã được trả hoặc không hợp lệ');
        }

        if ($this->shipper_payment_status !== 'confirmed') {
            throw new \Exception('Hub chưa xác nhận nhận tiền từ driver');
        }

        return $this->update([
            'driver_commission_status' => 'paid',
            'driver_paid_at' => now(),
        ]);
    }

    /**
     * Bước 4: Hub nộp Admin Profit cho System
     */
    public function hubTransferToSystem($hubAdminId, $method, $proof = null, $note = null)
    {
        if ($this->hub_system_status !== 'pending') {
            throw new \Exception('Chưa sẵn sàng nộp tiền cho hệ thống');
        }

        return $this->update([
            'hub_system_status' => 'transferred',
            'hub_system_transfer_time' => now(),
            'hub_system_method' => $method,
            'hub_system_proof' => $proof,
            'hub_system_transfer_by' => $hubAdminId,
            'hub_system_note' => $note,
        ]);
    }

    /**
     * Bước 5: Admin xác nhận đã nhận tiền từ Hub
     */
    public function systemConfirmReceived($adminId, $note = null)
    {
        if ($this->hub_system_status !== 'transferred') {
            throw new \Exception('Hub chưa chuyển tiền');
        }

        return $this->update([
            'hub_system_status' => 'confirmed',
            'system_confirm_time' => now(),
            'system_confirm_by' => $adminId,
            'system_confirm_note' => $note,
        ]);
    }

    // ============ STATUS HELPERS ============
    
    public function canDriverTransfer()
    {
        return $this->shipper_payment_status === 'pending';
    }

    public function canHubConfirm()
    {
        return $this->shipper_payment_status === 'transferred';
    }

    public function canHubTransferToSender()
    {
        return $this->sender_payment_status === 'pending';
    }

    public function canPayDriverCommission()
    {
        return $this->driver_commission_status === 'pending' 
            && $this->shipper_payment_status === 'confirmed';
    }

    public function canHubTransferToSystem()
    {
        return $this->hub_system_status === 'pending';
    }

    public function canSystemConfirm()
    {
        return $this->hub_system_status === 'transferred';
    }

    public function isFullyCompleted()
    {
        return $this->shipper_payment_status === 'confirmed'
            && ($this->sender_payment_status === 'completed' || $this->sender_payment_status === 'not_applicable')
            && $this->hub_system_status === 'confirmed'
            && $this->driver_commission_status === 'paid';
    }

    // ============ DISPLAY HELPERS ============
    
    public function getPaymentSummary()
    {
        return [
            'cod_amount' => $this->cod_amount,
            'shipping_fee' => $this->shipping_fee,
            'platform_fee' => $this->platform_fee,
            'cod_fee' => $this->cod_fee,
            'total_collected' => $this->total_collected,
            
            'sender_receive_amount' => $this->sender_receive_amount,
            'sender_debt_deducted' => $this->sender_debt_deducted,
            'driver_commission' => $this->driver_commission,
            'hub_profit' => $this->hub_profit,
            'admin_profit' => $this->admin_profit,
            
            'sender_fee_paid' => $this->sender_fee_paid,
            'recipient_fee_paid' => $this->recipient_fee_paid,
            
            'payer' => $this->payer_shipping === 'sender' ? 'Người gửi' : 'Người nhận',
        ];
    }

    public function getShipperStatusLabelAttribute()
    {
        return match($this->shipper_payment_status) {
            'pending' => 'Chờ chuyển tiền',
            'transferred' => 'Đã chuyển - Chờ xác nhận',
            'confirmed' => 'Đã xác nhận',
            'disputed' => 'Tranh chấp',
            default => 'Không xác định'
        };
    }

    public function getSenderStatusLabelAttribute()
    {
        return match($this->sender_payment_status) {
            'not_ready' => 'Chưa sẵn sàng',
            'pending' => 'Chờ chuyển tiền',
            'completed' => 'Đã chuyển',
            'not_applicable' => 'Không áp dụng',
            default => 'Không xác định'
        };
    }

    public function getSystemStatusLabelAttribute()
    {
        return match($this->hub_system_status) {
            'not_ready' => 'Chưa sẵn sàng',
            'pending' => 'Chờ nộp',
            'transferred' => 'Đã nộp - Chờ xác nhận',
            'confirmed' => 'Đã xác nhận',
            default => 'Không xác định'
        };
    }

    public function getDriverCommissionStatusLabelAttribute()
    {
        return match($this->driver_commission_status) {
            'pending' => 'Chờ thanh toán',
            'paid' => 'Đã thanh toán',
            default => 'Không xác định'
        };
    }
    public function disputes()
    {
        return $this->hasMany(\App\Models\CodDispute::class);
    }

    public function feeConfirmer()
    {
        return $this->belongsTo(User::class, 'sender_fee_confirmed_by');
    }
    public function hubConfirmSenderReceived($hubAdminId, $proofFile = null)
    {
        // Xác thực Hub đã chuyển tiền cho Sender
        return $this->update([
            'sender_payment_status' => 'completed',
            'sender_transfer_proof' => $proofFile,
            'sender_transfer_by' => $hubAdminId,
            'sender_transfer_time' => now(),
        ]);
    }
    /**
     * Kiểm tra đơn có bị hoàn về không
     */
    // Dòng 565-569 - THAY THẾ
    public function getIsReturnedOrderAttribute()
    {
        return $this->order && $this->order->has_return === true;
    }

    /**
     * Driver có cần nộp tiền không
     */
    public function driverMustPayCod()
    {
        // Nếu đơn có issue (giao thất bại) → Driver KHÔNG cần nộp
        if ($this->order && $this->order->deliveryIssues()->exists()) {
            return false;
        }
        
        // Nếu đơn đang hoàn về → Driver KHÔNG cần nộp
        if ($this->is_returned_order) {
            return false;
        }
        
        //Nếu đơn không ở trạng thái đã giao → không cần nộp
        if ($this->order && $this->order->status !== Order::STATUS_DELIVERED) {
            return false;
        }
        
        return $this->shipper_payment_status === 'pending';
    }

    /**
     * Customer có được nhận COD không
     */
   public function customerCanReceiveCod()
{
    //Nếu đơn có issue → Customer KHÔNG nhận COD
    if ($this->order && $this->order->deliveryIssues()->exists()) {
        return false;
    }
    
    // Nếu đơn bị hoàn về → Customer KHÔNG nhận COD
    if ($this->is_returned_order) {
        return false;
    }
    
    // Chỉ nhận COD khi đơn đã giao thành công
    if ($this->order && $this->order->status !== Order::STATUS_DELIVERED) {
        return false;
    }
    
    return $this->sender_receive_amount > 0 && 
        $this->sender_payment_status === 'pending';
}
    public function debtConfirmer()
    {
        return $this->belongsTo(User::class, 'sender_debt_confirmed_by');
    }

    public function debtRejecter()
    {
        return $this->belongsTo(User::class, 'sender_debt_rejected_by');
    }
}