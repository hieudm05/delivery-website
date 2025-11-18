<?php

namespace App\Models\Customer\Dashboard\Orders;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CodTransactionLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'cod_transaction_id',
        'user_id',
        'action',
        'old_status',
        'new_status',
        'note',
        'metadata',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ============ RELATIONSHIPS ============
    
    public function codTransaction()
    {
        return $this->belongsTo(CodTransaction::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ============ HELPER METHODS ============
    
    /**
     * Tạo log cho action driver transfer
     */
    public static function logDriverTransfer(CodTransaction $transaction, $userId, $data = [])
    {
        return self::create([
            'cod_transaction_id' => $transaction->id,
            'user_id' => $userId,
            'action' => 'driver_transfer',
            'old_status' => 'pending',
            'new_status' => 'transferred',
            'note' => $data['note'] ?? null,
            'metadata' => [
                'method' => $data['method'] ?? null,
                'amount' => $transaction->total_collected,
                'bank_account_id' => $data['bank_account_id'] ?? null,
                'proof_path' => $data['proof_path'] ?? null,
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Tạo log cho action hub confirm
     */
    public static function logHubConfirm(CodTransaction $transaction, $userId, $note = null)
    {
        return self::create([
            'cod_transaction_id' => $transaction->id,
            'user_id' => $userId,
            'action' => 'hub_confirm',
            'old_status' => 'transferred',
            'new_status' => 'confirmed',
            'note' => $note,
            'metadata' => [
                'amount_confirmed' => $transaction->total_collected,
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Tạo log cho action hub transfer to sender
     */
    public static function logHubTransferSender(CodTransaction $transaction, $userId, $data = [])
    {
        return self::create([
            'cod_transaction_id' => $transaction->id,
            'user_id' => $userId,
            'action' => 'hub_transfer_sender',
            'old_status' => 'pending',
            'new_status' => 'completed',
            'note' => $data['note'] ?? null,
            'metadata' => [
                'method' => $data['method'] ?? null,
                'amount' => $transaction->sender_receive_amount,
                'bank_account_id' => $data['bank_account_id'] ?? null,
                'proof_path' => $data['proof_path'] ?? null,
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Tạo log cho action hub pay driver commission
     */
    public static function logPayDriverCommission(CodTransaction $transaction, $userId, $note = null)
    {
        return self::create([
            'cod_transaction_id' => $transaction->id,
            'user_id' => $userId,
            'action' => 'hub_pay_commission',
            'old_status' => 'pending',
            'new_status' => 'paid',
            'note' => $note,
            'metadata' => [
                'commission_amount' => $transaction->driver_commission,
                'driver_id' => $transaction->driver_id,
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Tạo log cho action hub transfer to system
     */
    public static function logHubTransferSystem(CodTransaction $transaction, $userId, $data = [])
    {
        return self::create([
            'cod_transaction_id' => $transaction->id,
            'user_id' => $userId,
            'action' => 'hub_transfer_system',
            'old_status' => 'pending',
            'new_status' => 'transferred',
            'note' => $data['note'] ?? null,
            'metadata' => [
                'method' => $data['method'] ?? null,
                'amount' => $transaction->hub_system_amount,
                'proof_path' => $data['proof_path'] ?? null,
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Tạo log cho action system confirm
     */
    public static function logSystemConfirm(CodTransaction $transaction, $userId, $note = null)
    {
        return self::create([
            'cod_transaction_id' => $transaction->id,
            'user_id' => $userId,
            'action' => 'system_confirm',
            'old_status' => 'transferred',
            'new_status' => 'confirmed',
            'note' => $note,
            'metadata' => [
                'amount_confirmed' => $transaction->hub_system_amount,
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Tạo log cho dispute
     */
    public static function logDispute(CodTransaction $transaction, $userId, $reason, $proofPath = null)
    {
        return self::create([
            'cod_transaction_id' => $transaction->id,
            'user_id' => $userId,
            'action' => 'dispute',
            'old_status' => $transaction->shipper_payment_status,
            'new_status' => 'disputed',
            'note' => $reason,
            'metadata' => [
                'proof_path' => $proofPath,
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    // ============ SCOPES ============
    
    public function scopeByTransaction($query, $transactionId)
    {
        return $query->where('cod_transaction_id', $transactionId);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }

    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // ============ DISPLAY HELPERS ============
    
    public function getActionLabelAttribute()
    {
        return match($this->action) {
            'driver_transfer' => 'Driver chuyển tiền cho Hub',
            'hub_confirm' => 'Hub xác nhận nhận tiền',
            'hub_transfer_sender' => 'Hub chuyển COD cho Sender',
            'hub_pay_commission' => 'Hub trả commission cho Driver',
            'hub_transfer_system' => 'Hub nộp tiền cho System',
            'system_confirm' => 'Admin xác nhận nhận tiền',
            'dispute' => 'Tranh chấp',
            default => 'Không xác định'
        };
    }

    public function getActionIconAttribute()
    {
        return match($this->action) {
            'driver_transfer' => 'truck',
            'hub_confirm' => 'check-circle',
            'hub_transfer_sender' => 'send',
            'hub_pay_commission' => 'cash',
            'hub_transfer_system' => 'database',
            'system_confirm' => 'shield-check',
            'dispute' => 'exclamation-triangle',
            default => 'circle'
        };
    }

    public function getActionColorAttribute()
    {
        return match($this->action) {
            'driver_transfer' => 'primary',
            'hub_confirm' => 'success',
            'hub_transfer_sender' => 'info',
            'hub_pay_commission' => 'warning',
            'hub_transfer_system' => 'danger',
            'system_confirm' => 'success',
            'dispute' => 'danger',
            default => 'secondary'
        };
    }
}