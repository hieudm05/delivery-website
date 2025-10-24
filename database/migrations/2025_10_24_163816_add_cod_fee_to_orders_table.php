<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            // ✅ Phí dịch vụ COD (1.000đ + 1% cod_amount)
            $table->decimal('cod_fee', 10, 2)->default(0)->after('cod_amount')
                  ->comment('Phí dịch vụ thu hộ COD');
            
            // ✅ Tổng phí ship (base + extra, không bao gồm COD)
            $table->decimal('shipping_fee', 10, 2)->default(0)->after('cod_fee')
                  ->comment('Tổng phí vận chuyển');
            
            // ✅ Tổng tiền người gửi phải trả
            $table->decimal('sender_total', 10, 2)->default(0)->after('shipping_fee')
                  ->comment('Tổng tiền người gửi trả');
            
            // ✅ Tổng tiền người nhận phải trả
            $table->decimal('recipient_total', 10, 2)->default(0)->after('sender_total')
                  ->comment('Tổng tiền người nhận trả');
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['cod_fee', 'shipping_fee', 'sender_total', 'recipient_total']);
        });
    }
};