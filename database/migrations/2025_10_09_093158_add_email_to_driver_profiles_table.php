<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('driver_profiles', function (Blueprint $table) {
            // Thêm cột email (đặt sau user_id cho hợp lý)
            $table->string('email')->unique()->after('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('driver_profiles', function (Blueprint $table) {
            // Khi rollback thì xóa cột email
            $table->dropColumn('email');
        });
    }
};
