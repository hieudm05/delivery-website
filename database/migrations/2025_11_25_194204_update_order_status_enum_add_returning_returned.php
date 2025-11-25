<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Lấy enum hiện tại
        $enumValues = DB::select("
            SHOW COLUMNS FROM orders LIKE 'status'
        ")[0]->Type;

        // Tách giá trị ENUM hiện có
        preg_match("/^enum\((.*)\)$/", $enumValues, $matches);
        $currentValues = array_map(function ($value) {
            return trim($value, "'");
        }, explode(",", $matches[1]));

        // Thêm 2 value mới nếu chưa có
        if (!in_array('returning', $currentValues)) {
            $currentValues[] = 'returning';
        }

        if (!in_array('returned', $currentValues)) {
            $currentValues[] = 'returned';
        }

        // Convert thành chuỗi ENUM mới
        $valuesString = "'" . implode("','", $currentValues) . "'";

        // ALTER table để update ENUM
        DB::statement("
            ALTER TABLE orders 
            MODIFY status ENUM($valuesString) NOT NULL
        ");
    }

    public function down()
    {
        // Khi rollback: xóa returning, returned

        $enumValues = DB::select("
            SHOW COLUMNS FROM orders LIKE 'status'
        ")[0]->Type;

        preg_match("/^enum\((.*)\)$/", $enumValues, $matches);
        $currentValues = array_map(function ($value) {
            return trim($value, "'");
        }, explode(",", $matches[1]));

        // Xóa 2 value mới
        $filtered = array_filter($currentValues, function ($v) {
            return $v !== 'returning' && $v !== 'returned';
        });

        $valuesString = "'" . implode("','", $filtered) . "'";

        DB::statement("
            ALTER TABLE orders 
            MODIFY status ENUM($valuesString) NOT NULL
        ");
    }
};
