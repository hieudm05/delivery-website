<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('order_deliveries', function (Blueprint $table) {
            // Bước 1: Xóa foreign keys
            $table->dropForeign(['order_id']);
            $table->dropForeign(['delivery_driver_id']);
        });

        // Bước 2: Xóa unique constraint
        Schema::table('order_deliveries', function (Blueprint $table) {
            $table->dropUnique('order_deliveries_order_id_unique');
        });

        // Bước 3: Thêm cột mới
        Schema::table('order_deliveries', function (Blueprint $table) {
            $table->unsignedTinyInteger('attempt_number')->default(1)->after('order_id');
            $table->boolean('is_successful')->default(false)->after('delivery_note');
            $table->index(['order_id', 'attempt_number'], 'order_id_attempt_number_idx');
        });

        // Bước 4: Tạo lại foreign keys
        Schema::table('order_deliveries', function (Blueprint $table) {
            $table->foreign('order_id')
                ->references('id')
                ->on('orders')
                ->onDelete('cascade');
                
            $table->foreign('delivery_driver_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('order_deliveries', function (Blueprint $table) {
            // Xóa foreign keys
            $table->dropForeign(['order_id']);
            $table->dropForeign(['delivery_driver_id']);
        });

        Schema::table('order_deliveries', function (Blueprint $table) {
            // Xóa cột và index
            $table->dropIndex('order_id_attempt_number_idx');
            $table->dropColumn(['attempt_number', 'is_successful']);
        });

        // Tạo lại unique
        Schema::table('order_deliveries', function (Blueprint $table) {
            $table->unique('order_id', 'order_deliveries_order_id_unique');
        });

        // Tạo lại foreign keys
        Schema::table('order_deliveries', function (Blueprint $table) {
            $table->foreign('order_id')
                ->references('id')
                ->on('orders')
                ->onDelete('cascade');
                
            $table->foreign('delivery_driver_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }
};