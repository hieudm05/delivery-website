<?php

namespace App\Models\Hub;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hub extends Model
{
    use HasFactory;

    protected $table = 'hubs';

    protected $fillable = [
        'post_office_id',
        'user_id',
        'hub_latitude',
        'hub_longitude',
        'hub_address',
    ];
     protected $casts = [
        'hub_latitude' => 'decimal:7',
        'hub_longitude' => 'decimal:7',
    ];

    /**
     * Check if hub has valid coordinates
     */
    public function hasCoordinates()
    {
        return !is_null($this->hub_latitude) && !is_null($this->hub_longitude);
    }

    /**
     * Relationship: Hub has many orders
     */
    public function orders()
    {
        return $this->hasMany(\App\Models\Customer\Dashboard\Orders\Order::class, 'post_office_id');
    }

    /**
     * Một hub thuộc về một user.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
