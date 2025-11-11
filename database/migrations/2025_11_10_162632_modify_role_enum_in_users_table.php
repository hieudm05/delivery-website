<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Thêm 'hub' vào enum role
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('customer', 'driver', 'admin', 'hub') NOT NULL DEFAULT 'customer'");
    }

    public function down(): void
    {
        // Quay lại enum cũ nếu rollback
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('customer', 'driver', 'admin') NOT NULL DEFAULT 'customer'");
    }
};
