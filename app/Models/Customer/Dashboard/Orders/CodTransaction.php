<?php
namespace App\Models\Customer\Dashboard\Orders;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\BankAccount;

class CodTransaction extends Model
{
    protected $fillable = [
        'order_id', 'driver_id', 'sender_id', 'hub_id',
        'cod_amount', 'shipping_fee', 'platform_fee',
        'sender_receive_amount', 'payer_shipping', 'total_collected',
        'driver_commission', 'driver_commission_status', 'driver_paid_at',
        
        'shipper_payment_status', 'shipper_transfer_time',
        'shipper_transfer_method', 'shipper_transfer_proof',
        'shipper_bank_account_id', 'shipper_note',
        'hub_confirm_time', 'hub_confirm_by', 'hub_confirm_note',
        
        'sender_payment_status', 'sender_transfer_time',
        'sender_transfer_method', 'sender_transfer_proof',
        'sender_bank_account_id', 'sender_transfer_by', 'sender_transfer_note',
        
        'hub_system_status', 'hub_system_amount',
        'hub_system_transfer_time', 'hub_system_method',
        'hub_system_proof', 'hub_system_transfer_by',
        'hub_system_note', 'system_confirm_time',
        'system_confirm_by', 'system_confirm_note',
        
        'created_by', 'updated_by',
    ];

    protected $casts = [
        'cod_amount' => 'decimal:2',
        'shipping_fee' => 'decimal:2',
        'platform_fee' => 'decimal:2',
        'sender_receive_amount' => 'decimal:2',
        'total_collected' => 'decimal:2',
        'hub_system_amount' => 'decimal:2',
        'driver_commission' => 'decimal:2',
        'driver_paid_at' => 'datetime',
        
        'shipper_transfer_time' => 'datetime',
        'hub_confirm_time' => 'datetime',
        'sender_transfer_time' => 'datetime',
        'hub_system_transfer_time' => 'datetime',
        'system_confirm_time' => 'datetime',
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

    public function scopeWaitingAdminConfirm($q)
    {
        return $q->where('shipper_payment_status', 'transferred');
    }

    public function scopePendingSenderPayment($q)
    {
        return $q->where('shipper_payment_status', 'confirmed')
                 ->where('sender_payment_status', 'pending');
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

    // ============ AUTO CALCULATION ============
    
    /**
     * ✅ Tạo giao dịch COD với Driver Commission
     */
    public static function createFromOrder(Order $order)
    {
        // Validate sender_id
        $senderId = $order->sender_id ?? $order->orderGroup?->user_id;
        if (!$senderId) {
            throw new \Exception("Order #{$order->id} thiếu sender_id");
        }
        
        // Lấy hub_id từ bảng hubs
        $hub = \App\Models\Hub\Hub::where('post_office_id', $order->post_office_id)->first();
        $hubId = $hub?->user_id;
        if (!$hubId) {
            throw new \Exception("Không tìm thấy Hub cho post_office_id: {$order->post_office_id}");
        }
        
        // Lấy phí từ order
        $codAmount = $order->cod_amount ?? 0;
        $shippingFee = $order->shipping_fee ?? 0;
        $codFee = $order->cod_fee ?? 0;
        $payer = $order->payer;
        
        // ✅ TÍNH DRIVER COMMISSION
        $driverCommissionRate = config('delivery.driver_commission_rate', 0.5);
        $minCommission = config('delivery.min_driver_commission', 5000);
        $maxCommission = config('delivery.max_driver_commission', 50000);
        
        $driverCommission = $shippingFee * $driverCommissionRate;
        $driverCommission = max($minCommission, min($driverCommission, $maxCommission));
        
        // Sender nhận đủ COD
        $senderReceiveAmount = $codAmount;
        
        // Tổng Driver nộp Hub
        if ($payer === 'recipient') {
            $totalCollected = $codAmount + $shippingFee + $codFee;
        } else {
            $totalCollected = $codAmount;
        }
        
        // Hub nộp COD Fee cho System
        $platformFee = $codFee;
        $hubSystemAmount = $codFee;
        
        return self::create([
            'order_id' => $order->id,
            'driver_id' => $order->driver_id,
            'sender_id' => $senderId,
            'hub_id' => $hubId,
            
            'cod_amount' => $codAmount,
            'shipping_fee' => $shippingFee,
            'platform_fee' => $platformFee,
            'sender_receive_amount' => $senderReceiveAmount,
            'payer_shipping' => $payer,
            'total_collected' => $totalCollected,
            'hub_system_amount' => $hubSystemAmount,
            'driver_commission' => $driverCommission,
            'driver_commission_status' => 'pending',
            
            'shipper_payment_status' => 'pending',
            'sender_payment_status' => 'not_ready',
            'hub_system_status' => 'not_ready',
            
            'created_by' => auth()->id() ?? $senderId,
        ]);
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

        $updated = $this->update([
            'shipper_payment_status' => 'confirmed',
            'hub_confirm_time' => now(),
            'hub_confirm_by' => $hubAdminId,
            'hub_confirm_note' => $note,
            
            // Mở khóa các bước tiếp theo
            'sender_payment_status' => 'pending',
            'hub_system_status' => 'pending',
        ]);

        return $updated;
    }

    /**
     * Bước 3: Hub trả tiền COD cho Sender
     */
    public function hubTransferToSender($hubAdminId, $method, $bankAccountId = null, $proof = null, $note = null)
    {
        if ($this->sender_payment_status !== 'pending') {
            throw new \Exception('Chưa sẵn sàng trả tiền cho sender');
        }

        $updated = $this->update([
            'sender_payment_status' => 'completed',
            'sender_transfer_time' => now(),
            'sender_transfer_method' => $method,
            'sender_bank_account_id' => $bankAccountId,
            'sender_transfer_proof' => $proof,
            'sender_transfer_by' => $hubAdminId,
            'sender_transfer_note' => $note,
        ]);

        return $updated;
    }

    /**
     * ✅ Bước 3.5: Hub trả commission cho Driver
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
     * Bước 4: Hub nộp COD Fee cho System
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
     * Bước 5: Admin xác nhận đã nhận
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

    public function isFullyCompleted()
    {
        return $this->sender_payment_status === 'completed' 
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
            'total_collected' => $this->total_collected,
            
            // Phân chia
            'sender_receive_amount' => $this->sender_receive_amount,
            'driver_commission' => $this->driver_commission,
            'hub_profit' => $this->shipping_fee - $this->driver_commission,
            'hub_system_amount' => $this->hub_system_amount,
            
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
}