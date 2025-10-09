<?php
namespace App\Models\Customer\Dashboard\Orders;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'sender_id', 'receiver_name', 'receiver_phone', 'receiver_address',
        'province_code', 'district_code', 'ward_code',
        'weight', 'length', 'width', 'height',
        'service_type', 'cod_amount', 'total_amount', 'status'
    ];

    public function items() {
        return $this->hasMany(OrderItem::class);
    }

    public function extraServices() {
        return $this->belongsToMany(ExtraService::class, 'order_extra_service')
                    ->withPivot('amount')
                    ->withTimestamps();
    }

    public function sender() {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
