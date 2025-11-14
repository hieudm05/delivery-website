<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_accounts', function (Blueprint $table) {
            $table->id();

            /**
             * owner_type:
             *  - system (hệ thống)
             *  - branch (bưu cục)
             *  - shop (người gửi)
             */
            $table->enum('owner_type', ['system', 'branch', 'shop']);

            // Nếu là shop hoặc branch → cần lưu id
            $table->unsignedBigInteger('owner_id')->nullable();

            // Thông tin ngân hàng
            $table->string('bank_code', 20);        // Ví dụ: MBBANK, VIETCOMBANK
            $table->string('bank_name', 100);       // Tên đầy đủ của ngân hàng
            $table->string('account_number', 50);   // Số tài khoản
            $table->string('account_name', 100);    // Tên chủ tài khoản

            // QR template: cho custom addInfo (nếu cần)
            $table->string('qr_template')->nullable();

            // Ghi chú nếu cần
            $table->text('note')->nullable();

            $table->timestamps();

            // Chỉ mục
            $table->index(['owner_type', 'owner_id']);
            $table->index(['bank_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_accounts');
    }
};
