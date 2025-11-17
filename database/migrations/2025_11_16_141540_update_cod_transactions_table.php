<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cod_transactions', function (Blueprint $table) {

            // hub_id
            if (!Schema::hasColumn('cod_transactions', 'hub_id')) {
                $table->unsignedBigInteger('hub_id')->nullable()->after('sender_id');
            }

            // shipper_bank_account_id
            if (!Schema::hasColumn('cod_transactions', 'shipper_bank_account_id')) {
                $table->unsignedBigInteger('shipper_bank_account_id')->nullable()->after('shipper_transfer_method');
            }

            // sender_bank_account_id
            if (!Schema::hasColumn('cod_transactions', 'sender_bank_account_id')) {
                $table->unsignedBigInteger('sender_bank_account_id')->nullable()->after('sender_transfer_method');
            }

            // total_collected
            if (!Schema::hasColumn('cod_transactions', 'total_collected')) {
                $table->decimal('total_collected', 12, 2)->default(0)->after('payer_shipping');
            }
        });

        // --- Add foreign keys in a separate block ---
        Schema::table('cod_transactions', function (Blueprint $table) {

            // hub_id FK
            if (Schema::hasColumn('cod_transactions', 'hub_id')) {
                $table->foreign('hub_id')
                    ->references('id')->on('users')
                    ->onDelete('set null');
            }

            // shipper bank account FK
            if (Schema::hasColumn('cod_transactions', 'shipper_bank_account_id')) {
                $table->foreign('shipper_bank_account_id')
                    ->references('id')->on('bank_accounts')
                    ->onDelete('set null');
            }

            // sender bank account FK
            if (Schema::hasColumn('cod_transactions', 'sender_bank_account_id')) {
                $table->foreign('sender_bank_account_id')
                    ->references('id')->on('bank_accounts')
                    ->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        // Down phải kiểm tra tồn tại trước khi drop
        Schema::table('cod_transactions', function (Blueprint $table) {

            if (Schema::hasColumn('cod_transactions', 'hub_id')) {
                $table->dropForeign(['hub_id']);
                $table->dropColumn('hub_id');
            }

            if (Schema::hasColumn('cod_transactions', 'shipper_bank_account_id')) {
                $table->dropForeign(['shipper_bank_account_id']);
                $table->dropColumn('shipper_bank_account_id');
            }

            if (Schema::hasColumn('cod_transactions', 'sender_bank_account_id')) {
                $table->dropForeign(['sender_bank_account_id']);
                $table->dropColumn('sender_bank_account_id');
            }

            if (Schema::hasColumn('cod_transactions', 'total_collected')) {
                $table->dropColumn('total_collected');
            }
        });
    }
};
