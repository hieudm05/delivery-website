<?php

return [
    /*
    |--------------------------------------------------------------------------
    | System Bank Account Strategy
    |--------------------------------------------------------------------------
    |
    | Chiến lược lấy tài khoản hệ thống:
    | - 'database': Lấy từ bảng bank_accounts (default)
    | - 'config': Lấy từ .env (backup)
    |
    */
    
    'bank_account_strategy' => env('SYSTEM_BANK_STRATEGY', 'database'),

    /*
    |--------------------------------------------------------------------------
    | System Bank Account Information (Backup)
    |--------------------------------------------------------------------------
    |
    | Thông tin tài khoản ngân hàng backup (chỉ dùng khi strategy = 'config')
    | Ưu tiên lấy từ database trước
    |
    */
    
    'bank_code' => env('SYSTEM_BANK_CODE', 'VCB'),
    
    'bank_name' => env('SYSTEM_BANK_NAME', 'Ngân hàng TMCP Ngoại thương Việt Nam (Vietcombank)'),
    
    'bank_short_name' => env('SYSTEM_BANK_SHORT_NAME', 'Vietcombank'),
    
    'bank_account' => env('SYSTEM_BANK_ACCOUNT', '1034567890'),
    
    'bank_account_name' => env('SYSTEM_BANK_ACCOUNT_NAME', 'CONG TY TNHH GIAO HANG'),
    
    'bank_branch' => env('SYSTEM_BANK_BRANCH', 'Chi nhánh TP. Hồ Chí Minh'),

    /*
    |--------------------------------------------------------------------------
    | System User Identification
    |--------------------------------------------------------------------------
    |
    | Cách xác định user nào là system account
    |
    */
    
    // Email của user hệ thống (để tìm bank account)
    'system_user_email' => env('SYSTEM_USER_EMAIL', 'admin@example.com'),
    
    // Role của user hệ thống
    'system_user_roles' => ['admin', 'system'],
    
    // ID của user hệ thống (fallback)
    'system_user_id' => env('SYSTEM_USER_ID', 1),

    /*
    |--------------------------------------------------------------------------
    | System Contact Information
    |--------------------------------------------------------------------------
    |
    | Thông tin liên hệ với hệ thống khi có vấn đề về COD
    |
    */
    
    'contact_phone' => env('SYSTEM_CONTACT_PHONE', '1900xxxx'),
    
    'contact_email' => env('SYSTEM_CONTACT_EMAIL', 'finance@delivery.com'),
    
    'contact_hotline' => env('SYSTEM_CONTACT_HOTLINE', '0909123456'),

    /*
    |--------------------------------------------------------------------------
    | COD Fee Settings
    |--------------------------------------------------------------------------
    |
    | Cài đặt phí COD mà Hub phải nộp cho hệ thống
    |
    */
    
    // Phí cố định (VNĐ)
    'cod_fixed_fee' => env('COD_FIXED_FEE', 1000),
    
    // Phí % trên số tiền COD
    'cod_percentage_fee' => env('COD_PERCENTAGE_FEE', 0.01), // 1%
    
    // Phí COD tối thiểu
    'cod_min_fee' => env('COD_MIN_FEE', 1000),
    
    // Phí COD tối đa
    'cod_max_fee' => env('COD_MAX_FEE', 50000),

    /*
    |--------------------------------------------------------------------------
    | Payment Deadline Settings
    |--------------------------------------------------------------------------
    |
    | Thời hạn Hub phải nộp tiền cho hệ thống
    |
    */
    
    // Số ngày Hub phải nộp tiền sau khi xác nhận nhận từ driver
    'payment_deadline_days' => env('HUB_PAYMENT_DEADLINE_DAYS', 3),
    
    // Phí chậm nộp mỗi ngày (%)
    'late_payment_fee_percent' => env('LATE_PAYMENT_FEE_PERCENT', 0.001), // 0.1%/ngày

    /*
    |--------------------------------------------------------------------------
    | Notification Settings
    |--------------------------------------------------------------------------
    |
    | Cài đặt thông báo cho các admin hệ thống
    |
    */
    
    'admin_notification_emails' => [
        'hieudm05@gmail.com',
        'admin@delivery.com',
    ],
    
    'admin_notification_phones' => [
        '0365016573',
        '0909654321',
    ],
];