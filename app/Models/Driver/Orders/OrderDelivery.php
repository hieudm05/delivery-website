<?php

namespace App\Models\Driver\Orders;

use App\Models\Customer\Dashboard\Orders\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderDelivery extends Model
{
    use HasFactory;

    protected $table = 'order_deliveries';

    protected $fillable = [
        'order_id',
        'delivery_driver_id',
        'actual_delivery_start_time',
        'actual_delivery_time',
        'delivery_latitude',
        'delivery_longitude',
        'delivery_address',
        'received_by_name',
        'received_by_phone',
        'received_by_relation',
        'delivery_note',
        'cod_collected_amount',
        'cod_collected_at',
    ];

    protected $casts = [
        'actual_delivery_start_time' => 'datetime',
        'actual_delivery_time' => 'datetime',
        'cod_collected_at' => 'datetime',
        'cod_collected_amount' => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delivery_driver_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(OrderDeliveryImage::class, 'order_id', 'order_id');
    }

    public function issues(): HasMany
    {
        return $this->hasMany(OrderDeliveryIssue::class, 'order_id', 'order_id');
    }

    public function getIsDeliveredAttribute(): bool
    {
        return !is_null($this->actual_delivery_time);
    }

    public function getGoogleMapsLinkAttribute(): ?string
    {
        if (!$this->delivery_latitude || !$this->delivery_longitude) return null;
        return "https://www.google.com/maps?q={$this->delivery_latitude},{$this->delivery_longitude}";
    }
}
