<?php
namespace App\Models\Customer\Dashboard\Accounts;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserInfo extends Model
{
    use HasFactory;
    protected $table = 'user_info';
    protected $primaryKey = 'id';
    protected $fillable = [
        'user_id',
        'national_id',
        'tax_code',
        'date_of_birth',
        'full_address',
        'address_detail',
        'latitude',
        'longitude',
        'province_code',
        'district_code',
        'ward_code',
    ];
    // Quan hệ với User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}