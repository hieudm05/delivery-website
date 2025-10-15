<?php

namespace App\Models\Driver;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverProfile extends Model
{
    use HasFactory;
    protected $table = "driver_profiles";

    protected $fillable = [
        'full_name',
        'user_id',
        'email',
        'vehicle_type',
        'license_number',
        'license_image',
        'identity_image',
        'experience',
        'status',
        'approved_at',
    ];
    protected $casts = [
    'created_at' => 'datetime',
    'updated_at' => 'datetime',
    'approved_at' => 'datetime',
];


    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
