<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("
            ALTER TABLE cod_transactions
            MODIFY sender_payment_status
            ENUM('not_ready', 'pending', 'completed', 'not_applicable')
            NOT NULL
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE cod_transactions
            MODIFY sender_payment_status
            ENUM('not_ready', 'pending', 'completed')
            NOT NULL
        ");
    }
};
