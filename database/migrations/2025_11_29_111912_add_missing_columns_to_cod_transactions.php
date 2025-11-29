<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Thêm cột cod_fee
        if (!$this->columnExists('cod_fee')) {
            DB::statement("ALTER TABLE `cod_transactions` ADD COLUMN `cod_fee` DECIMAL(15,2) DEFAULT 0 AFTER `platform_fee`");
        }
        
        // Thêm cột hub_profit
        if (!$this->columnExists('hub_profit')) {
            DB::statement("ALTER TABLE `cod_transactions` ADD COLUMN `hub_profit` DECIMAL(15,2) DEFAULT 0 AFTER `driver_commission`");
        }
        
        // Thêm cột admin_profit
        if (!$this->columnExists('admin_profit')) {
            DB::statement("ALTER TABLE `cod_transactions` ADD COLUMN `admin_profit` DECIMAL(15,2) DEFAULT 0 AFTER `hub_profit`");
        }
        
        // Thêm indexes
        $this->addIndexIfNotExists('idx_sender_hub', ['sender_id', 'hub_id']);
        $this->addIndexIfNotExists('idx_shipper_status', ['shipper_payment_status']);
        $this->addIndexIfNotExists('idx_sender_status', ['sender_payment_status']);
        $this->addIndexIfNotExists('idx_system_status', ['hub_system_status']);
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `cod_transactions` DROP COLUMN IF EXISTS `cod_fee`");
        DB::statement("ALTER TABLE `cod_transactions` DROP COLUMN IF EXISTS `hub_profit`");
        DB::statement("ALTER TABLE `cod_transactions` DROP COLUMN IF EXISTS `admin_profit`");
        
        DB::statement("ALTER TABLE `cod_transactions` DROP INDEX IF EXISTS `idx_sender_hub`");
        DB::statement("ALTER TABLE `cod_transactions` DROP INDEX IF EXISTS `idx_shipper_status`");
        DB::statement("ALTER TABLE `cod_transactions` DROP INDEX IF EXISTS `idx_sender_status`");
        DB::statement("ALTER TABLE `cod_transactions` DROP INDEX IF EXISTS `idx_system_status`");
    }
    
    private function columnExists(string $column): bool
    {
        $result = DB::select("SHOW COLUMNS FROM `cod_transactions` LIKE '$column'");
        return !empty($result);
    }
    
    private function addIndexIfNotExists(string $indexName, array $columns): void
    {
        $exists = DB::select("SHOW INDEX FROM `cod_transactions` WHERE Key_name = '$indexName'");
        
        if (empty($exists)) {
            $columnsStr = '`' . implode('`, `', $columns) . '`';
            DB::statement("ALTER TABLE `cod_transactions` ADD INDEX `$indexName` ($columnsStr)");
        }
    }
};