<?php

namespace App\Models\Customer\Dashboard\Orders;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Order;

class CodTransaction extends Model
{
    protected $fillable = [
        'order_id', 'driver_id', 'sender_id',

        'cod_amount', 'shipping_fee', 'platform_fee',
        'sender_receive_amount', 'payer_shipping',

        // Shipper → Hub
        'shipper_payment_status', 'shipper_transfer_time',
        'shipper_transfer_method', 'shipper_transfer_proof',
        'shipper_note', 'hub_confirm_time',
        'hub_confirm_by', 'hub_confirm_note',

        // Hub → Sender
        'sender_payment_status', 'sender_transfer_time',
        'sender_transfer_method', 'sender_transfer_proof',
        'sender_transfer_by', 'sender_transfer_note',

        // Hub → System
        'hub_system_status', 'hub_system_amount',
        'hub_system_transfer_time', 'hub_system_method',
        'hub_system_proof', 'hub_system_transfer_by',
        'hub_system_note', 'system_confirm_time',
        'system_confirm_by', 'system_confirm_note',

        // Audit
        'created_by', 'updated_by',
    ];

    protected $casts = [
        'cod_amount' => 'decimal:2',
        'shipping_fee' => 'decimal:2',
        'platform_fee' => 'decimal:2',
        'sender_receive_amount' => 'decimal:2',
        'hub_system_amount' => 'decimal:2',

        'shipper_transfer_time' => 'datetime',
        'hub_confirm_time' => 'datetime',
        'sender_transfer_time' => 'datetime',
        'hub_system_transfer_time' => 'datetime',
        'system_confirm_time' => 'datetime',
    ];

    // ================= RELATIONS =================

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

    public function hubConfirmer()
    {
        return $this->belongsTo(User::class, 'hub_confirm_by');
    }

    public function senderTransferer()
    {
        return $this->belongsTo(User::class, 'sender_transfer_by');
    }

    public function hubSystemTransferer()
    {
        return $this->belongsTo(User::class, 'hub_system_transfer_by');
    }

    public function systemConfirmer()
    {
        return $this->belongsTo(User::class, 'system_confirm_by');
    }

    // ================= SCOPES =================

    public function scopePendingShipper($q)
    {
        return $q->where('shipper_payment_status', 'pending');
    }

    public function scopePendingSender($q)
    {
        return $q->where('sender_payment_status', 'pending');
    }

    public function scopePendingSystem($q)
    {
        return $q->where('hub_system_status', 'pending');
    }

    // ================= METHODS =================

    // 1. Shipper chuyển tiền cho hub
    public function markShipperTransferred($method, $proof = null, $note = null)
    {
        return $this->update([
            'shipper_payment_status' => 'transferred',
            'shipper_transfer_time' => now(),
            'shipper_transfer_method' => $method,
            'shipper_transfer_proof' => $proof,
            'shipper_note' => $note,
        ]);
    }

    // 2. Hub xác nhận tiền từ shipper
    public function hubConfirmShipper($hubId, $note = null)
    {
        return $this->update([
            'shipper_payment_status' => 'confirmed',
            'hub_confirm_time' => now(),
            'hub_confirm_by' => $hubId,
            'hub_confirm_note' => $note,
        ]);
    }

    // 3. Hub → Sender
    public function transferToSender($adminId, $method, $proof = null, $note = null)
    {
        return $this->update([
            'sender_payment_status' => 'completed',
            'sender_transfer_time' => now(),
            'sender_transfer_method' => $method,
            'sender_transfer_proof' => $proof,
            'sender_transfer_by' => $adminId,
            'sender_transfer_note' => $note,
        ]);
    }

    // 4. Hub → System
    public function transferToSystem($hubId, $method, $proof = null, $note = null)
    {
        return $this->update([
            'hub_system_status' => 'transferred',
            'hub_system_transfer_time' => now(),
            'hub_system_method' => $method,
            'hub_system_proof' => $proof,
            'hub_system_transfer_by' => $hubId,
            'hub_system_note' => $note,
        ]);
    }

    // 5. System xác nhận nhận tiền
    public function systemConfirm($adminId, $note = null)
    {
        return $this->update([
            'hub_system_status' => 'confirmed',
            'system_confirm_time' => now(),
            'system_confirm_by' => $adminId,
            'system_confirm_note' => $note,
        ]);
    }

    // Tính lại tiền shop nhận
    public function calculateAmounts()
    {
        $this->platform_fee = $this->cod_amount * 0.02; // ví dụ 2%
        $this->sender_receive_amount = $this->cod_amount - $this->platform_fee;

        $this->hub_system_amount = $this->platform_fee;

        $this->save();
    }
}
