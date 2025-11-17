<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Cách 1: Dùng raw SQL (hoạt động tốt nhất với ENUM)
        DB::statement("ALTER TABLE `cod_transactions` 
            MODIFY COLUMN `sender_payment_status` 
            ENUM('not_ready', 'pending', 'completed') 
            DEFAULT 'not_ready'");
        
        DB::statement("ALTER TABLE `cod_transactions` 
            MODIFY COLUMN `shipper_payment_status` 
            ENUM('pending', 'transferred', 'confirmed', 'disputed') 
            DEFAULT 'pending'");
        
        DB::statement("ALTER TABLE `cod_transactions` 
            MODIFY COLUMN `hub_system_status` 
            ENUM('not_ready', 'pending', 'transferred', 'confirmed') 
            DEFAULT 'not_ready'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `cod_transactions` 
            MODIFY COLUMN `sender_payment_status` 
            ENUM('pending', 'completed') 
            DEFAULT 'pending'");
    }
};