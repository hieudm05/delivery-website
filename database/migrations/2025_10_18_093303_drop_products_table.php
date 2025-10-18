<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('products');
    }

    public function down(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('name');
            $table->float('weight');
            $table->decimal('price', 10, 2);
            $table->float('length');
            $table->float('width');
            $table->float('height');
            $table->timestamps();
        });
    }
};
