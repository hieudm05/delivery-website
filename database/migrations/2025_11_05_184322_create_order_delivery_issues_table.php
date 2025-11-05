<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('order_delivery_issues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');

            $table->enum('issue_type', [
                'recipient_not_home',
                'wrong_address',
                'refused_package',
                'unable_to_contact',
                'address_too_far',
                'dangerous_area',
                'other'
            ]);

            $table->text('issue_note')->nullable();
            $table->timestamp('issue_time');

            $table->foreignId('reported_by')->nullable()->constrained('users')->onDelete('set null');

            $table->decimal('issue_latitude', 10, 7)->nullable();
            $table->decimal('issue_longitude', 10, 7)->nullable();

            $table->timestamps();

            $table->index('order_id');
            $table->index(['issue_type', 'issue_time']);
            $table->index('reported_by');
        });
    }

    public function down()
    {
        Schema::dropIfExists('order_delivery_issues');
    }
};
