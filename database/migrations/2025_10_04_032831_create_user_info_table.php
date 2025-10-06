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
        Schema::create('user_info', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique(); // Liên kết với bảng users
            $table->string('national_id',20)->unique()->nullable(false); // Số CMND/CCCD
            $table->string('tax_code',20)->unique()->nullable(); // Mã số thuế
            $table->date('date_of_birth')->nullable();
            $table->string('full_address')->nullable();
            // Mã code địa chỉ
            $table->unsignedInteger('province_code')->nullable();
            $table->unsignedInteger('district_code')->nullable();
            $table->unsignedInteger('ward_code')->nullable();
            $table->timestamps();

            // Khoá ngoại nhé
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_info');
    }
};
