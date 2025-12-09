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
        Schema::table('orders', function (Blueprint $table) {
            // Thêm cột distance_fee sau cột shipping_fee
            $table->decimal('distance_fee', 10, 2)->default(0)->after('shipping_fee')
                ->comment('Phí theo khoảng cách địa lý');
            
            // Thêm cột distance_km để lưu khoảng cách tính toán
            $table->decimal('distance_km', 8, 2)->nullable()->after('distance_fee')
                ->comment('Khoảng cách từ trung tâm (km)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['distance_fee', 'distance_km']);
        });
    }
};