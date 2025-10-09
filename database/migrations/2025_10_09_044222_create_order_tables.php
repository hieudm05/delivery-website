<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // Bảng đơn hàng
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sender_id');
            $table->string('receiver_name');
            $table->string('receiver_phone');
            $table->string('receiver_address');
            $table->string('province_code')->nullable();
            $table->string('district_code')->nullable();
            $table->string('ward_code')->nullable();
            $table->float('weight')->default(0);
            $table->float('length')->nullable();
            $table->float('width')->nullable();
            $table->float('height')->nullable();
            $table->string('service_type')->default('tiet_kiem'); // hoặc 'nhanh'
            $table->decimal('cod_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->string('status')->default('pending');
            $table->timestamps();

            $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
        });

        // Bảng chi tiết hàng hóa
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->string('name');
            $table->integer('quantity')->default(1);
            $table->decimal('value', 12, 2)->default(0);
            $table->json('special_type')->nullable(); // Dễ vỡ, quá khổ, v.v.
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
        });

        // Bảng dịch vụ cộng thêm
        Schema::create('extra_services', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('fee_type', ['fixed', 'percent'])->default('fixed');
            $table->decimal('fee_value', 8, 2)->default(0);
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // Gắn dịch vụ cộng thêm vào đơn hàng
        Schema::create('order_extra_service', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('extra_service_id');
            $table->decimal('amount', 12, 2)->default(0);
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->foreign('extra_service_id')->references('id')->on('extra_services')->onDelete('cascade');
        });
    }

    public function down(): void {
        Schema::dropIfExists('order_extra_service');
        Schema::dropIfExists('extra_services');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
