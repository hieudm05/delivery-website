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
        Schema::create('cod_transaction_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cod_transaction_id')->constrained('cod_transactions')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('action', 100); // 'driver_transfer', 'hub_confirm', 'hub_transfer_sender', 'hub_pay_commission', 'hub_transfer_system', 'system_confirm', 'dispute'
            $table->string('old_status', 50)->nullable();
            $table->string('new_status', 50)->nullable();
            $table->text('note')->nullable();
            $table->json('metadata')->nullable(); // Lưu thông tin bổ sung: amount, method, proof_path, etc.
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('cod_transaction_id');
            $table->index('user_id');
            $table->index('action');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cod_transaction_logs');
    }
};