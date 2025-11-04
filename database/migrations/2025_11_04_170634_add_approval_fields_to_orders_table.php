<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->boolean('auto_approved')->default(false)->after('status');
            $table->unsignedBigInteger('approved_by')->nullable()->after('auto_approved');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->text('approval_note')->nullable()->after('approved_at');
            $table->integer('risk_score')->nullable()->after('approval_note');
            
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            $table->index('auto_approved');
            $table->index('risk_score');
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropColumn([
                'auto_approved',
                'approved_by',
                'approved_at',
                'approval_note',
                'risk_score'
            ]);
        });
    }
};