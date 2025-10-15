<?php
namespace App\Models\Customer\Dashboard\Orders;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id', 'name', 'quantity', 'weight', 'value',
        'length', 'width', 'height', 'specials'
    ];

    protected $casts = [
        'specials' => 'array',
    ];
}
