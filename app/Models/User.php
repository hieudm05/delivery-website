<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

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
    ];

    /**
     * Ghi đè lại tên trường password (Laravel mặc định là "password").
     * Vì DB đang dùng "password_hash".
     */
    public function getAuthPassword()
    {
        return $this->password_hash;
    }
}
