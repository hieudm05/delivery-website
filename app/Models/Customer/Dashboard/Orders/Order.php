<?php
namespace App\Models\Customer\Dashboard\Orders;

use App\Models\Driver\Orders\OrderDeliveryImage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_id', 'sender_name', 'sender_phone', 'sender_address',
        'sender_latitude', 'sender_longitude', 'post_office_id', 'pickup_time',
        'recipient_name', 'recipient_phone', 'province_code', 'district_code',
        'ward_code', 'address_detail', 'recipient_latitude', 'recipient_longitude',
        'recipient_full_address', 'delivery_time', 'item_type', 'services',
        'cod_amount', 'note', 'products_json', 'save_address', 'status'
    ];

    protected $casts = [
        'services' => 'array',
        'products_json' => 'array',
        'save_address' => 'boolean',
        'pickup_time' => 'datetime',
        'delivery_time' => 'datetime',
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

}
