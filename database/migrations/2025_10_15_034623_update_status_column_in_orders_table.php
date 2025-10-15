<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Sửa lại kiểu dữ liệu của status thành enum
            $table->enum('status', [
                'pending',
                'confirmed',
                'picking_up',
                'picked_up',
                'shipping',
                'delivered',
                'cancelled'
            ])->default('pending')->change();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Quay lại kiểu cũ (string)
            $table->string('status')->default('pending')->change();
        });
    }
};
