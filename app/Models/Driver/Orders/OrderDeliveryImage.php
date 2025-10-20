<?php

namespace App\Models\Driver\Orders;

use App\Models\Customer\Dashboard\Orders\Order;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDeliveryImage extends Model
{
    use HasFactory;

    protected $table = 'order_delivery_images';

    protected $fillable = [
        'order_id',
        'image_path',
        'note',      // ghi chú ảnh khi giao hàng
        'location',  // vị trí giao (vd: GPS hoặc địa chỉ)
    ];

    /**
     * Mỗi ảnh thuộc về một đơn hàng
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
