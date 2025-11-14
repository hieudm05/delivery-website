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
        Schema::table('bank_accounts', function (Blueprint $table) {
            // Thêm các trường mới từ VietQR API
            $table->string('bank_short_name')->nullable()->after('bank_name');
            $table->string('bank_logo')->nullable()->after('bank_short_name');
            
            // Xóa các trường không cần thiết
            $table->dropColumn([
                'account_type',
                'branch_name', 
                'branch_code'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bank_accounts', function (Blueprint $table) {
            // Khôi phục các trường cũ
            $table->enum('account_type', ['CHECKING', 'SAVINGS'])->default('CHECKING')->after('account_name');
            $table->string('branch_name')->nullable()->after('verification_code');
            $table->string('branch_code', 50)->nullable()->after('branch_name');
            
            // Xóa các trường mới
            $table->dropColumn([
                'bank_short_name',
                'bank_logo'
            ]);
        });
    }
};