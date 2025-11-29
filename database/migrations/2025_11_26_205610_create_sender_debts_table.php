<?php

// ========== 1. MIGRATION: create_sender_debts_table.php ==========
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sender_debts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sender_id')->comment('ID người gửi (A)');
            $table->unsignedBigInteger('hub_id')->comment('ID Hub (D)');
            $table->unsignedBigInteger('order_id')->nullable()->comment('ID đơn hàng liên quan');
            
            $table->decimal('amount', 15, 2)->comment('Số tiền nợ/trừ');
            $table->enum('type', ['debt', 'deduction'])->comment('debt=phát sinh nợ, deduction=trừ nợ');
            $table->enum('status', ['unpaid', 'paid', 'cancelled'])->default('unpaid');
            
            $table->text('note')->nullable()->comment('Ghi chú');
            $table->timestamp('paid_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['sender_id', 'hub_id', 'status']);
            $table->index('order_id');
            $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('hub_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sender_debts');
    }
};
