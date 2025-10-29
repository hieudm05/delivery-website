<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('post_offices', function (Blueprint $table) {
            if (!Schema::hasColumn('post_offices', 'code')) {
                $table->string('code')->nullable()->unique()->after('id');
            }
            if (!Schema::hasColumn('post_offices', 'phone')) {
                $table->string('phone')->nullable()->after('name');
            }
            if (!Schema::hasColumn('post_offices', 'province_code')) {
                $table->string('province_code')->nullable()->after('address');
            }
            if (!Schema::hasColumn('post_offices', 'district_code')) {
                $table->string('district_code')->nullable();
            }
            if (!Schema::hasColumn('post_offices', 'ward_code')) {
                $table->string('ward_code')->nullable();
            }
            if (!Schema::hasColumn('post_offices', 'lat')) {
                $table->decimal('lat', 10, 7)->nullable();
            }
            if (!Schema::hasColumn('post_offices', 'lng')) {
                $table->decimal('lng', 10, 7)->nullable();
            }
            if (!Schema::hasColumn('post_offices', 'meta')) {
                $table->json('meta')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('post_offices', function (Blueprint $table) {
            $table->dropColumn(['code','phone','province_code','district_code','ward_code','lat','lng','meta']);
        });
    }
};
