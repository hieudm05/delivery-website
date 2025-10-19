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
        'user_id',
        'full_name',
        'email',
        'phone',
        'province_code',
        'post_office_id',     
        'post_office_name',   
        'post_office_address',
        'post_office_lat',    
        'post_office_lng',    
        'post_office_phone',  
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
