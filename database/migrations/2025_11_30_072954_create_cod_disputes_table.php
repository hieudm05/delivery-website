<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('cod_disputes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cod_transaction_id')->constrained('cod_transactions')->onDelete('cascade');
            $table->foreignId('reporter_id')->constrained('users')->onDelete('cascade');
            $table->enum('reporter_role', ['driver', 'hub', 'sender', 'admin']);
            $table->text('reason')->comment('Lý do tranh chấp');
            $table->string('proof_file')->nullable()->comment('File chứng minh');
            
            $table->enum('status', ['pending', 'investigating', 'resolved', 'rejected'])
                ->default('pending')
                ->comment('pending=chờ xử lý, investigating=đang điều tra, resolved=đã giải quyết, rejected=từ chối');
            
            $table->foreignId('resolver_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('resolution_note')->nullable()->comment('Kết quả xử lý');
            $table->json('resolution_actions')->nullable()->comment('Hành động đã thực hiện (refund, adjust_amount, etc.)');
            $table->timestamp('resolved_at')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['cod_transaction_id', 'status']);
            $table->index('reporter_id');
            $table->index('resolver_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('cod_disputes');
    }
};