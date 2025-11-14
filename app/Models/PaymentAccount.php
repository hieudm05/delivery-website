<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PaymentAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_type',
        'owner_id',
        'bank_code',
        'bank_name',
        'account_number',
        'account_name',
        'qr_template',
        'note',
    ];

    /**
     * Quan hệ động theo owner_type:
     * - system  → null
     * - branch  → Branch model (tuỳ dự án)
     * - shop    → User model (hoặc Sender model)
     */
    public function owner()
    {
        return $this->morphTo(__FUNCTION__, 'owner_type', 'owner_id');
    }

    /**
     * Sinh URL VietQR để thanh toán.
     * - $amount: số tiền cần chuyển
     * - $info: nội dung chuyển khoản (addInfo)
     */
    public function generateQr($amount = 0, $info = '')
    {
        $bank    = $this->bank_code;
        $account = $this->account_number;

        $url = "https://img.vietqr.io/image/{$bank}-{$account}.jpg";

        $params = [];

        if ($amount > 0) {
            $params['amount'] = (int) $amount;
        }

        if ($info) {
            $params['addInfo'] = urlencode($info);
        }

        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        return $url;
    }

    /**
     * Lấy tên chủ tài khoản in HOA
     */
    public function getFormattedNameAttribute()
    {
        return strtoupper($this->account_name);
    }
}
