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
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->id();

            // Liên kết với user
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();

            // Thông tin ngân hàng
            $table->string('bank_code', 50)->comment('Mã ngân hàng (MB, TCB, VCB...)');
            $table->string('bank_name', 255)->comment('Tên ngân hàng');
            $table->string('account_number', 30)->comment('Số tài khoản');
            $table->string('account_name', 255)->comment('Tên chủ tài khoản');
            $table->enum('account_type', ['CHECKING', 'SAVINGS'])->default('CHECKING')->comment('Loại tài khoản');

            // Chi nhánh ngân hàng
            $table->string('branch_name', 255)->nullable()->comment('Tên chi nhánh');
            $table->string('branch_code', 50)->nullable()->comment('Mã chi nhánh');

            // Trạng thái
            $table->boolean('is_primary')->default(false)->comment('Tài khoản chính?');
            $table->boolean('is_active')->default(true)->comment('Kích hoạt?');
            $table->timestamp('verified_at')->nullable()->comment('Thời gian xác thực');
            $table->unsignedBigInteger('verified_by')->nullable()->comment('Người xác thực');
            $table->foreign('verified_by')->references('id')->on('users')->nullOnDelete();
            $table->string('verification_code', 10)->nullable()->comment('Mã xác thực');

            // QR Code
            $table->text('qr_code_url')->nullable()->comment('URL QR code');

            // Ghi chú
            $table->text('note')->nullable()->comment('Ghi chú (lý do từ chối...)');

            // Audit
            $table->unsignedBigInteger('created_by')->nullable();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();

            // Timestamps
            $table->timestamps();
            $table->softDeletes();

            // Index
            $table->index('user_id');
            $table->index('bank_code');
            $table->index(['user_id', 'is_primary']);
            $table->index(['user_id', 'is_active']);
            $table->index('verified_at');
            $table->unique(['user_id', 'account_number', 'bank_code', 'deleted_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_accounts');
    }
};