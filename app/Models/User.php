<?php

namespace App\Models;

use App\Http\Middleware\RoleMiddleware;
use App\Models\Customer\Dashboard\Accounts\UserInfo;
use App\Models\Driver\DriverProfile;
use App\Traits\UserIncome; // ✅ IMPORT TRAIT
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;
    use UserIncome; // ✅ SỬ DỤNG TRAIT THU NHẬP

    /**
     * Các cột có thể gán giá trị hàng loạt (mass assignment).
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'phone',
        'password_hash',
        'full_name',
        'role',
        'status',
        'avatar_url',
        'last_login_at',
        'last_seen_at'
    ];

    /**
     * Các cột không được hiển thị khi serialize (ví dụ trả về JSON).
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password_hash',
    ];

    /**
     * Các cột sẽ được cast tự động.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
        'last_login_at' => 'datetime',
        'last_seen_at' => 'datetime',
    ];
    
    /**
     * Ghi đè lại tên trường password (Laravel mặc định là "password").
     * Vì DB đang dùng "password_hash".
     */
    public function getAuthPassword()
    {
        return $this->password_hash;
    }
    
    // ============ RELATIONSHIPS ============
    
    /**
     * Quan hệ với UserInfo
     */
    public function userInfo() 
    {
        return $this->hasOne(UserInfo::class);
    }
    
    /**
     * Driver profile
     */
    public function driverProfile()
    {
        return $this->hasOne(DriverProfile::class);
    }
    
    /**
     * Bank accounts
     */
    public function bankAccounts()
    {
        return $this->hasMany(\App\Models\BankAccount::class);
    }
    
    /**
     * Primary bank account
     */
    public function primaryBankAccount()
    {
        return $this->hasOne(\App\Models\BankAccount::class)
            ->where('is_primary', true)
            ->where('is_active', true);
    }
    
    /**
     * COD Transactions as Driver
     */
    public function driverTransactions()
    {
        return $this->hasMany(\App\Models\Customer\Dashboard\Orders\CodTransaction::class, 'driver_id');
    }
    
    /**
     * COD Transactions as Sender
     */
    public function senderTransactions()
    {
        return $this->hasMany(\App\Models\Customer\Dashboard\Orders\CodTransaction::class, 'sender_id');
    }
    
    /**
     * COD Transactions as Hub
     */
    public function hubTransactions()
    {
        return $this->hasMany(\App\Models\Customer\Dashboard\Orders\CodTransaction::class, 'hub_id');
    }
    
    /**
     * Sender Debts
     */
    public function senderDebts()
    {
        return $this->hasMany(\App\Models\SenderDebt::class, 'sender_id');
    }
    
    /**
     * Hub Debts (từ customers)
     */
    public function hubDebts()
    {
        return $this->hasMany(\App\Models\SenderDebt::class, 'hub_id');
    }
    
    /**
     * Orders as Sender
     */
    public function sentOrders()
    {
        return $this->hasMany(\App\Models\Customer\Dashboard\Orders\Order::class, 'sender_id');
    }
    
    /**
     * Orders as Driver
     */
    public function driverOrders()
    {
        return $this->hasMany(\App\Models\Customer\Dashboard\Orders\Order::class, 'driver_id');
    }
    
    /**
     * Order Returns as Driver
     */
    public function driverReturns()
    {
        return $this->hasMany(\App\Models\Driver\Orders\OrderReturn::class, 'return_driver_id');
    }
    
    // ============ STATUS HELPERS ============
    
    /**
     * Kiểm tra user có online không
     */
    public function isOnline()
    {
        return $this->last_seen_at && $this->last_seen_at->gt(now()->subSeconds(90));
    }
    
    /**
     * Kiểm tra user có active không
     */
    public function isActive()
    {
        return $this->status === 'active';
    }
    
    /**
     * Kiểm tra role
     */
    public function isDriver()
    {
        return $this->role === 'driver';
    }
    
    public function isCustomer()
    {
        return $this->role === 'customer';
    }
    
    public function isHub()
    {
        return $this->role === 'hub';
    }
    
    public function isAdmin()
    {
        return $this->role === 'admin';
    }
    
    /**
     * ✅ HELPER: Get formatted full name with role
     */
    public function getDisplayNameAttribute()
    {
        $roleLabels = [
            'driver' => 'Tài xế',
            'customer' => 'Khách hàng',
            'hub' => 'Bưu cục',
            'admin' => 'Quản trị viên',
        ];
        
        $roleLabel = $roleLabels[$this->role] ?? '';
        return $this->full_name . ($roleLabel ? " ({$roleLabel})" : '');
    }
    
    /**
     * ✅ HELPER: Get avatar URL với fallback
     */
    public function getAvatarAttribute()
    {
        if ($this->avatar_url) {
            return asset('storage/' . $this->avatar_url);
        }
        
        // Default avatar theo role
        return match($this->role) {
            'driver' => asset('images/avatars/driver-default.png'),
            'hub' => asset('images/avatars/hub-default.png'),
            'admin' => asset('images/avatars/admin-default.png'),
            default => asset('images/avatars/customer-default.png'),
        };
    }
}