<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Đổi từ bigint sang string
            $table->string('post_office_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Khôi phục lại kiểu cũ nếu rollback
            $table->unsignedBigInteger('post_office_id')->nullable()->change();
        });
    }
};
