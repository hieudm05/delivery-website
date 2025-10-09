<?php
namespace App\Models\Customer\Dashboard\Orders;

use Illuminate\Database\Eloquent\Model;

class OrderExtraService extends Model
{
    protected $table = 'order_extra_service';

    protected $fillable = ['order_id', 'extra_service_id', 'amount'];
}
