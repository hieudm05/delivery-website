<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Sửa ENUM để thêm 'refund'
        DB::statement("ALTER TABLE sender_debts MODIFY COLUMN type ENUM('debt', 'deduction', 'refund', 'adjustment') NOT NULL COMMENT 'debt=nợ phát sinh, deduction=trừ nợ, refund=hoàn nợ khi hủy đơn, adjustment=điều chỉnh'");
    }

    public function down()
    {
        DB::statement("ALTER TABLE sender_debts MODIFY COLUMN type ENUM('debt', 'deduction', 'adjustment') NOT NULL");
    }
};