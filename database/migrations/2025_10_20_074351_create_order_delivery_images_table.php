<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_delivery_images', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->string('image_path'); // đường dẫn ảnh
            $table->text('note')->nullable(); // ghi chú ảnh giao hàng (vd: "Giao cho anh Nam")
            $table->string('location')->nullable(); // vị trí giao (vd: GPS hoặc địa chỉ)
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_delivery_images');
    }
};
