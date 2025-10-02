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
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id'); // ID người dùng
            $table->string('email', 255)->unique(); // Email đăng nhập
            $table->string('phone', 20)->unique(); // Số điện thoại
            $table->string('password_hash', 255); // Mật khẩu mã hóa
            $table->string('full_name', 255); // Họ tên

            // ENUM role
            $table->enum('role', ['customer', 'driver', 'admin'])->default('customer');

            // ENUM status
            $table->enum('status', ['active', 'inactive', 'pending', 'rejected'])->default('pending');

            $table->string('avatar_url', 500)->nullable(); // Link ảnh đại diện

            $table->timestamp('created_at')->useCurrent(); // Thời gian tạo
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate(); // Thời gian cập nhật
            $table->timestamp('last_login_at')->nullable(); // Lần đăng nhập cuối
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
