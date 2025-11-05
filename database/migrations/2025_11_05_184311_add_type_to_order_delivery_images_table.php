<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('order_delivery_images', function (Blueprint $table) {
            if (!Schema::hasColumn('order_delivery_images', 'type')) {
                $table->enum('type', [
                    'delivery_proof', 
                    'recipient_signature', 
                    'package_condition', 
                    'location_proof'
                ])->default('delivery_proof')->after('image_path');
            }

            $table->index('order_id');
            $table->index(['order_id', 'type']);
        });
    }

    public function down()
    {
        Schema::table('order_delivery_images', function (Blueprint $table) {
            $table->dropIndex(['order_id', 'type']);
            $table->dropIndex(['order_id']);
            $table->dropColumn('type');
        });
    }
};
