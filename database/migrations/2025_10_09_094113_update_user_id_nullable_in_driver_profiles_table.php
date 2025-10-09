<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('driver_profiles', function (Blueprint $table) {
            // Xóa ràng buộc khóa ngoại cũ
            $table->dropForeign(['user_id']);

            // Cho phép user_id nullable
            $table->unsignedBigInteger('user_id')->nullable()->change();

            // Thêm lại khóa ngoại với onDelete('cascade')
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('driver_profiles', function (Blueprint $table) {
            // Xóa khóa ngoại mới
            $table->dropForeign(['user_id']);

            // Đưa user_id về NOT NULL như ban đầu
            $table->unsignedBigInteger('user_id')->nullable(false)->change();

            // Thêm lại khóa ngoại cũ
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
};
