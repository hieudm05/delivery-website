<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Chỉ tạo index nếu chưa có
        if (!Schema::hasIndex('orders', ['has_return', 'status'])) {
            Schema::table('orders', function (Blueprint $table) {
                $table->index(['has_return', 'status']);
            });
        }
    }

    public function down(): void
    {
        // Chỉ xóa index nếu tồn tại
        if (Schema::hasIndex('orders', ['has_return', 'status'])) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropIndex(['has_return', 'status']);
            });
        }
    }
};
