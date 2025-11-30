<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('cod_transactions', function (Blueprint $table) {
            //  Thêm trạng thái xác nhận phí từ Customer
            $table->enum('sender_fee_status', ['pending', 'transferred', 'confirmed', 'rejected'])
                ->default('pending')
                ->after('sender_fee_paid')
                ->comment('Trạng thái xác nhận phí: pending=chưa trả, transferred=đã chuyển chờ xác nhận, confirmed=đã xác nhận, rejected=từ chối');
            
            $table->string('sender_fee_payment_proof')->nullable()->after('sender_fee_payment_method');
            $table->unsignedBigInteger('sender_fee_confirmed_by')->nullable()->after('sender_fee_paid_at');
            $table->timestamp('sender_fee_confirmed_at')->nullable()->after('sender_fee_confirmed_by');
            $table->text('sender_fee_rejection_reason')->nullable()->after('sender_fee_confirmed_at');
            
            // Thêm flag để đánh dấu nợ đã được xử lý
            $table->boolean('debt_processed')->default(false)->after('sender_debt_deducted')
                ->comment('Đã xử lý trừ nợ chưa (chỉ trừ khi Hub confirm nhận tiền)');
            
            // Foreign keys
            $table->foreign('sender_fee_confirmed_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('cod_transactions', function (Blueprint $table) {
            $table->dropForeign(['sender_fee_confirmed_by']);
            $table->dropColumn([
                'sender_fee_status',
                'sender_fee_payment_proof',
                'sender_fee_confirmed_by',
                'sender_fee_confirmed_at',
                'sender_fee_rejection_reason',
                'debt_processed'
            ]);
        });
    }
};