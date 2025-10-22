<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // 👉 Thông tin shipper
            $table->unsignedBigInteger('driver_id')->nullable()->after('status')->comment('Người giao hàng phụ trách');

            // 👉 Thông tin lấy hàng
            $table->timestamp('actual_pickup_start_time')->nullable()->comment('Thời gian bắt đầu đi lấy hàng');
            $table->timestamp('actual_pickup_time')->nullable()->comment('Thời gian lấy hàng thực tế');
            $table->integer('actual_packages')->nullable()->comment('Số kiện thực tế');
            $table->decimal('actual_weight', 10, 2)->nullable()->comment('Cân nặng thực tế (kg)');
            $table->text('pickup_note')->nullable()->comment('Ghi chú khi lấy hàng');
            $table->decimal('pickup_latitude', 10, 7)->nullable()->comment('Vĩ độ vị trí lấy hàng');
            $table->decimal('pickup_longitude', 10, 7)->nullable()->comment('Kinh độ vị trí lấy hàng');

            // 👉 Báo cáo sự cố khi lấy hàng
            $table->enum('pickup_issue_type', [
                'shop_closed', 'wrong_address', 'no_goods', 'customer_cancel', 'other'
            ])->nullable()->comment('Loại sự cố khi lấy hàng');
            $table->text('pickup_issue_note')->nullable()->comment('Ghi chú sự cố lấy hàng');
            $table->timestamp('pickup_issue_time')->nullable()->comment('Thời gian báo sự cố lấy hàng');
            $table->unsignedBigInteger('pickup_issue_driver_id')->nullable()->comment('Shipper báo sự cố');

            // 👉 Thông tin chuyển hàng về bưu cục
            $table->unsignedBigInteger('current_hub_id')->nullable()->comment('Bưu cục hiện tại');
            $table->timestamp('hub_transfer_time')->nullable()->comment('Thời gian chuyển hàng về bưu cục');
            $table->text('hub_transfer_note')->nullable()->comment('Ghi chú khi chuyển hàng về bưu cục');

            // 👉 Index hỗ trợ truy vấn
            $table->index('driver_id');
            $table->index(['status', 'actual_pickup_time']);
            $table->index('current_hub_id');
        });

        // ✅ Cập nhật ENUM status để bổ sung trạng thái "at_hub"
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM(
            'pending',
            'confirmed',
            'picking_up',
            'picked_up',
            'at_hub',
            'shipping',
            'delivered',
            'cancelled'
        ) NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['driver_id']);
            $table->dropIndex(['status', 'actual_pickup_time']);
            $table->dropIndex(['current_hub_id']);

            $table->dropColumn([
                'driver_id',
                'actual_pickup_start_time',
                'actual_pickup_time',
                'actual_packages',
                'actual_weight',
                'pickup_note',
                'pickup_latitude',
                'pickup_longitude',
                'pickup_issue_type',
                'pickup_issue_note',
                'pickup_issue_time',
                'pickup_issue_driver_id',
                'current_hub_id',
                'hub_transfer_time',
                'hub_transfer_note',
            ]);
        });

        // ✅ Khôi phục ENUM status cũ
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM(
            'pending',
            'confirmed',
            'picking_up',
            'picked_up',
            'shipping',
            'delivered',
            'cancelled'
        ) NOT NULL DEFAULT 'pending'");
    }
};
