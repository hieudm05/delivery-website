<?php

namespace App\Models\Customer\Dashboard\Orders;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class OrderGroup extends Model
{
    use HasFactory;

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
     * Recalculate totals from child orders
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
     * Update group status based on child orders
     */
    public function updateGroupStatus()
    {
        $orderStatuses = $this->orders()->pluck('status')->toArray();
        
        if (empty($orderStatuses)) {
            $this->status = 'cancelled';
        } elseif (in_array('picking_up', $orderStatuses)) {
            $this->status = 'picking_up';
        } elseif (all_match($orderStatuses, 'picked_up')) {
            $this->status = 'picked_up';
        } elseif (in_array('shipping', $orderStatuses)) {
            $this->status = 'in_transit';
        } elseif (all_match($orderStatuses, 'delivered')) {
            $this->status = 'completed';
        } elseif (some_match($orderStatuses, 'delivered')) {
            $this->status = 'partially_delivered';
        } elseif (all_match($orderStatuses, 'cancelled')) {
            $this->status = 'cancelled';
        } else {
            $this->status = 'confirmed';
        }
        
        $this->save();
    }

    /**
     * Scope: Filter by status
     */
    public function scopeWithStatus($query, $status)
    {
        if ($status && $status !== 'all') {
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
            'pending' => 'Chờ xác nhận',
            'confirmed' => 'Đã xác nhận',
            'picking_up' => 'Đang lấy hàng',
            'picked_up' => 'Đã lấy hàng',
            'in_transit' => 'Đang vận chuyển',
            'partially_delivered' => 'Giao một phần',
            'completed' => 'Hoàn thành',
            'cancelled' => 'Đã hủy',
            default => 'Không xác định'
        };
    }

    /**
     * Get status badge color
     */
    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            'pending' => 'warning',
            'confirmed' => 'info',
            'picking_up' => 'primary',
            'picked_up' => 'secondary',
            'in_transit' => 'primary',
            'partially_delivered' => 'info',
            'completed' => 'success',
            'cancelled' => 'danger',
            default => 'dark'
        };
    }

    /**
     * Check if this is a multi-recipient order
     */
    public function isMultiRecipient()
    {
        return $this->total_recipients > 1;
    }
}

// Helper functions
if (!function_exists('all_match')) {
    function all_match($array, $value) {
        return count($array) === count(array_filter($array, fn($v) => $v === $value));
    }
}

if (!function_exists('some_match')) {
    function some_match($array, $value) {
        return in_array($value, $array);
    }
}