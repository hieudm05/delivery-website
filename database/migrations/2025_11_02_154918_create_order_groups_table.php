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
        // Tạo bảng order_groups (Đơn hàng tổng)
        Schema::create('order_groups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            
            // Thông tin người gửi (chung cho tất cả đơn con)
            $table->string('sender_name');
            $table->string('sender_phone', 20);
            $table->text('sender_address');
            $table->decimal('sender_latitude', 10, 8)->nullable();
            $table->decimal('sender_longitude', 11, 8)->nullable();
            $table->string('post_office_id')->nullable();
            $table->timestamp('pickup_time')->nullable();
            
            // Thống kê đơn hàng
            $table->integer('total_recipients')->default(0);
            $table->decimal('total_shipping_fee', 15, 2)->default(0);
            $table->decimal('total_cod_fee', 15, 2)->default(0);
            $table->decimal('total_sender_pays', 15, 2)->default(0);
            $table->decimal('total_recipient_pays', 15, 2)->default(0);
            
            // Trạng thái tổng
            $table->enum('status', [
                'pending',      // Chờ xác nhận
                'confirmed',    // Đã xác nhận
                'picking_up',   // Đang lấy hàng
                'picked_up',    // Đã lấy hàng
                'in_transit',   // Đang vận chuyển
                'partially_delivered', // Giao một phần
                'completed',    // Hoàn thành hết
                'cancelled'     // Đã hủy
            ])->default('pending');
            
            // Ghi chú chung
            $table->text('note')->nullable();
            
            $table->timestamps();
            
            // Foreign key
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            // Indexes
            $table->index('user_id');
            $table->index('status');
            $table->index('created_at');
        });
        
        // Thêm field order_group_id vào bảng orders
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('order_group_id')->nullable()->after('id');
            $table->foreign('order_group_id')
                  ->references('id')
                  ->on('order_groups')
                  ->onDelete('cascade');
            
            $table->index('order_group_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['order_group_id']);
            $table->dropColumn('order_group_id');
        });
        
        Schema::dropIfExists('order_groups');
    }
};