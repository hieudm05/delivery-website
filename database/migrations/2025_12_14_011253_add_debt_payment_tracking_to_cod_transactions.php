<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * ✅ THÊM CÁC CỘT THEO DÕI THANH TOÁN NỢ VÀO COD_TRANSACTIONS
     * 
     * Lý do: Không cần thêm trạng thái 'pending' vào SenderDebt,
     * mà lưu thông tin thanh toán vào CodTransaction để Hub xác nhận.
     */
    public function up(): void
    {
        Schema::table('cod_transactions', function (Blueprint $table) {
            // ✅ THÔNG TIN THANH TOÁN NỢ (do Customer gửi)
            $table->string('sender_debt_payment_method')->nullable()->after('sender_fee_payment_proof')
                ->comment('bank_transfer, cash');
            
            $table->string('sender_debt_payment_proof')->nullable()->after('sender_debt_payment_method')
                ->comment('Ảnh chứng từ thanh toán nợ');
            
            $table->timestamp('sender_debt_paid_at')->nullable()->after('sender_debt_payment_proof')
                ->comment('Thời điểm customer gửi thanh toán');

            // ✅ TRẠNG THÁI XÁC NHẬN (do Hub xử lý)
            $table->enum('sender_debt_payment_status', ['pending', 'completed', 'rejected'])
                ->nullable()->after('sender_debt_paid_at')
                ->comment('Trạng thái xác nhận thanh toán nợ từ Hub');

            // ✅ THÔNG TIN XÁC NHẬN
            $table->timestamp('sender_debt_confirmed_at')->nullable()->after('sender_debt_payment_status')
                ->comment('Thời điểm Hub xác nhận');
            
            $table->unsignedBigInteger('sender_debt_confirmed_by')->nullable()->after('sender_debt_confirmed_at')
                ->comment('Hub user ID xác nhận');

            // ✅ THÔNG TIN TỪ CHỐI (nếu có)
            $table->text('sender_debt_rejection_reason')->nullable()->after('sender_debt_confirmed_by')
                ->comment('Lý do Hub từ chối thanh toán');
            
            $table->timestamp('sender_debt_rejected_at')->nullable()->after('sender_debt_rejection_reason');
            
            $table->unsignedBigInteger('sender_debt_rejected_by')->nullable()->after('sender_debt_rejected_at')
                ->comment('Hub user ID từ chối');

            // ✅ INDEX
            $table->index('sender_debt_payment_status');
            $table->index(['sender_id', 'sender_debt_payment_status']);
            $table->index(['hub_id', 'sender_debt_payment_status']);

            // ✅ FOREIGN KEY
            $table->foreign('sender_debt_confirmed_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('sender_debt_rejected_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cod_transactions', function (Blueprint $table) {
            $table->dropForeign(['sender_debt_confirmed_by']);
            $table->dropForeign(['sender_debt_rejected_by']);
            
            $table->dropIndex(['sender_debt_payment_status']);
            $table->dropIndex(['sender_id', 'sender_debt_payment_status']);
            $table->dropIndex(['hub_id', 'sender_debt_payment_status']);

            $table->dropColumn([
                'sender_debt_payment_method',
                'sender_debt_payment_proof',
                'sender_debt_paid_at',
                'sender_debt_payment_status',
                'sender_debt_confirmed_at',
                'sender_debt_confirmed_by',
                'sender_debt_rejection_reason',
                'sender_debt_rejected_at',
                'sender_debt_rejected_by',
            ]);
        });
    }
};