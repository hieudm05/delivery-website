<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_images', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->string('image_path'); // đường dẫn ảnh
            $table->string('type')->default('pickup'); // loại ảnh: pickup, before_ship, etc.
            $table->text('note')->nullable(); // ghi chú ảnh
            $table->timestamps();

            // Ràng buộc với bảng orders
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_images');
    }
};
