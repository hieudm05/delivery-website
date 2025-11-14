<?php 
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cod_transactions', function (Blueprint $table) {

            // ==== Bổ sung trạng thái rõ ràng hơn ====
            if (!Schema::hasColumn('cod_transactions', 'cod_type')) {
                $table->enum('cod_type', ['cod', 'non_cod'])
                      ->default('cod')
                      ->after('order_id'); 
            }

            // ==== Bổ sung trường người tạo đơn (shop) ====
            if (!Schema::hasColumn('cod_transactions', 'shop_id')) {
                $table->unsignedBigInteger('shop_id')->nullable()->after('driver_id');
            }

            // ==== Bổ sung phí nền tảng nếu chưa có ====
            if (!Schema::hasColumn('cod_transactions', 'platform_fee')) {
                $table->decimal('platform_fee', 15, 2)->nullable()->after('sender_receive_amount');
            }

            // ==== Bổ sung trạng thái shipper → admin ====
            if (!Schema::hasColumn('cod_transactions', 'shipper_payment_status')) {
                $table->enum('shipper_payment_status', ['pending','transferred','confirmed','disputed'])
                      ->default('pending')
                      ->after('total_collected');
            }

            // ==== Bổ sung trạng thái admin → shop ====
            if (!Schema::hasColumn('cod_transactions', 'sender_payment_status')) {
                $table->enum('sender_payment_status', ['pending','processing','completed','failed'])
                      ->default('pending')
                      ->after('admin_note');
            }

        });
    }

    public function down(): void
    {
        Schema::table('cod_transactions', function (Blueprint $table) {
            // Không rollback vì đang cập nhật bảng đang dùng
        });
    }
};
