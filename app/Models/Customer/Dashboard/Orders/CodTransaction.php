<?php

namespace App\Models\Customer\Dashboard\Orders;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CodTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'cod_amount',
        'shipping_fee',
        'total_collected',
        
        // Shipper → Admin
        'shipper_payment_status',
        'driver_id',
        'shipper_transfer_time',
        'shipper_transfer_method',
        'shipper_transfer_proof',
        'shipper_note',
        'admin_confirm_time',
        'admin_confirm_by',
        'admin_note',
        
        // Admin → Sender
        'sender_payment_status',
        'sender_id',
        'sender_receive_amount',
        'platform_fee',
        'sender_transfer_time',
        'sender_transfer_method',
        'sender_transfer_proof',
        'sender_transfer_by',
        'sender_transfer_note',
    ];

    protected $casts = [
        'cod_amount' => 'decimal:2',
        'shipping_fee' => 'decimal:2',
        'total_collected' => 'decimal:2',
        'sender_receive_amount' => 'decimal:2',
        'platform_fee' => 'decimal:2',
        'shipper_transfer_time' => 'datetime',
        'admin_confirm_time' => 'datetime',
        'sender_transfer_time' => 'datetime',
    ];

    // ========== RELATIONSHIPS ==========
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

    public function adminConfirmer()
    {
        return $this->belongsTo(User::class, 'admin_confirm_by');
    }

    public function adminTransferer()
    {
        return $this->belongsTo(User::class, 'sender_transfer_by');
    }

    // ========== SCOPES ==========
    
    /**
     * ✅ Danh sách chờ shipper chuyển tiền
     */
    public function scopePendingShipperPayment($query)
    {
        return $query->where('shipper_payment_status', 'pending');
    }

    /**
     * ✅ Danh sách chờ admin xác nhận
     */
    public function scopeWaitingAdminConfirm($query)
    {
        return $query->where('shipper_payment_status', 'transferred');
    }

    /**
     * ✅ Danh sách chờ trả sender
     */
    public function scopePendingSenderPayment($query)
    {
        return $query->where('shipper_payment_status', 'confirmed')
                     ->where('sender_payment_status', 'pending');
    }

    // ========== METHODS ==========

    /**
     * ✅ Shipper xác nhận đã chuyển tiền
     */
    public function markShipperTransferred($method, $proof = null, $note = null)
    {
        $this->update([
            'shipper_payment_status' => 'transferred',
            'shipper_transfer_time' => now(),
            'shipper_transfer_method' => $method,
            'shipper_transfer_proof' => $proof,
            'shipper_note' => $note,
        ]);

        $this->order->update(['cod_status' => 'transferred']);
    }

    /**
     * ✅ Admin xác nhận đã nhận tiền từ shipper
     */
    public function adminConfirmReceived($adminId, $note = null)
    {
        $this->update([
            'shipper_payment_status' => 'confirmed',
            'admin_confirm_time' => now(),
            'admin_confirm_by' => $adminId,
            'admin_note' => $note,
        ]);

        // Tự động chuyển trạng thái sang chờ trả sender
        $this->update(['sender_payment_status' => 'pending']);
    }

    /**
     * ✅ Admin chuyển tiền cho sender
     */
    public function transferToSender($adminId, $method, $proof = null, $note = null)
    {
        $this->update([
            'sender_payment_status' => 'completed',
            'sender_transfer_time' => now(),
            'sender_transfer_method' => $method,
            'sender_transfer_proof' => $proof,
            'sender_transfer_by' => $adminId,
            'sender_transfer_note' => $note,
        ]);

        $this->order->update(['cod_status' => 'settled']);
    }

    /**
     * ✅ Tính số tiền sender sẽ nhận
     */
    public function calculateSenderAmount()
    {
        // Nếu người gửi trả phí ship → sender nhận full COD
        // Nếu người nhận trả → sender nhận COD - phí nền tảng (nếu có)
        
        $amount = $this->cod_amount;
        
        // Trừ phí nền tảng (ví dụ: 2% COD)
        $platformFee = $this->cod_amount * 0.02;
        
        $this->update([
            'platform_fee' => $platformFee,
            'sender_receive_amount' => $amount - $platformFee,
        ]);

        return $amount - $platformFee;
    }

    // ========== ACCESSORS ==========

    public function getShipperStatusLabelAttribute()
    {
        return match($this->shipper_payment_status) {
            'pending' => 'Chờ shipper chuyển',
            'transferred' => 'Đã chuyển, chờ xác nhận',
            'confirmed' => 'Admin đã nhận',
            'disputed' => 'Có tranh chấp',
            default => 'Không xác định'
        };
    }

    public function getSenderStatusLabelAttribute()
    {
        return match($this->sender_payment_status) {
            'pending' => 'Chờ chuyển',
            'processing' => 'Đang xử lý',
            'completed' => 'Đã chuyển',
            'failed' => 'Thất bại',
            default => 'Không xác định'
        };
    }

    public function getStatusBadgeAttribute()
    {
        if ($this->sender_payment_status === 'completed') {
            return 'success';
        }
        if ($this->shipper_payment_status === 'confirmed') {
            return 'info';
        }
        if ($this->shipper_payment_status === 'transferred') {
            return 'warning';
        }
        return 'secondary';
    }
}