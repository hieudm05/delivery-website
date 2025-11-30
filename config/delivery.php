<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Shipping Fee Configuration
    |--------------------------------------------------------------------------
    */
    'shipping' => [
        'base_fee' => (float) env('SHIPPING_BASE_FEE', 20000), // 20,000đ
        'extra_weight_fee' => (float) env('SHIPPING_EXTRA_WEIGHT_FEE', 5), // 5đ/gram
    ],

    /*
    |--------------------------------------------------------------------------
    | Service Fees
    |--------------------------------------------------------------------------
    */
    'fees' => [
        // Giao ưu tiên (% shipping fee)
        'priority_percent' => (float) env('SERVICE_PRIORITY_FEE', 0.25), // 25%
        
        // Giao nhanh (% shipping fee)
        'fast_percent' => (float) env('SERVICE_FAST_PERCENT', 0.15), // 15%
        
        // Bảo hiểm (% giá trị hàng)
        'insurance_percent' => (float) env('SERVICE_INSURANCE_PERCENT', 0.01), // 1%
        
        // COD
        'cod_base_fee' => (float) env('SERVICE_COD_BASE_FEE', 1000), // 1,000đ
        'cod_percent' => (float) env('SERVICE_COD_PERCENT', 0.01), // 1%
    ],

    /*
    |--------------------------------------------------------------------------
    | Platform Fee
    |--------------------------------------------------------------------------
    */
    'platform' => [
        'base_fee' => (float) env('PLATFORM_BASE_FEE', 2000), // 2,000đ mỗi đơn
    ],

    /*
    |--------------------------------------------------------------------------
    | Driver Commission
    |--------------------------------------------------------------------------
    */
    'driver' => [
        'commission_rate' => (float) env('DRIVER_COMMISSION_RATE', 0.5), // 50%
        'min_commission' => (float) env('MIN_DRIVER_COMMISSION', 5000),
        'max_commission' => (float) env('MAX_DRIVER_COMMISSION', 50000),
    ],

    /*
    |--------------------------------------------------------------------------
    | Profit Sharing
    |--------------------------------------------------------------------------
    */
    'profit' => [
        'hub_share' => (float) env('HUB_PROFIT_SHARE', 0.60), // 60%
        'admin_share' => (float) env('ADMIN_PROFIT_SHARE', 0.40), // 40%
    ],

    /*
    |--------------------------------------------------------------------------
    | Debt Management
    |--------------------------------------------------------------------------
    */
    'debt' => [
        'allow_sender_debt' => env('ALLOW_SENDER_DEBT', true),
        'max_sender_debt' => (float) env('MAX_SENDER_DEBT', 10000000),
        'auto_deduct_debt' => env('AUTO_DEDUCT_DEBT', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Order Auto Approve
    |--------------------------------------------------------------------------
    */
    'order' => [
        'auto_approve' => env('ORDER_AUTO_APPROVE', false),
    ],
];