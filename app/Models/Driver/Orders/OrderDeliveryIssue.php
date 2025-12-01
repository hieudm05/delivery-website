<?php

namespace App\Models\Driver\Orders;

use App\Models\Customer\Dashboard\Orders\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderDeliveryIssue extends Model
{
    use HasFactory;

    protected $table = 'order_delivery_issues';

    protected $fillable = [
        'order_id',
        'issue_type',
        'issue_note',
        'issue_time',
        'reported_by',
        'issue_latitude',
        'issue_longitude',
        'resolution_action',
        'resolved_by',
        'resolved_at',
        'resolution_note',
        'order_return_id',
    ];
    public const ACTION_RETRY = 'retry';
    public const ACTION_RETURN = 'return';
    public const ACTION_HOLD = 'hold_at_hub';
    public const ACTION_PENDING = 'pending';

    protected $casts = [
        'issue_time' => 'datetime',
        'issue_latitude' => 'decimal:7',
        'issue_longitude' => 'decimal:7',
        'resolved_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function getGoogleMapsLinkAttribute(): ?string
    {
        if (!$this->issue_latitude || !$this->issue_longitude)
            return null;
        return "https://www.google.com/maps?q={$this->issue_latitude},{$this->issue_longitude}";
    }
    public function orderReturn()
    {
        return $this->belongsTo(\App\Models\Driver\Orders\OrderReturn::class, 'order_return_id');
    }

    public function resolver()
    {
        return $this->belongsTo(\App\Models\User::class, 'resolved_by');
    }

    /**
     * ✅ CẬP NHẬT PHƯƠNG THỨC RESOLVE
     */
    public function resolve($action, $resolvedBy, $note = null)
    {
        if (!in_array($action, [self::ACTION_RETRY, self::ACTION_RETURN, self::ACTION_HOLD])) {
            throw new \InvalidArgumentException("Invalid resolution action: {$action}");
        }

        try {
            $this->update([
                'resolution_action' => $action,
                'resolved_by' => $resolvedBy,
                'resolved_at' => now(),
                'resolution_note' => $note,
            ]);

            $order = $this->order;

            switch ($action) {
                case self::ACTION_RETRY:
                    // ✅ Kiểm tra số lần thử
                    $attemptCount = $order->deliveryAttempts()->count();
                    
                    if ($attemptCount >= 3) {
                        // Đã thất bại 3 lần → tự động chuyển sang hoàn hàng
                        $orderReturn = \App\Models\Driver\Orders\OrderReturn::createFromOrder(
                            $order,
                            \App\Models\Driver\Orders\OrderReturn::REASON_AUTO_FAILED,
                            "Tự động hoàn hàng do thất bại {$attemptCount} lần",
                            $resolvedBy
                        );
                        
                        $this->update(['order_return_id' => $orderReturn->id]);
                        
                        throw new \Exception("Đơn hàng đã thất bại {$attemptCount} lần. Hệ thống tự động chuyển sang hoàn hàng.");
                    }
                    
                    // ✅ KHÔNG cần xóa, chỉ reset status để tài xế giao lại
                    $order->update([
                        'status' => Order::STATUS_AT_HUB,
                        'delivery_attempt_count' => $attemptCount + 1
                    ]);
                    break;

                case self::ACTION_RETURN:
                    $orderReturn = \App\Models\Driver\Orders\OrderReturn::createFromOrder(
                        $order,
                        \App\Models\Driver\Orders\OrderReturn::REASON_HUB_DECISION,
                        "Hub quyết định hoàn hàng do: {$this->issue_type_label}",
                        $resolvedBy
                    );
                    $this->update(['order_return_id' => $orderReturn->id]);
                    break;

                case self::ACTION_HOLD:
                    $order->update(['status' => Order::STATUS_AT_HUB]);
                    break;
            }
            
            return true;

        } catch (\Exception $e) {
            throw $e;
        }
    }
}
