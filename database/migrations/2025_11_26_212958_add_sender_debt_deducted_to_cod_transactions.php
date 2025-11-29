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
        Schema::table('cod_transactions', function (Blueprint $table) {
            // Thêm column để tracking nợ đã trừ
            $table->decimal('sender_debt_deducted', 15, 2)->default(0)->after('sender_receive_amount')
                ->comment('Số nợ đã trừ từ tiền Sender nhận (chỉ để tracking, không tính vào chi)');
            
            // Index để query nhanh các transaction có trừ nợ
            $table->index('sender_debt_deducted');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cod_transactions', function (Blueprint $table) {
            $table->dropIndex(['sender_debt_deducted']);
            $table->dropColumn('sender_debt_deducted');
        });
    }
};