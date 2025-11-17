<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cod_transactions', function (Blueprint $table) {
            // ========== BASIC INFO ==========
            if (!Schema::hasColumn('cod_transactions', 'order_id')) {
                $table->unsignedBigInteger('order_id')->after('id');
                $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            }
            
            if (!Schema::hasColumn('cod_transactions', 'driver_id')) {
                $table->unsignedBigInteger('driver_id')->nullable()->after('order_id');
                $table->foreign('driver_id')->references('id')->on('users')->onDelete('set null');
            }
            
            if (!Schema::hasColumn('cod_transactions', 'sender_id')) {
                $table->unsignedBigInteger('sender_id')->nullable()->after('driver_id');
                $table->foreign('sender_id')->references('id')->on('users')->onDelete('set null');
            }
            
            if (!Schema::hasColumn('cod_transactions', 'hub_id')) {
                $table->unsignedBigInteger('hub_id')->nullable()->after('sender_id');
                $table->foreign('hub_id')->references('id')->on('users')->onDelete('set null');
            }

            // ========== MONEY ==========
            if (!Schema::hasColumn('cod_transactions', 'cod_amount')) {
                $table->decimal('cod_amount', 12, 2)->default(0)->after('hub_id');
            }
            
            if (!Schema::hasColumn('cod_transactions', 'shipping_fee')) {
                $table->decimal('shipping_fee', 12, 2)->default(0)->after('cod_amount');
            }
            
            if (!Schema::hasColumn('cod_transactions', 'platform_fee')) {
                $table->decimal('platform_fee', 12, 2)->default(0)->after('shipping_fee');
            }
            
            if (!Schema::hasColumn('cod_transactions', 'sender_receive_amount')) {
                $table->decimal('sender_receive_amount', 12, 2)->default(0)->after('platform_fee');
            }
            
            if (!Schema::hasColumn('cod_transactions', 'payer_shipping')) {
                $table->enum('payer_shipping', ['sender', 'recipient'])
                    ->default('sender')
                    ->after('sender_receive_amount');
            }
            
            if (!Schema::hasColumn('cod_transactions', 'total_collected')) {
                $table->decimal('total_collected', 12, 2)->default(0)->after('payer_shipping');
            }
            
            if (!Schema::hasColumn('cod_transactions', 'hub_system_amount')) {
                $table->decimal('hub_system_amount', 12, 2)->default(0)->after('total_collected');
            }

            // ========== SHIPPER → HUB ==========
            if (!Schema::hasColumn('cod_transactions', 'shipper_payment_status')) {
                $table->enum('shipper_payment_status', ['pending', 'transferred', 'confirmed', 'disputed'])
                    ->default('pending')
                    ->after('hub_system_amount');
            }
            
            if (!Schema::hasColumn('cod_transactions', 'shipper_transfer_time')) {
                $table->timestamp('shipper_transfer_time')->nullable()->after('shipper_payment_status');
            }
            
            if (!Schema::hasColumn('cod_transactions', 'shipper_transfer_method')) {
                $table->string('shipper_transfer_method', 50)->nullable()->after('shipper_transfer_time');
            }
            
            if (!Schema::hasColumn('cod_transactions', 'shipper_bank_account_id')) {
                $table->unsignedBigInteger('shipper_bank_account_id')->nullable()->after('shipper_transfer_method');
                $table->foreign('shipper_bank_account_id')->references('id')->on('bank_accounts')->onDelete('set null');
            }
            
            if (!Schema::hasColumn('cod_transactions', 'shipper_transfer_proof')) {
                $table->string('shipper_transfer_proof', 255)->nullable()->after('shipper_bank_account_id');
            }
            
            if (!Schema::hasColumn('cod_transactions', 'shipper_note')) {
                $table->text('shipper_note')->nullable()->after('shipper_transfer_proof');
            }
            
            if (!Schema::hasColumn('cod_transactions', 'hub_confirm_time')) {
                $table->timestamp('hub_confirm_time')->nullable()->after('shipper_note');
            }
            
            if (!Schema::hasColumn('cod_transactions', 'hub_confirm_by')) {
                $table->unsignedBigInteger('hub_confirm_by')->nullable()->after('hub_confirm_time');
                $table->foreign('hub_confirm_by')->references('id')->on('users')->onDelete('set null');
            }
            
            if (!Schema::hasColumn('cod_transactions', 'hub_confirm_note')) {
                $table->text('hub_confirm_note')->nullable()->after('hub_confirm_by');
            }

            // ========== HUB → SENDER ==========
            if (!Schema::hasColumn('cod_transactions', 'sender_payment_status')) {
                $table->enum('sender_payment_status', ['not_ready', 'pending', 'completed'])
                    ->default('not_ready')
                    ->after('hub_confirm_note');
            }
            
            if (!Schema::hasColumn('cod_transactions', 'sender_transfer_time')) {
                $table->timestamp('sender_transfer_time')->nullable()->after('sender_payment_status');
            }
            
            if (!Schema::hasColumn('cod_transactions', 'sender_transfer_method')) {
                $table->string('sender_transfer_method', 50)->nullable()->after('sender_transfer_time');
            }
            
            if (!Schema::hasColumn('cod_transactions', 'sender_bank_account_id')) {
                $table->unsignedBigInteger('sender_bank_account_id')->nullable()->after('sender_transfer_method');
                $table->foreign('sender_bank_account_id')->references('id')->on('bank_accounts')->onDelete('set null');
            }
            
            if (!Schema::hasColumn('cod_transactions', 'sender_transfer_proof')) {
                $table->string('sender_transfer_proof', 255)->nullable()->after('sender_bank_account_id');
            }
            
            if (!Schema::hasColumn('cod_transactions', 'sender_transfer_by')) {
                $table->unsignedBigInteger('sender_transfer_by')->nullable()->after('sender_transfer_proof');
                $table->foreign('sender_transfer_by')->references('id')->on('users')->onDelete('set null');
            }
            
            if (!Schema::hasColumn('cod_transactions', 'sender_transfer_note')) {
                $table->text('sender_transfer_note')->nullable()->after('sender_transfer_by');
            }

            // ========== HUB → SYSTEM ==========
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

            // ========== AUDIT ==========
            if (!Schema::hasColumn('cod_transactions', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->after('system_confirm_note');
                $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            }
            
            if (!Schema::hasColumn('cod_transactions', 'updated_by')) {
                $table->unsignedBigInteger('updated_by')->nullable()->after('created_by');
                $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        // Không cần down vì migration này chỉ thêm, không xóa
    }
};