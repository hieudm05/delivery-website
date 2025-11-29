<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SenderDebt extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'sender_id',
        'hub_id',
        'order_id',
        'amount',
        'type',
        'status',
        'note',
        'paid_at',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'float',
        'paid_at' => 'datetime',
    ];

    // ========== Relationships ==========
    
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function hub(): BelongsTo
    {
        return $this->belongsTo(User::class, 'hub_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ========== Static Methods ==========
    
    /**
     * Lấy tổng nợ chưa thanh toán của Sender với Hub
     */
    public static function getTotalUnpaidDebt(int $senderId, int $hubId): float
    {
        return self::where('sender_id', $senderId)
            ->where('hub_id', $hubId)
            ->where('type', 'debt')
            ->where('status', 'unpaid')
            ->sum('amount');
    }

    /**
     * Tạo nợ mới cho Sender
     */
    public static function createDebt(int $senderId, int $hubId, float $amount, ?int $orderId = null, ?string $note = null): self
    {
        return self::create([
            'sender_id' => $senderId,
            'hub_id' => $hubId,
            'order_id' => $orderId,
            'amount' => $amount,
            'type' => 'debt',
            'status' => 'unpaid',
            'note' => $note,
            'created_by' => auth()->id(),
        ]);
    }

    /**
     * Ghi nhận việc trừ nợ (khi có đơn mới)
     */
    public static function recordDeduction(int $senderId, int $hubId, int $orderId, float $amount): void
    {
        // 1. Tạo record deduction
        self::create([
            'sender_id' => $senderId,
            'hub_id' => $hubId,
            'order_id' => $orderId,
            'amount' => $amount,
            'type' => 'deduction',
            'status' => 'paid',
            'note' => "Trừ nợ từ đơn #{$orderId}",
            'paid_at' => now(),
            'created_by' => auth()->id(),
        ]);

        // 2. Trừ dần từ các khoản nợ cũ nhất (FIFO)
        $remainingAmount = $amount;
        $debts = self::where('sender_id', $senderId)
            ->where('hub_id', $hubId)
            ->where('type', 'debt')
            ->where('status', 'unpaid')
            ->orderBy('created_at', 'asc')
            ->get();

        foreach ($debts as $debt) {
            if ($remainingAmount <= 0) {
                break;
            }

            if ($debt->amount <= $remainingAmount) {
                // Trả hết khoản nợ này
                $debt->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                ]);
                $remainingAmount -= $debt->amount;
            } else {
                // Trả một phần, tạo debt mới cho phần còn lại
                $paidAmount = $remainingAmount;
                $remainingDebt = $debt->amount - $paidAmount;

                // Đánh dấu nợ cũ là đã trả
                $debt->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                    'note' => ($debt->note ?? '') . " | Trả một phần: " . number_format($paidAmount),
                ]);

                // Tạo nợ mới cho phần còn lại
                self::createDebt(
                    $senderId,
                    $hubId,
                    $remainingDebt,
                    $debt->order_id,
                    "Nợ còn lại từ đơn #{$debt->order_id}"
                );

                $remainingAmount = 0;
            }
        }
    }

    /**
     * Lấy lịch sử nợ của Sender với Hub
     */
    public static function getDebtHistory(int $senderId, int $hubId, int $limit = 50)
    {
        return self::where('sender_id', $senderId)
            ->where('hub_id', $hubId)
            ->with(['order', 'creator'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Xóa nợ (admin only)
     */
    public function cancelDebt(?string $reason = null): bool
    {
        return $this->update([
            'status' => 'cancelled',
            'note' => ($this->note ?? '') . " | Hủy: " . ($reason ?? 'Admin hủy'),
        ]);
    }

    // ========== Scopes ==========
    
    public function scopeUnpaid($query)
    {
        return $query->where('status', 'unpaid');
    }

    public function scopeDebts($query)
    {
        return $query->where('type', 'debt');
    }

    public function scopeDeductions($query)
    {
        return $query->where('type', 'deduction');
    }

    public function scopeForSender($query, int $senderId)
    {
        return $query->where('sender_id', $senderId);
    }

    public function scopeForHub($query, int $hubId)
    {
        return $query->where('hub_id', $hubId);
    }
}

