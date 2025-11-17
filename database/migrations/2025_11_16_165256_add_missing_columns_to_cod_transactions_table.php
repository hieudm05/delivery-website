<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cod_transactions', function (Blueprint $table) {
            // Kiểm tra và thêm các cột thiếu
            if (!Schema::hasColumn('cod_transactions', 'hub_id')) {
                $table->unsignedBigInteger('hub_id')->nullable()->after('sender_id');
                $table->foreign('hub_id')->references('id')->on('users')->onDelete('set null');
            }

            if (!Schema::hasColumn('cod_transactions', 'total_collected')) {
                $table->decimal('total_collected', 12, 2)->default(0)->after('payer_shipping');
            }

            if (!Schema::hasColumn('cod_transactions', 'hub_system_amount')) {
                $table->decimal('hub_system_amount', 12, 2)->default(0)->after('sender_receive_amount');
            }

            if (!Schema::hasColumn('cod_transactions', 'hub_system_status')) {
                $table->enum('hub_system_status', ['not_ready', 'pending', 'transferred', 'confirmed'])
                    ->default('not_ready')
                    ->after('sender_transfer_note');
            }

            if (!Schema::hasColumn('cod_transactions', 'hub_system_transfer_time')) {
                $table->timestamp('hub_system_transfer_time')->nullable()->after('hub_system_status');
            }

            if (!Schema::hasColumn('cod_transactions', 'hub_system_method')) {
                $table->string('hub_system_method', 50)->nullable()->after('hub_system_transfer_time');
            }

            if (!Schema::hasColumn('cod_transactions', 'hub_system_proof')) {
                $table->string('hub_system_proof', 255)->nullable()->after('hub_system_method');
            }

            if (!Schema::hasColumn('cod_transactions', 'hub_system_transfer_by')) {
                $table->unsignedBigInteger('hub_system_transfer_by')->nullable()->after('hub_system_proof');
                $table->foreign('hub_system_transfer_by')->references('id')->on('users')->onDelete('set null');
            }

            if (!Schema::hasColumn('cod_transactions', 'hub_system_note')) {
                $table->text('hub_system_note')->nullable()->after('hub_system_transfer_by');
            }

            if (!Schema::hasColumn('cod_transactions', 'system_confirm_time')) {
                $table->timestamp('system_confirm_time')->nullable()->after('hub_system_note');
            }

            if (!Schema::hasColumn('cod_transactions', 'system_confirm_by')) {
                $table->unsignedBigInteger('system_confirm_by')->nullable()->after('system_confirm_time');
                $table->foreign('system_confirm_by')->references('id')->on('users')->onDelete('set null');
            }

            if (!Schema::hasColumn('cod_transactions', 'system_confirm_note')) {
                $table->text('system_confirm_note')->nullable()->after('system_confirm_by');
            }

            // Bank account references
            if (!Schema::hasColumn('cod_transactions', 'shipper_bank_account_id')) {
                $table->unsignedBigInteger('shipper_bank_account_id')->nullable()->after('shipper_transfer_method');
                $table->foreign('shipper_bank_account_id')->references('id')->on('bank_accounts')->onDelete('set null');
            }

            if (!Schema::hasColumn('cod_transactions', 'sender_bank_account_id')) {
                $table->unsignedBigInteger('sender_bank_account_id')->nullable()->after('sender_transfer_method');
                $table->foreign('sender_bank_account_id')->references('id')->on('bank_accounts')->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('cod_transactions', function (Blueprint $table) {
            $columns = [
                'hub_id',
                'total_collected',
                'hub_system_amount',
                'hub_system_status',
                'hub_system_transfer_time',
                'hub_system_method',
                'hub_system_proof',
                'hub_system_transfer_by',
                'hub_system_note',
                'system_confirm_time',
                'system_confirm_by',
                'system_confirm_note',
                'shipper_bank_account_id',
                'sender_bank_account_id',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('cod_transactions', $column)) {
                    // Drop foreign keys first
                    if (in_array($column, ['hub_id', 'hub_system_transfer_by', 'system_confirm_by', 'shipper_bank_account_id', 'sender_bank_account_id'])) {
                        $table->dropForeign(['cod_transactions_' . $column . '_foreign']);
                    }
                    $table->dropColumn($column);
                }
            }
        });
    }
};