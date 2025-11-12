<?php

namespace Database\Seeders;

use App\Models\Customer\Dashboard\Accounts\UserInfo;
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

       $hubData = [
            'post_office_id' => '11564316606',
            'hub_latitude' => '21.04393060',
            'hub_longitude' => '105.78605370',
            'hub_address' => 'Đường Đặng Thùy Trâm',
        ];

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

        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => $hubData['post_office_id'],
                'hub_latitude' => $hubData['hub_latitude'],
                'hub_longitude' => $hubData['hub_longitude'],
                'hub_address' => $hubData['hub_address'],
            ]
        );

        // =======================
        // 2️⃣ Data 10 địa chỉ tài xế
        // =======================
        $driverAddresses = [
            [
                'full_address' => '61 Ng. 2 Đ. Phan Bá Vành, Cầu Diễn, Bắc Từ Liêm, Hà Nội, Vietnam',
                'latitude' => 21.0488487,
                'longitude' => 105.7712802,
            ],
            [
                'full_address' => '26/Đường Phú Diễn/50 Ngh. 193/220 Đ. Phú Diễn, Làng Phú Diễn, Bắc Từ Liêm, Hà Nội, Vietnam',
                'latitude' => 21.043306,
                'longitude' => 105.75788,
            ],
            [
                'full_address' => 'Ngách 326/14, Cổ Nhuế, Bắc Từ Liêm, Hà Nội, Vietnam',
                'latitude' => 21.0722364,
                'longitude' => 105.7708217,
            ],
            [
                'full_address' => 'P. Hoàng Quán Chi, Dịch Vọng, Cầu Giấy, Hà Nội, Vietnam',
                'latitude' => 21.0291808,
                'longitude' => 105.7929987,
            ],
            [
                'full_address' => '973 Phúc Diễn, Tây Mỗ, Nam Từ Liêm, Hà Nội, Vietnam',
                'latitude' => 21.0210051,
                'longitude' => 105.7542671,
            ],
            [
                'full_address' => '76/52 An Dương, Tổ Dân Phố Số 31, Tây Hồ, Hà Nội, Vietnam',
                'latitude' => 21.0537953,
                'longitude' => 105.8418034,
            ],
            [
                'full_address' => '186 P. Tân Phong, Thuỵ Phương, Bắc Từ Liêm, Hà Nội, Vietnam',
                'latitude' => 21.074882,
                'longitude' => 105.7637406,
            ],
            [
                'full_address' => '187 P. Khâm Thiên, Thổ Quan, Đống Đa, Hà Nội, Vietnam',
                'latitude' => 21.0189543,
                'longitude' => 105.8344767,
            ],
            [
                'full_address' => '246 Chiến Thắng, P. Văn Quán, Thanh Trì, Hà Nội 10000, Vietnam',
                'latitude' => 20.9773203,
                'longitude' => 105.7976272,
            ],
            [
                'full_address' => '79 Cầu Giấy, Yên Hoà, Cầu Giấy, Hà Nội, Vietnam',
                'latitude' => 21.0193886,
                'longitude' => 105.7864842,
            ],
        ];

        // =======================
        // 3️. Tạo 10 tài khoản tài xế + user_info
        // =======================
        for ($i = 1; $i <= 10; $i++) {
            $email = "driver{$i}@example.com";
            $phone = "092000000{$i}";
            $fullName = "Driver $i";

            // Tạo tài khoản user
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

            // Hồ sơ tài xế
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

            // Thêm thông tin chi tiết user_info
            $addr = $driverAddresses[$i - 1];
            UserInfo::updateOrCreate(
                ['user_id' => $driverUser->id],
                [
                    'national_id' => '0123456789' . $i,
                    'tax_code' => 'TX' . str_pad($i, 4, '0', STR_PAD_LEFT),
                    'date_of_birth' => '1995-01-0' . (($i % 9) + 1),
                    'full_address' => $addr['full_address'],
                    'address_detail' => 'Khu vực giao hàng chính',
                    'latitude' => $addr['latitude'],
                    'longitude' => $addr['longitude'],
                    'province_code' => null,
                    'district_code' => null,
                    'ward_code' => null,
                ]
            );
        }
    }
}
