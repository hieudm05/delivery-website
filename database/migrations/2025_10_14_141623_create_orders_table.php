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
    Schema::create('orders', function (Blueprint $table) {
        $table->id();
        $table->string('sender_id')->nullable();
        $table->string('sender_name');
        $table->string('sender_phone');
        $table->text('sender_address');
        $table->decimal('sender_latitude', 10, 7);
        $table->decimal('sender_longitude', 10, 7);
        $table->string('post_office_id');
        $table->dateTime('pickup_time');
        $table->string('recipient_name');
        $table->string('recipient_phone');
        $table->string('province_code');
        $table->string('district_code');
        $table->string('ward_code');
        $table->string('address_detail');
        $table->decimal('recipient_latitude', 10, 7);
        $table->decimal('recipient_longitude', 10, 7);
        $table->text('recipient_full_address');
        $table->dateTime('delivery_time');
        $table->string('item_type');
        $table->json('services')->nullable();
        $table->decimal('cod_amount', 15, 2)->nullable();
        $table->text('note')->nullable();
        $table->json('products_json');
        $table->boolean('save_address')->default(false);
        $table->string('status')->default('pending');
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
