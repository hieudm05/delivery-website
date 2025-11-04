<?php

namespace App\Models\Customer\Dashboard\Orders;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class OrderGroup extends Model
{
    use HasFactory;

    // ✅ Định nghĩa các trạng thái của OrderGroup
    public const STATUS_PENDING = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_PICKING_UP = 'picking_up';
    public const STATUS_PICKED_UP = 'picked_up';
    public const STATUS_IN_TRANSIT = 'in_transit';
    public const STATUS_PARTIALLY_DELIVERED = 'partially_delivered';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_CONFIRMED,
        self::STATUS_PICKING_UP,
        self::STATUS_PICKED_UP,
        self::STATUS_IN_TRANSIT,
        self::STATUS_PARTIALLY_DELIVERED,
        self::STATUS_COMPLETED,
        self::STATUS_CANCELLED,
    ];

    protected $fillable = [
        'user_id',
        'sender_name',
        'sender_phone',
        'sender_address',
        'sender_latitude',
        'sender_longitude',
        'post_office_id',
        'pickup_time',
        'total_recipients',
        'total_shipping_fee',
        'total_cod_fee',
        'total_sender_pays',
        'total_recipient_pays',
        'status',
        'note',
    ];

    protected $casts = [
        'pickup_time' => 'datetime',
        'total_shipping_fee' => 'decimal:2',
        'total_cod_fee' => 'decimal:2',
        'total_sender_pays' => 'decimal:2',
        'total_recipient_pays' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationship: OrderGroup belongs to User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship: OrderGroup has many Orders
     */
    public function orders()
    {
        return $this->hasMany(Order::class, 'order_group_id');
    }

    /**
     * Get all products from all orders in this group
     */
    public function allProducts()
    {
        return $this->hasManyThrough(
            OrderProduct::class,
            Order::class,
            'order_group_id', // FK on orders table
            'order_id',       // FK on order_products table
            'id',             // PK on order_groups table
            'id'              // PK on orders table
        );
    }

    /**
     * Get all images from all orders in this group
     */
    public function allImages()
    {
        return $this->hasManyThrough(
            OrderImage::class,
            Order::class,
            'order_group_id',
            'order_id',
            'id',
            'id'
        );
    }

    /**
     * ✅ Recalculate totals from child orders
     */
    public function recalculateTotals()
    {
        $this->total_recipients = $this->orders()->count();
        $this->total_shipping_fee = $this->orders()->sum('shipping_fee');
        $this->total_cod_fee = $this->orders()->sum('cod_fee');
        $this->total_sender_pays = $this->orders()->sum('sender_total');
        $this->total_recipient_pays = $this->orders()->sum('recipient_total');
        $this->save();
    }

    /**
     * ✅ Update group status based on child orders - CẢI TIẾN
     */
    public function updateGroupStatus()
    {
        $orderStatuses = $this->orders()->pluck('status')->toArray();
        
        if (empty($orderStatuses)) {
            $this->status = self::STATUS_CANCELLED;
        } elseif ($this->allMatch($orderStatuses, Order::STATUS_CANCELLED)) {
            // Tất cả đơn đều bị hủy
            $this->status = self::STATUS_CANCELLED;
        } elseif ($this->allMatch($orderStatuses, Order::STATUS_DELIVERED)) {
            // Tất cả đơn đều đã giao
            $this->status = self::STATUS_COMPLETED;
        } elseif ($this->someMatch($orderStatuses, Order::STATUS_DELIVERED)) {
            // Một số đơn đã giao
            $this->status = self::STATUS_PARTIALLY_DELIVERED;
        } elseif ($this->someMatch($orderStatuses, Order::STATUS_SHIPPING) || 
                  $this->someMatch($orderStatuses, Order::STATUS_AT_HUB)) {
            // Có đơn đang giao hoặc tại hub
            $this->status = self::STATUS_IN_TRANSIT;
        } elseif ($this->someMatch($orderStatuses, Order::STATUS_PICKING_UP)) {
            // Có đơn đang lấy hàng
            $this->status = self::STATUS_PICKING_UP;
        } elseif ($this->allMatch($orderStatuses, Order::STATUS_PICKED_UP)) {
            // Tất cả đơn đã lấy hàng
            $this->status = self::STATUS_PICKED_UP;
        } else {
            // Mặc định: confirmed
            $this->status = self::STATUS_CONFIRMED;
        }
        
        $this->save();
    }

    /**
     * ✅ Check if all items in array match a value
     */
    private function allMatch($array, $value)
    {
        return count($array) === count(array_filter($array, fn($v) => $v === $value));
    }

    /**
     * ✅ Check if some items in array match a value
     */
    private function someMatch($array, $value)
    {
        return in_array($value, $array);
    }

    /**
     * Scope: Filter by status
     */
    public function scopeWithStatus($query, $status)
    {
        if ($status && $status !== 'all' && in_array($status, self::STATUSES)) {
            return $query->where('status', $status);
        }
        return $query;
    }

    /**
     * Scope: Search
     */
    public function scopeSearch($query, $search)
    {
        if ($search) {
            return $query->where(function($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhere('sender_name', 'like', "%{$search}%")
                  ->orWhere('sender_phone', 'like', "%{$search}%")
                  ->orWhereHas('orders', function($q2) use ($search) {
                      $q2->where('recipient_name', 'like', "%{$search}%")
                         ->orWhere('recipient_phone', 'like', "%{$search}%");
                  });
            });
        }
        return $query;
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            self::STATUS_PENDING => 'Chờ xác nhận',
            self::STATUS_CONFIRMED => 'Đã xác nhận',
            self::STATUS_PICKING_UP => 'Đang lấy hàng',
            self::STATUS_PICKED_UP => 'Đã lấy hàng',
            self::STATUS_IN_TRANSIT => 'Đang vận chuyển',
            self::STATUS_PARTIALLY_DELIVERED => 'Giao một phần',
            self::STATUS_COMPLETED => 'Hoàn thành',
            self::STATUS_CANCELLED => 'Đã hủy',
            default => 'Không xác định'
        };
    }

    /**
     * Get status badge color
     */
    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            self::STATUS_PENDING => 'warning',
            self::STATUS_CONFIRMED => 'info',
            self::STATUS_PICKING_UP => 'primary',
            self::STATUS_PICKED_UP => 'secondary',
            self::STATUS_IN_TRANSIT => 'primary',
            self::STATUS_PARTIALLY_DELIVERED => 'info',
            self::STATUS_COMPLETED => 'success',
            self::STATUS_CANCELLED => 'danger',
            default => 'secondary'
        };
    }

    /**
     * Get status icon
     */
    public function getStatusIconAttribute()
    {
        return match($this->status) {
            self::STATUS_PENDING => 'clock-history',
            self::STATUS_CONFIRMED => 'check-circle',
            self::STATUS_PICKING_UP => 'box-arrow-up',
            self::STATUS_PICKED_UP => 'box-seam',
            self::STATUS_IN_TRANSIT => 'truck',
            self::STATUS_PARTIALLY_DELIVERED => 'pie-chart',
            self::STATUS_COMPLETED => 'check-circle-fill',
            self::STATUS_CANCELLED => 'x-circle',
            default => 'question-circle'
        };
    }

    /**
     * Check if this is a multi-recipient order
     */
    public function isMultiRecipient()
    {
        return $this->total_recipients > 1;
    }

    /**
     * ✅ Get completion percentage
     */
    public function getCompletionPercentageAttribute()
    {
        $total = $this->orders()->count();
        if ($total === 0) return 0;
        
        $delivered = $this->orders()->where('status', Order::STATUS_DELIVERED)->count();
        return round(($delivered / $total) * 100);
    }

    /**
     * ✅ Check if group can be cancelled
     */
    public function canCancel()
    {
        return !in_array($this->status, [
            self::STATUS_COMPLETED,
            self::STATUS_CANCELLED,
            self::STATUS_PARTIALLY_DELIVERED
        ]);
    }
}