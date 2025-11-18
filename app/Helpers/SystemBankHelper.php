<?php

namespace App\Helpers;

use App\Models\BankAccount;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SystemBankHelper
{
    /**
     * ✅ Lấy System Bank Account (với cache 1 giờ)
     * 
     * @return BankAccount|null
     */
    public static function getAccount()
    {
        return Cache::remember('system_bank_account', 3600, function () {
            $strategy = config('system.bank_account_strategy', 'database');
            
            if ($strategy === 'database') {
                return self::getFromDatabase();
            }
            
            return self::getFromConfig();
        });
    }

    /**
     * ✅ Lấy từ Database
     */
    protected static function getFromDatabase()
    {
        try {
            // Thử 1: Lấy theo role admin/system
            $systemUser = User::whereIn('role', config('system.system_user_roles', ['admin', 'system']))
                ->where('status', 'active')
                ->first();
            
            if ($systemUser) {
                $account = BankAccount::where('user_id', $systemUser->id)
                    ->where('is_primary', true)
                    ->where('is_active', true)
                    ->verified()
                    ->first();
                
                if ($account) {
                    Log::info('System bank account found by role', ['user_id' => $systemUser->id]);
                    return $account;
                }
            }
            
            // Thử 2: Lấy theo email cụ thể
            $systemEmail = config('system.system_user_email', 'admin@delivery.com');
            $systemUser = User::where('email', $systemEmail)
                ->where('status', 'active')
                ->first();
            
            if ($systemUser) {
                $account = BankAccount::where('user_id', $systemUser->id)
                    ->where('is_primary', true)
                    ->where('is_active', true)
                    ->verified()
                    ->first();
                
                if ($account) {
                    Log::info('System bank account found by email', ['email' => $systemEmail]);
                    return $account;
                }
            }
            
            // Thử 3: Lấy theo user_id cố định
            $systemUserId = config('system.system_user_id', 1);
            $account = BankAccount::where('user_id', $systemUserId)
                ->where('is_primary', true)
                ->where('is_active', true)
                ->verified()
                ->first();
            
            if ($account) {
                Log::info('System bank account found by user_id', ['user_id' => $systemUserId]);
                return $account;
            }
            
            Log::warning('System bank account not found in database');
            return null;
            
        } catch (\Exception $e) {
            Log::error('Error getting system bank account from database', [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * ✅ Lấy từ Config (Backup)
     */
    protected static function getFromConfig()
    {
        Log::info('Using system bank account from config (backup mode)');
        
        // Tạo object giả có cấu trúc giống BankAccount
        return (object) [
            'bank_code' => config('system.bank_code'),
            'bank_name' => config('system.bank_name'),
            'bank_short_name' => config('system.bank_short_name'),
            'account_number' => config('system.bank_account'),
            'account_name' => config('system.bank_account_name'),
            'generateQrCode' => function($amount = 0, $description = '') {
                $bank = config('system.bank_code');
                $account = config('system.bank_account');
                $url = "https://img.vietqr.io/image/{$bank}-{$account}-compact2.jpg";
                
                $params = [];
                if ($amount > 0) $params['amount'] = (int) $amount;
                if ($description) $params['addInfo'] = $description;
                
                if (!empty($params)) {
                    $url .= '?' . http_build_query($params);
                }
                
                return $url;
            }
        ];
    }

    /**
     * ✅ Xóa cache
     */
    public static function clearCache()
    {
        Cache::forget('system_bank_account');
        Log::info('System bank account cache cleared');
    }

    /**
     * ✅ Kiểm tra system bank account có hợp lệ không
     */
    public static function isValid()
    {
        $account = self::getAccount();
        return !is_null($account);
    }

    /**
     * ✅ Lấy thông tin hiển thị
     */
    public static function getDisplayInfo()
    {
        $account = self::getAccount();
        
        if (!$account) {
            return [
                'error' => 'Không tìm thấy tài khoản hệ thống'
            ];
        }
        
        return [
            'bank_code' => $account->bank_code,
            'bank_name' => $account->bank_name,
            'bank_short_name' => $account->bank_short_name ?? $account->bank_name,
            'account_number' => $account->account_number,
            'account_name' => $account->account_name,
        ];
    }

    /**
     * ✅ Tạo QR code
     */
    public static function generateQrCode($amount = 0, $description = '')
    {
        $account = self::getAccount();
        
        if (!$account) {
            throw new \Exception('Không tìm thấy tài khoản ngân hàng hệ thống');
        }
        
        // Nếu là object từ database
        if ($account instanceof BankAccount) {
            return $account->generateQrCode($amount, $description);
        }
        
        // Nếu là object từ config
        if (is_callable($account->generateQrCode)) {
            return call_user_func($account->generateQrCode, $amount, $description);
        }
        
        throw new \Exception('Không thể tạo QR code');
    }

    /**
     * ✅ Kiểm tra và cảnh báo nếu thiếu config
     */
    public static function validateSetup()
    {
        $issues = [];
        
        // Check database
        try {
            $account = self::getFromDatabase();
            if (!$account) {
                $issues[] = 'Không tìm thấy tài khoản ngân hàng hệ thống trong database';
            }
        } catch (\Exception $e) {
            $issues[] = 'Lỗi khi truy vấn database: ' . $e->getMessage();
        }
        
        // Check config backup
        if (!config('system.bank_code') || !config('system.bank_account')) {
            $issues[] = 'Thiếu thông tin backup trong config/system.php hoặc .env';
        }
        
        return [
            'valid' => empty($issues),
            'issues' => $issues,
            'recommendations' => empty($issues) ? [] : [
                'Tạo user với role admin/system',
                'Thêm bank account primary cho user đó',
                'Verify bank account',
                'Hoặc cấu hình backup trong .env'
            ]
        ];
    }
}