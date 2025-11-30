<?php
namespace App\Models\Customer\Dashboard\Orders;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\BankAccount;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CodTransaction extends Model
{
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
     *    - sender_receive_before_debt = cod_amount - platform_fee - cod_fee
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
        // ========== 1. XÁC ĐỊNH THÔNG TIN CƠ BẢN ==========
        $senderId = $order->sender_id ?? $order->orderGroup?->user_id;
        if (!$senderId) {
            throw new \Exception("Order #{$order->id} thiếu sender_id");
        }
        
        $hub = \App\Models\Hub\Hub::where('post_office_id', $order->post_office_id)->first();
        $hubId = $hub?->user_id;
        if (!$hubId) {
            throw new \Exception("Không tìm thấy Hub cho post_office_id: {$order->post_office_id}");
        }
        
        $codAmount = (float)($order->cod_amount ?? 0);
        $shippingFee = (float)($order->shipping_fee ?? 0);
        $codFee = (float)($order->cod_fee ?? 0);
        $platformFee = (float)config('delivery.platform_base_fee', 2000);
        $payer = $order->payer ?? 'sender';
        
        // ========== 2. TÍNH TIỀN DRIVER THU TỪ NGƯỜI NHẬN ==========
        if ($payer === 'recipient') {
            // Người nhận trả: shipping + cod
            $totalCollected = $codAmount + $shippingFee;
        } else {
            // Người gửi trả ship, người nhận chỉ trả COD
            $totalCollected = $codAmount;
        }
        
        // ========== 3. TÍNH DRIVER COMMISSION ==========
        $driverCommissionRate = config('delivery.driver_commission_rate', 0.5);
        $minCommission = config('delivery.min_driver_commission', 5000);
        $maxCommission = config('delivery.max_driver_commission', 50000);
        
        $driverCommission = $shippingFee * $driverCommissionRate;
        $driverCommission = max($minCommission, min($driverCommission, $maxCommission));
        
        // ========== 4. XỬ LÝ NỢ CỦA SENDER ==========
        $senderDebt = 0;
        if (config('delivery.debt.auto_deduct', true)) {
            $senderDebt = self::getSenderDebtWithHub($senderId, $hubId);
        }
        
        // ========== 5. TÍNH TIỀN SENDER NHẬN ==========
        // Sender nhận = COD - platform_fee - cod_fee - nợ
        $senderReceiveBeforeDebt = $codAmount - $platformFee - $codFee;
        $senderReceiveAmount = $senderReceiveBeforeDebt - $senderDebt;
        
        // Nếu âm → tạo nợ mới
        if ($senderReceiveAmount < 0) {
            $newDebt = abs($senderReceiveAmount);
            self::createDebt($senderId, $hubId, $newDebt, $order->id, "Nợ từ đơn #{$order->id}");
            $senderReceiveAmount = 0;
            $senderDebt += $newDebt;
        }
        
        // ========== 6. TÍNH CHIA LỢI NHUẬN HUB & ADMIN ==========
        // Hub nhận tất cả tiền từ Driver
        $hubReceivedTotal = $totalCollected;
        
        // Hub phải trả cho Sender + Driver
        $hubMustPay = $senderReceiveAmount + $driverCommission;
        
        // Phần còn lại chia giữa Hub và Admin
        $remainingProfit = $hubReceivedTotal - $hubMustPay;
        
        $hubProfitShare = config('delivery.hub_profit_share', 0.60);
        $adminProfitShare = config('delivery.admin_profit_share', 0.40);
        
        $hubProfit = round($remainingProfit * $hubProfitShare);
        $adminProfit = round($remainingProfit * $adminProfitShare);
        
        // ========== 7. KIỂM TRA CÂN BẰNG DÒNG TIỀN ==========
        $totalDistributed = $senderReceiveAmount + $driverCommission + $hubProfit + $adminProfit;
        $diff = abs($totalCollected - $totalDistributed);
        
        if ($diff > 0.01) {
            Log::error("❌ Dòng tiền không cân bằng!", [
                'order_id' => $order->id,
                'thu_vao' => $totalCollected,
                'chi_ra' => $totalDistributed,
                'chenh_lech' => $totalCollected - $totalDistributed,
                'breakdown' => [
                    'cod_amount' => $codAmount,
                    'shipping_fee' => $shippingFee,
                    'cod_fee' => $codFee,
                    'platform_fee' => $platformFee,
                    'total_collected' => $totalCollected,
                    'sender_receive' => $senderReceiveAmount,
                    'sender_debt' => $senderDebt,
                    'driver_commission' => $driverCommission,
                    'hub_profit' => $hubProfit,
                    'admin_profit' => $adminProfit,
                ]
            ]);
            throw new \Exception("Dòng tiền không cân bằng! Thu: {$totalCollected} - Chi: {$totalDistributed}");
        }
        
        // ========== 8. TẠO TRANSACTION ==========
        $transaction = self::create([
            'order_id' => $order->id,
            'driver_id' => $order->driver_id,
            'sender_id' => $senderId,
            'hub_id' => $hubId,
            
            'cod_amount' => $codAmount,
            'shipping_fee' => $shippingFee,
            'platform_fee' => $platformFee,
            'cod_fee' => $codFee,
            'payer_shipping' => $payer,
            
            'total_collected' => $totalCollected,
            'sender_receive_amount' => $senderReceiveAmount,
            'sender_debt_deducted' => $senderDebt,
            'driver_commission' => $driverCommission,
            'hub_profit' => $hubProfit,
            'admin_profit' => $adminProfit,
            'hub_system_amount' => $adminProfit, // Admin profit = platform fee
            
            'driver_commission_status' => 'pending',
            'shipper_payment_status' => 'pending',
            'sender_payment_status' => $senderReceiveAmount > 0 ? 'not_ready' : 'not_applicable',
            'hub_system_status' => 'not_ready',
            
            'sender_fee_paid' => $payer === 'sender' ? $shippingFee + $platformFee + $codFee : $platformFee + $codFee,
            'recipient_fee_paid' => $payer === 'recipient' ? $shippingFee : 0,
            
            'created_by' => Auth::id() ?? $senderId,
        ]);
        
        // ========== 9. GHI NHẬN NỢ ĐÃ TRỪ ==========
        if ($senderDebt > 0) {
            self::recordDebtDeduction($senderId, $hubId, $order->id, $senderDebt);
        }
        
        Log::info("✅ Transaction created successfully", [
            'order_id' => $order->id,
            'transaction_id' => $transaction->id,
            'summary' => [
                'total_collected' => $totalCollected,
                'sender_receive' => $senderReceiveAmount,
                'driver_commission' => $driverCommission,
                'hub_profit' => $hubProfit,
                'admin_profit' => $adminProfit,
                'debt_deducted' => $senderDebt,
            ]
        ]);
        
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
}