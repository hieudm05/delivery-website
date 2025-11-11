<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hubs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('post_office_id')->nullable(); // không liên kết bảng nào
            $table->unsignedBigInteger('user_id'); // liên kết với bảng users
            $table->decimal('hub_latitude', 10, 7)->nullable();
            $table->decimal('hub_longitude', 10, 7)->nullable();
            $table->string('hub_address', 500)->nullable();
            $table->timestamps();

            // Khóa ngoại user_id
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hubs');
    }
};
