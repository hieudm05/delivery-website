<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('order_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->unique()->constrained('orders')->onDelete('cascade');

            $table->foreignId('delivery_driver_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('actual_delivery_start_time')->nullable();
            $table->timestamp('actual_delivery_time')->nullable();

            $table->decimal('delivery_latitude', 10, 7)->nullable();
            $table->decimal('delivery_longitude', 10, 7)->nullable();
            $table->string('delivery_address')->nullable();

            $table->string('received_by_name')->nullable();
            $table->string('received_by_phone', 20)->nullable();
            $table->enum('received_by_relation', [
                'self', 'family', 'neighbor', 'security', 'other'
            ])->nullable();

            $table->text('delivery_note')->nullable();

            $table->decimal('cod_collected_amount', 15, 2)->default(0);
            $table->timestamp('cod_collected_at')->nullable();

            $table->timestamps();

            $table->index('delivery_driver_id');
            $table->index('actual_delivery_time');
            $table->index('cod_collected_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('order_deliveries');
    }
};
