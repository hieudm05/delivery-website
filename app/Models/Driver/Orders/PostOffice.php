<?php

namespace App\Models\Driver\Orders;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostOffice extends Model
{
    use HasFactory;

    protected $table = 'post_offices';

    protected $fillable = [
        'code',
        'name',
        'phone',
        'address',
        'province_code',
        'district_code',
        'ward_code',
        'lat',
        'lng',
        'meta',
    ];

    protected $casts = [
        'lat' => 'decimal:7',
        'lng' => 'decimal:7',
        'meta' => 'array',
    ];

    // Quan hệ (nếu cần)
    public function orders()
    {
        return $this->hasMany(\App\Models\Customer\Dashboard\Orders\Order::class, 'post_office_id');
    }
}
