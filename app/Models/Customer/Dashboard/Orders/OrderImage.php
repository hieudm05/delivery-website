<?php

namespace App\Models\Customer\Dashboard\Orders;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderImage extends Model
{
    use HasFactory;

    protected $table = 'order_images';

    protected $fillable = [
        'order_id',
        'image_path',
        'type',   // loại ảnh: pickup, before_ship, etc.
        'note',   // ghi chú ảnh
    ];

    /**
     * Mỗi ảnh thuộc về một đơn hàng
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
