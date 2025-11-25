<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('order_returns', function (Blueprint $table) {
            $table->id();
            
            // Liên kết với order
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            
            // Trạng thái hoàn hàng
            $table->enum('status', [
                'pending',      // Chờ hoàn về
                'assigned',     // Đã phân tài xế
                'returning',    // Đang hoàn về
                'completed',    // Hoàn thành
                'cancelled'     // Hủy hoàn
            ])->default('pending');
            
            // Thông tin khởi tạo hoàn hàng
            $table->enum('reason_type', [
                'auto_failed',      // Tự động (thất bại > 3 lần)
                'hub_decision',     // Hub quyết định
                'customer_request', // Khách hàng yêu cầu
                'wrong_info',       // Thông tin sai
                'other'
            ])->default('auto_failed');
            
            $table->text('reason_detail')->nullable(); // Mô tả chi tiết
            $table->integer('failed_attempts')->default(0); // Số lần thất bại trước đó
            
            // Thời gian
            $table->timestamp('initiated_at')->nullable(); // Thời điểm khởi tạo
            $table->timestamp('assigned_at')->nullable();  // Thời điểm phân tài xế
            $table->timestamp('started_at')->nullable();   // Thời điểm bắt đầu hoàn
            $table->timestamp('completed_at')->nullable(); // Thời điểm hoàn thành
            
            // Người xử lý
            $table->foreignId('initiated_by')->nullable()->constrained('users'); // Hub staff khởi tạo
            $table->foreignId('return_driver_id')->nullable()->constrained('users'); // Tài xế hoàn hàng
            
            // Thông tin sender (người nhận hoàn)
            $table->string('sender_name');
            $table->string('sender_phone');
            $table->text('sender_address');
            $table->decimal('sender_latitude', 10, 7)->nullable();
            $table->decimal('sender_longitude', 10, 7)->nullable();
            
            // Thông tin hoàn trả thực tế
            $table->timestamp('actual_return_time')->nullable();
            $table->decimal('actual_return_latitude', 10, 7)->nullable();
            $table->decimal('actual_return_longitude', 10, 7)->nullable();
            $table->text('actual_return_address')->nullable();
            
            $table->string('received_by_name')->nullable(); // Người thực tế nhận hoàn
            $table->string('received_by_phone')->nullable();
            $table->enum('received_by_relation', [
                'self', 'family', 'staff', 'other'
            ])->nullable();
            
            $table->text('return_note')->nullable(); // Ghi chú khi hoàn
            
            // Tài chính
            $table->decimal('return_fee', 10, 2)->default(0); // Phí hoàn hàng
            $table->decimal('cod_amount', 10, 2)->default(0); // Số tiền COD cần trả lại
            $table->boolean('cod_returned')->default(false); // Đã trả COD chưa
            $table->timestamp('cod_returned_at')->nullable();
            
            // Trạng thái hàng hóa khi hoàn
            $table->enum('package_condition', [
                'good',      // Nguyên vẹn
                'damaged',   // Hư hỏng
                'opened',    // Đã mở
                'missing'    // Thiếu sót
            ])->default('good');
            
            $table->text('package_condition_note')->nullable();
            
            // Thống kê
            $table->integer('return_distance')->nullable(); // Khoảng cách hoàn (km)
            $table->integer('return_duration')->nullable(); // Thời gian hoàn (phút)
            
            $table->timestamps();
            $table->softDeletes(); // Soft delete để lưu lịch sử
            
            // Indexes
            $table->index('status');
            $table->index('return_driver_id');
            $table->index('initiated_at');
            $table->index(['order_id', 'status']);
        });

        // ✅ Bảng order_return_images - Ảnh chứng từ hoàn hàng
        Schema::create('order_return_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_return_id')->constrained('order_returns')->onDelete('cascade');
            
            $table->string('image_path'); // Storage path
            $table->enum('type', [
                'package_proof',     // Ảnh hàng hóa
                'signature',         // Chữ ký người nhận
                'location_proof',    // Ảnh vị trí
                'condition_proof',   // Ảnh tình trạng hàng
                'cod_proof'         // Ảnh bằng chứng trả COD
            ])->default('package_proof');
            
            $table->text('note')->nullable();
            $table->integer('order_index')->default(0); // Thứ tự ảnh
            
            $table->timestamps();
            
            $table->index('order_return_id');
        });

        // ✅ Bảng order_return_timeline - Lịch sử chi tiết
        Schema::create('order_return_timeline', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_return_id')->constrained('order_returns')->onDelete('cascade');
            
            $table->enum('event_type', [
                'initiated',        // Khởi tạo hoàn hàng
                'assigned',         // Phân tài xế
                'driver_accepted',  // Tài xế nhận
                'started',          // Bắt đầu hoàn
                'arrived',          // Đến nơi
                'completed',        // Hoàn thành
                'issue_reported',   // Báo vấn đề
                'cancelled',        // Hủy
                'status_changed'    // Thay đổi trạng thái
            ]);
            
            $table->text('description'); // Mô tả sự kiện
            $table->json('metadata')->nullable(); // Dữ liệu bổ sung (JSON)
            
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamp('event_time');
            
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            
            $table->timestamps();
            
            $table->index('order_return_id');
            $table->index('event_type');
            $table->index('event_time');
        });

        // ✅ CẬP NHẬT bảng orders - Chỉ giữ tham chiếu đơn giản
        Schema::table('orders', function (Blueprint $table) {
            // Chỉ cần 1 cột để biết đơn có đang/đã hoàn không
            $table->boolean('has_return')->default(false)->after('status');
            
            // Số lần thất bại giao hàng
            $table->integer('delivery_attempt_count')->default(0)->after('has_return');
        });

        // ✅ CẬP NHẬT bảng order_delivery_issues
        Schema::table('order_delivery_issues', function (Blueprint $table) {
            // Thêm action resolution
            $table->enum('resolution_action', [
                'retry',          
                'return',         
                'hold_at_hub',    
                'pending'         
            ])->default('pending')->after('issue_note');
            
            $table->foreignId('resolved_by')->nullable()->constrained('users')->after('resolution_action');
            $table->timestamp('resolved_at')->nullable()->after('resolved_by');
            $table->text('resolution_note')->nullable()->after('resolved_at');
            
            // Nếu action = return, link đến bảng order_returns
            $table->foreignId('order_return_id')->nullable()->constrained('order_returns')->after('resolution_note');
        });
    }

    public function down()
    {
        Schema::table('order_delivery_issues', function (Blueprint $table) {
            $table->dropForeign(['resolved_by']);
            $table->dropForeign(['order_return_id']);
            $table->dropColumn([
                'resolution_action',
                'resolved_by',
                'resolved_at',
                'resolution_note',
                'order_return_id'
            ]);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['has_return', 'delivery_attempt_count']);
        });

        Schema::dropIfExists('order_return_timeline');
        Schema::dropIfExists('order_return_images');
        Schema::dropIfExists('order_returns');
    }
};