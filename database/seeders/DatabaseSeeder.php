<?php

namespace Database\Seeders;

use App\Models\Driver\DriverProfile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Carbon\Carbon;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        // Data mẫu cho bảng users
        // User::factory()->admin()->create();
        // User::factory()->driver()->create();
        // User::factory()->customer()->create();

       // Thông tin bưu cục Đặng Thùy Trâm
        $hubData = [
            'post_office_id' => '11564316606',
            'hub_latitude' => '21.04393060',
            'hub_longitude' => '105.78605370',
            'hub_address' => 'Đường Đặng Thùy Trâm',
        ];

        // 1️.Tạo tài khoản Hub
        $hubUser = User::firstOrCreate(
            ['email' => 'hub@example.com'],
            [
                'phone' => '0910000001',
                'full_name' => 'Hub Main',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        // 2.Tạo Hub tương ứng
        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => $hubData['post_office_id'],
                'hub_latitude' => $hubData['hub_latitude'],
                'hub_longitude' => $hubData['hub_longitude'],
                'hub_address' => $hubData['hub_address'],
            ]
        );


        // 4️. Tạo 10 tài khoản tài xế (driver)
        for ($i = 1; $i <= 10; $i++) {
            $email = "driver{$i}@example.com";
            $phone = "092000000{$i}";
            $fullName = "Driver $i";

            // Tạo tài khoản user cho driver
            $driverUser = User::firstOrCreate(
                ['email' => $email],
                [
                    'phone' => $phone,
                    'full_name' => $fullName,
                    'role' => 'driver',
                    'status' => 'active',
                    'password_hash' => Hash::make('123456'),
                ]
            );

            // Tạo hồ sơ tài xế tương ứng
            DriverProfile::updateOrCreate(
                ['user_id' => $driverUser->id],
                [
                    'full_name' => $fullName,
                    'email' => $email,
                    'phone' => $phone,
                    'province_code' => 1,
                    'post_office_id' => $hubData['post_office_id'],
                    'post_office_name' => 'Bưu cục Đặng Thùy Trâm',
                    'post_office_address' => $hubData['hub_address'],
                    'post_office_lat' => $hubData['hub_latitude'],
                    'post_office_lng' => $hubData['hub_longitude'],
                    'post_office_phone' => '0241234567',
                    'vehicle_type' => 'Xe máy',
                    'license_number' => "$i$i$i$i$i$i$i$i",
                    'license_image' => 'license_image.png',
                    'identity_image' => 'identity_image.png',
                    'experience' => rand(1, 5) . ' năm',
                    'status' => 'approved',
                    'approved_at' => Carbon::now(),
                ]
            );
        }
    }
}
