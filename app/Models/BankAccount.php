<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class BankAccount extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'bank_code',
        'bank_name',
        'bank_short_name',
        'bank_logo',
        'account_number',
        'account_name',
        'is_primary',
        'is_active',
        'verified_at',
        'verified_by',
        'verification_code',
        'qr_code_url',
        'note',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'is_active' => 'boolean',
        'verified_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // ================= RELATIONS =================

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function codTransactions()
    {
        return $this->hasMany(CodTransaction::class, 'bank_account_id');
    }

    // ================= SCOPES =================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    public function scopeVerified($query)
    {
        return $query->whereNotNull('verified_at');
    }

    public function scopeUnverified($query)
    {
        return $query->whereNull('verified_at');
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByBank($query, $bankCode)
    {
        return $query->where('bank_code', $bankCode);
    }

    // ================= METHODS =================

    /**
     * Xác thực tài khoản ngân hàng
     */
    public function verify($adminId, $code = null)
    {
        if ($this->isVerified()) {
            return false;
        }

        if ($code && $this->verification_code !== $code) {
            return false;
        }

        $this->update([
            'verified_at' => now(),
            'verified_by' => $adminId,
            'verification_code' => null,
        ]);

        return true;
    }

    /**
     * Kiểm tra tài khoản đã xác thực
     */
    public function isVerified()
    {
        return !is_null($this->verified_at);
    }

    /**
     * Đặt làm tài khoản chính
     */
    public function makePrimary()
    {
        self::where('user_id', $this->user_id)
            ->where('id', '!=', $this->id)
            ->update(['is_primary' => false]);

        $this->update(['is_primary' => true]);

        return true;
    }

    /**
     * Kích hoạt tài khoản
     */
    public function activate()
    {
        return $this->update(['is_active' => true]);
    }

    /**
     * Vô hiệu hóa tài khoản
     */
    public function deactivate()
    {
        if ($this->is_primary) {
            return false;
        }

        return $this->update(['is_active' => false]);
    }

    /**
     * Sinh mã xác thực 6 số
     */
    public function generateVerificationCode()
    {
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $this->verification_code = $code;
        return $code;
    }

    /**
     * Sinh QR Code từ VietQR API
     * @param int $amount Số tiền (0 = không hiển thị số tiền)
     * @param string $description Nội dung chuyển khoản
     * @return string URL của QR code
     */
    public function generateQrCode($amount = 0, $description = '')
    {
        try {
            $bank = $this->bank_code;
            $account = $this->account_number;

            $url = "https://img.vietqr.io/image/{$bank}-{$account}-compact2.jpg";

            $params = [];

            if ($amount > 0) {
                $params['amount'] = (int) $amount;
            }

            if ($description) {
                $params['addInfo'] = $description;
            }

            if (!empty($params)) {
                $url .= '?' . http_build_query($params);
            }

            $this->qr_code_url = $url;

            return $url;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Lấy tài khoản chính của user
     */
    public static function getPrimaryAccount($userId)
    {
        return self::where('user_id', $userId)
            ->where('is_primary', true)
            ->where('is_active', true)
            ->verified()
            ->first();
    }

    /**
     * Format số tài khoản (ẩn giữa, giữ 4 số đầu và 4 số cuối)
     */
    public function getMaskedAccountNumber()
    {
        $account = $this->account_number;
        $length = strlen($account);

        if ($length <= 8) {
            return str_repeat('*', $length - 4) . substr($account, -4);
        }

        return substr($account, 0, 4) . str_repeat('*', $length - 8) . substr($account, -4);
    }

    /**
     * Lấy thông tin hiển thị cho frontend
     */
    public function getDisplayInfo()
    {
        return [
            'id' => $this->id,
            'bank_code' => $this->bank_code,
            'bank_name' => $this->bank_name,
            'bank_short_name' => $this->bank_short_name ?? $this->bank_name,
            'bank_logo' => $this->bank_logo,
            'account_name' => $this->account_name,
            'account_number' => $this->account_number,
            'account_number_masked' => $this->getMaskedAccountNumber(),
            'is_primary' => $this->is_primary,
            'is_active' => $this->is_active,
            'is_verified' => $this->isVerified(),
            'verified_at' => $this->verified_at?->format('d/m/Y H:i'),
            'qr_code_url' => $this->qr_code_url,
            'note' => $this->note,
        ];
    }
}