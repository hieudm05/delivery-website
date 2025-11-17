<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('cod_transactions', function (Blueprint $table) {
            // Driver commission
            $table->decimal('driver_commission', 10, 2)->default(0)->after('hub_system_amount')
                ->comment('Hoa hồng driver (% của shipping_fee)');
            
            $table->enum('driver_commission_status', ['pending', 'paid'])->default('pending')
                ->after('driver_commission')
                ->comment('Trạng thái thanh toán hoa hồng');
            
            $table->timestamp('driver_paid_at')->nullable()->after('driver_commission_status')
                ->comment('Thời gian Hub trả hoa hồng cho driver');
        });
    }

    public function down()
    {
        Schema::table('cod_transactions', function (Blueprint $table) {
            $table->dropColumn(['driver_commission', 'driver_commission_status', 'driver_paid_at']);
        });
    }
};