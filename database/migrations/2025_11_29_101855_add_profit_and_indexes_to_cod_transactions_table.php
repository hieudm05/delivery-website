<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cod_transactions', function (Blueprint $table) {
            // Thêm cột hub_profit và admin_profit
            $table->decimal('hub_profit', 15, 2)->default(0)->after('driver_commission');
            $table->decimal('admin_profit', 15, 2)->default(0)->after('hub_profit');
            
            // Update sender_payment_status có thêm giá trị 'not_applicable'
            // (cho trường hợp không có tiền trả sender)
            
            // Có thể thêm index để tìm kiếm nhanh hơn
            $table->index(['sender_id', 'hub_id'], 'idx_sender_hub');
            $table->index('shipper_payment_status');
            $table->index('sender_payment_status');
            $table->index('hub_system_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cod_transactions', function (Blueprint $table) {
            $table->dropColumn(['hub_profit', 'admin_profit']);
            $table->dropIndex('idx_sender_hub');
            $table->dropIndex(['shipper_payment_status']);
            $table->dropIndex(['sender_payment_status']);
            $table->dropIndex(['hub_system_status']);
        });
    }
};