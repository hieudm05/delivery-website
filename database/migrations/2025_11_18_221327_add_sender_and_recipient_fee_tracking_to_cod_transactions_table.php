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
            // Sender fee payment tracking
            $table->decimal('sender_fee_paid', 10, 2)
                ->default(0)
                ->after('sender_receive_amount')
                ->comment('Số tiền phí sender cần trả (từ order.sender_total)');

            $table->timestamp('sender_fee_paid_at')
                ->nullable()
                ->after('sender_fee_paid')
                ->comment('Thời điểm sender thanh toán phí');

            $table->string('sender_fee_payment_method')
                ->nullable()
                ->after('sender_fee_paid_at')
                ->comment('Phương thức thanh toán phí: bank_transfer, wallet, cash');

            // Recipient fee payment tracking
            $table->decimal('recipient_fee_paid', 10, 2)
                ->default(0)
                ->after('sender_fee_payment_method')
                ->comment('Số tiền phí recipient cần trả (từ order.recipient_total)');

            $table->timestamp('recipient_fee_paid_at')
                ->nullable()
                ->after('recipient_fee_paid')
                ->comment('Thời điểm recipient thanh toán phí');

            $table->string('recipient_fee_payment_method')
                ->nullable()
                ->after('recipient_fee_paid_at')
                ->comment('Phương thức thanh toán phí: bank_transfer, wallet, cash');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cod_transactions', function (Blueprint $table) {
            $table->dropColumn([
                'sender_fee_paid',
                'sender_fee_paid_at',
                'sender_fee_payment_method',
                'recipient_fee_paid',
                'recipient_fee_paid_at',
                'recipient_fee_payment_method',
            ]);
        });
    }
};
