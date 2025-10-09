<?php
namespace App\Models\Customer\Dashboard\Orders;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = ['order_id', 'name', 'quantity', 'value', 'special_type'];

    protected $casts = [
        'special_type' => 'array',
    ];
}
