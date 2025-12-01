<?php

namespace App\Models\Driver\Orders;

use App\Models\Customer\Dashboard\Orders\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDelivery extends Model
{
    use HasFactory;

    protected $table = 'order_deliveries';

    protected $fillable = [
        'order_id',
        'delivery_driver_id',
        'attempt_number', 
        'actual_delivery_start_time',
        'actual_delivery_time',
        'delivery_latitude',
        'delivery_longitude',
        'delivery_address',
        'received_by_name',
        'received_by_phone',
        'received_by_relation',
        'delivery_note',
        'is_successful', 
        'cod_collected_amount',
        'cod_collected_at',
    ];

    protected $casts = [
        'actual_delivery_start_time' => 'datetime',
        'actual_delivery_time' => 'datetime',
        'cod_collected_at' => 'datetime',
        'cod_collected_amount' => 'decimal:2',
        'is_successful' => 'boolean',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'delivery_driver_id');
    }

    public function images()
    {
        return $this->hasMany(OrderDeliveryImage::class, 'order_id', 'order_id');
    }

    public function issues()
    {
        return $this->hasMany(OrderDeliveryIssue::class, 'order_id', 'order_id');
    }

    public function getIsDeliveredAttribute()
    {
        return !is_null($this->actual_delivery_time);
    }

    public function getGoogleMapsLinkAttribute()
    {
        if (!$this->delivery_latitude || !$this->delivery_longitude) return null;
        return "https://www.google.com/maps?q={$this->delivery_latitude},{$this->delivery_longitude}";
    }
    // Lấy lần thử mới nhất cho đơn hàng
      public static function getLatestAttempt($orderId)
    {
        return self::where('order_id', $orderId)
            ->orderBy('attempt_number', 'desc')
            ->first();
    }

    // Tạo lần thử mới
    public static function createNewAttempt($orderId, $driverId)
    {
        $lastAttempt = self::getLatestAttempt($orderId);
        $attemptNumber = $lastAttempt ? $lastAttempt->attempt_number + 1 : 1;

        return self::create([
            'order_id' => $orderId,
            'delivery_driver_id' => $driverId,
            'attempt_number' => $attemptNumber,
            'actual_delivery_start_time' => now(),
            'is_successful' => false,
        ]);
    }
}
