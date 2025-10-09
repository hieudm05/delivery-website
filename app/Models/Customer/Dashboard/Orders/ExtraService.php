<?php
namespace App\Models\Customer\Dashboard\Orders;

use Illuminate\Database\Eloquent\Model;

class ExtraService extends Model
{
    protected $fillable = ['name', 'fee_type', 'fee_value', 'description'];
}
