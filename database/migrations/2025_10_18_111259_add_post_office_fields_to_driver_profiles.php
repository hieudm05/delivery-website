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
        Schema::table('driver_profiles', function (Blueprint $table) {
             // Thêm province_code nếu chưa có
            if (!Schema::hasColumn('driver_profiles', 'province_code')) {
                $table->string('province_code')->nullable()->after('email')->comment('Mã tỉnh/thành phố');
            }
            // Thêm các trường bưu cục
            $table->string('post_office_id')->nullable()->after('province_code')->comment('ID bưu cục');
            $table->string('post_office_name')->nullable()->after('post_office_id')->comment('Tên bưu cục');
            $table->text('post_office_address')->nullable()->after('post_office_name')->comment('Địa chỉ bưu cục');
            $table->decimal('post_office_lat', 10, 8)->nullable()->after('post_office_address')->comment('Vĩ độ bưu cục');
            $table->decimal('post_office_lng', 11, 8)->nullable()->after('post_office_lat')->comment('Kinh độ bưu cục');
            $table->string('post_office_phone')->nullable()->after('post_office_lng')->comment('Số điện thoại bưu cục');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('driver_profiles', function (Blueprint $table) {
            // Xóa các trường khi rollback
            $table->dropColumn([
                'post_office_id',
                'post_office_name',
                'post_office_address',
                'post_office_lat',
                'post_office_lng',
                'post_office_phone',
            ]);
        });
    }
};