<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * ✅ Bảng theo dõi tiền COD từ shipper → admin → sender
     */
    public function up()
    {
        Schema::create('cod_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            
            // Thông tin COD
            $table->decimal('cod_amount', 15, 2)->default(0)->comment('Số tiền COD');
            $table->decimal('shipping_fee', 15, 2)->default(0)->comment('Phí ship (nếu người nhận trả)');
            $table->decimal('total_collected', 15, 2)->default(0)->comment('Tổng shipper thu = COD + phí ship');
            
            // GIAI ĐOẠN 1: Shipper → Admin
            $table->enum('shipper_payment_status', [
                'pending',      // Chờ shipper chuyển
                'transferred',  // Shipper đã chuyển
                'confirmed',    // Admin xác nhận nhận được
                'disputed'      // Có tranh chấp
            ])->default('pending');
            
            $table->foreignId('driver_id')->nullable()->constrained('users')->comment('ID shipper');
            $table->timestamp('shipper_transfer_time')->nullable()->comment('Thời gian shipper chuyển');
            $table->string('shipper_transfer_method')->nullable()->comment('Phương thức: bank_transfer, cash, wallet');
            $table->text('shipper_transfer_proof')->nullable()->comment('Ảnh chứng từ chuyển tiền');
            $table->text('shipper_note')->nullable();
            
            $table->timestamp('admin_confirm_time')->nullable()->comment('Admin xác nhận nhận');
            $table->foreignId('admin_confirm_by')->nullable()->constrained('users');
            $table->text('admin_note')->nullable();
            
            // GIAI ĐOẠN 2: Admin → Sender
            $table->enum('sender_payment_status', [
                'pending',      // Chờ chuyển cho sender
                'processing',   // Đang xử lý
                'completed',    // Đã chuyển thành công
                'failed'        // Thất bại
            ])->default('pending');
            
            $table->foreignId('sender_id')->constrained('users')->comment('Người gửi hàng');
            $table->decimal('sender_receive_amount', 15, 2)->default(0)->comment('Số tiền sender nhận = COD - phí');
            $table->decimal('platform_fee', 15, 2)->default(0)->comment('Phí nền tảng trừ (nếu có)');
            
            $table->timestamp('sender_transfer_time')->nullable();
            $table->string('sender_transfer_method')->nullable()->comment('bank_transfer, wallet');
            $table->text('sender_transfer_proof')->nullable();
            $table->foreignId('sender_transfer_by')->nullable()->constrained('users')->comment('Admin thực hiện');
            $table->text('sender_transfer_note')->nullable();
            
            $table->timestamps();
            
            // ✅ Indexes (đặt tên ngắn để tránh lỗi Identifier quá dài)
            $table->index(['shipper_payment_status', 'sender_payment_status'], 'cod_txn_status_idx');
            $table->index('driver_id', 'cod_txn_driver_idx');
            $table->index('sender_id', 'cod_txn_sender_idx');
        });

        // ✅ Thêm cột vào bảng orders để link nhanh
        Schema::table('orders', function (Blueprint $table) {
            $table->boolean('has_cod_transaction')->default(false)->after('cod_amount');
            $table->enum('cod_status', [
                'not_applicable',  // Không có COD
                'pending',         // Chờ thu
                'collected',       // Đã thu từ khách
                'transferred',     // Shipper đã chuyển admin
                'settled'          // Đã thanh toán cho sender
            ])->default('not_applicable')->after('has_cod_transaction');
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['has_cod_transaction', 'cod_status']);
        });
        
        Schema::dropIfExists('cod_transactions');
    }
};
