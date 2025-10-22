<?php
namespace App\Models\Customer\Dashboard\Orders;

use App\Models\Driver\Orders\OrderDeliveryImage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_id',
        'sender_name',
        'sender_phone',
        'sender_address',
        'sender_latitude',
        'sender_longitude',
        'post_office_id',
        'pickup_time',
        'recipient_name',
        'recipient_phone',
        'province_code',
        'district_code',
        'ward_code',
        'address_detail',
        'recipient_latitude',
        'recipient_longitude',
        'recipient_full_address',
        'delivery_time',
        'item_type',
        'services',
        'cod_amount',
        'note',
        'products_json',
        'save_address',
        'status',
        'driver_id',
        'actual_pickup_start_time',
        'actual_pickup_time',
        'actual_packages',
        'actual_weight',
        'pickup_note',
        'pickup_latitude',
        'pickup_longitude',
        'pickup_issue_type',
        'pickup_issue_note',
        'pickup_issue_time',
        'pickup_issue_driver_id',
        'current_hub_id',
        'hub_transfer_time',
        'hub_transfer_note',
    ];


   protected $casts = [
    'services' => 'array',
    'products_json' => 'array',
    'pickup_time' => 'datetime',
    'delivery_time' => 'datetime',
    'actual_pickup_start_time' => 'datetime',
    'actual_pickup_time' => 'datetime',
    'pickup_issue_time' => 'datetime',
    'hub_transfer_time' => 'datetime',
    'created_at' => 'datetime',
    'updated_at' => 'datetime',
    ];


    public function products()
    {
        return $this->hasMany(OrderProduct::class);
    }
    public function pickupImages()
    {
        return $this->hasMany(OrderImage::class);
    }

    public function deliveryImages()
    {
        return $this->hasMany(OrderDeliveryImage::class);
    }
       public function images()
    {
        return $this->hasMany(OrderImage::class);
    }
    /**
     * ✅ Scope để filter theo status
     */
    public function scopeWithStatus($query, $status)
    {
        if ($status && $status !== 'all') {
            return $query->where('status', $status);
        }
        return $query;
    }

    /**
     * ✅ Scope để search
     */
    public function scopeSearch($query, $search)
    {
        if ($search) {
            return $query->where(function($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhere('recipient_name', 'like', "%{$search}%")
                  ->orWhere('recipient_phone', 'like', "%{$search}%")
                  ->orWhere('sender_name', 'like', "%{$search}%")
                  ->orWhere('sender_phone', 'like', "%{$search}%");
            });
        }
        return $query;
    }

    /**
     * ✅ Accessor để lấy tên status dễ đọc
     */
    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            'pending' => 'Chờ xác nhận',
            'confirmed' => 'Đã xác nhận',
            'picking_up' => 'Đang lấy hàng',
            'picked_up' => 'Đã lấy hàng',
            'shipping' => 'Đang giao',
            'delivered' => 'Đã giao',
            'cancelled' => 'Đã hủy',
            default => 'Không xác định'
        };
    }

    /**
     * ✅ Accessor để lấy màu badge status
     */
    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            'pending' => 'warning',
            'confirmed' => 'info',
            'picking_up' => 'primary',
            'picked_up' => 'secondary',
            'shipping' => 'primary',
            'delivered' => 'success',
            'cancelled' => 'danger',
            default => 'dark'
        };
    }

}
