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
        // Data mẫu cho bảng users
        // User::factory()->admin()->create();
        // User::factory()->driver()->create();
        // User::factory()->customer()->create();

        //    $hubData = [
        //         'post_office_id' => '11564316606',
        //         'hub_latitude' => '21.04393060',
        //         'hub_longitude' => '105.78605370',
        //         'hub_address' => 'Đường Đặng Thùy Trâm',
        //     ];

        //     $hubUser = User::firstOrCreate(
        //         ['email' => 'hub@example.com'],
        //         [
        //             'phone' => '0910000001',
        //             'full_name' => 'Hub Main',
        //             'role' => 'hub',
        //             'status' => 1,
        //             'password_hash' => Hash::make('123456'),
        //         ]
        //     );

        //     DB::table('hubs')->updateOrInsert(
        //         ['user_id' => $hubUser->id],
        //         [
        //             'post_office_id' => $hubData['post_office_id'],
        //             'hub_latitude' => $hubData['hub_latitude'],
        //             'hub_longitude' => $hubData['hub_longitude'],
        //             'hub_address' => $hubData['hub_address'],
        //         ]
        //     );

        //     // =======================
        //     // 2️⃣ Data 10 địa chỉ tài xế
        //     // =======================
        //     $driverAddresses = [
        //         [
        //             'full_address' => '61 Ng. 2 Đ. Phan Bá Vành, Cầu Diễn, Bắc Từ Liêm, Hà Nội, Vietnam',
        //             'latitude' => 21.0488487,
        //             'longitude' => 105.7712802,
        //         ],
        //         [
        //             'full_address' => '26/Đường Phú Diễn/50 Ngh. 193/220 Đ. Phú Diễn, Làng Phú Diễn, Bắc Từ Liêm, Hà Nội, Vietnam',
        //             'latitude' => 21.043306,
        //             'longitude' => 105.75788,
        //         ],
        //         [
        //             'full_address' => 'Ngách 326/14, Cổ Nhuế, Bắc Từ Liêm, Hà Nội, Vietnam',
        //             'latitude' => 21.0722364,
        //             'longitude' => 105.7708217,
        //         ],
        //         [
        //             'full_address' => 'P. Hoàng Quán Chi, Dịch Vọng, Cầu Giấy, Hà Nội, Vietnam',
        //             'latitude' => 21.0291808,
        //             'longitude' => 105.7929987,
        //         ],
        //         [
        //             'full_address' => '973 Phúc Diễn, Tây Mỗ, Nam Từ Liêm, Hà Nội, Vietnam',
        //             'latitude' => 21.0210051,
        //             'longitude' => 105.7542671,
        //         ],
        //         [
        //             'full_address' => '76/52 An Dương, Tổ Dân Phố Số 31, Tây Hồ, Hà Nội, Vietnam',
        //             'latitude' => 21.0537953,
        //             'longitude' => 105.8418034,
        //         ],
        //         [
        //             'full_address' => '186 P. Tân Phong, Thuỵ Phương, Bắc Từ Liêm, Hà Nội, Vietnam',
        //             'latitude' => 21.074882,
        //             'longitude' => 105.7637406,
        //         ],
        //         [
        //             'full_address' => '187 P. Khâm Thiên, Thổ Quan, Đống Đa, Hà Nội, Vietnam',
        //             'latitude' => 21.0189543,
        //             'longitude' => 105.8344767,
        //         ],
        //         [
        //             'full_address' => '246 Chiến Thắng, P. Văn Quán, Thanh Trì, Hà Nội 10000, Vietnam',
        //             'latitude' => 20.9773203,
        //             'longitude' => 105.7976272,
        //         ],
        //         [
        //             'full_address' => '79 Cầu Giấy, Yên Hoà, Cầu Giấy, Hà Nội, Vietnam',
        //             'latitude' => 21.0193886,
        //             'longitude' => 105.7864842,
        //         ],
        //     ];

        //     // =======================
        //     // 3️. Tạo 10 tài khoản tài xế + user_info
        //     // =======================
        //     for ($i = 1; $i <= 10; $i++) {
        //         $email = "driver{$i}@example.com";
        //         $phone = "092000000{$i}";
        //         $fullName = "Driver $i";

        //         // Tạo tài khoản user
        //         $driverUser = User::firstOrCreate(
        //             ['email' => $email],
        //             [
        //                 'phone' => $phone,
        //                 'full_name' => $fullName,
        //                 'role' => 'driver',
        //                 'status' => 'active',
        //                 'password_hash' => Hash::make('123456'),
        //             ]
        //         );

        //         // Hồ sơ tài xế
        //         DriverProfile::updateOrCreate(
        //             ['user_id' => $driverUser->id],
        //             [
        //                 'full_name' => $fullName,
        //                 'email' => $email,
        //                 'phone' => $phone,
        //                 'province_code' => 1,
        //                 'post_office_id' => $hubData['post_office_id'],
        //                 'post_office_name' => 'Bưu cục Đặng Thùy Trâm',
        //                 'post_office_address' => $hubData['hub_address'],
        //                 'post_office_lat' => $hubData['hub_latitude'],
        //                 'post_office_lng' => $hubData['hub_longitude'],
        //                 'post_office_phone' => '0241234567',
        //                 'vehicle_type' => 'Xe máy',
        //                 'license_number' => "$i$i$i$i$i$i$i$i",
        //                 'license_image' => 'license_image.png',
        //                 'identity_image' => 'identity_image.png',
        //                 'experience' => rand(1, 5) . ' năm',
        //                 'status' => 'approved',
        //                 'approved_at' => Carbon::now(),
        //             ]
        //         );

        //         // Thêm thông tin chi tiết user_info
        //         $addr = $driverAddresses[$i - 1];
        //         UserInfo::updateOrCreate(
        //             ['user_id' => $driverUser->id],
        //             [
        //                 'national_id' => '0123456789' . $i,
        //                 'tax_code' => 'TX' . str_pad($i, 4, '0', STR_PAD_LEFT),
        //                 'date_of_birth' => '1995-01-0' . (($i % 9) + 1),
        //                 'full_address' => $addr['full_address'],
        //                 'address_detail' => 'Khu vực giao hàng chính',
        //                 'latitude' => $addr['latitude'],
        //                 'longitude' => $addr['longitude'],
        //                 'province_code' => null,
        //                 'district_code' => null,
        //                 'ward_code' => null,
        //             ]
        //         );
        //     }

        // ════════════════════════════════════════════
// HUB: Bưu Cục Giải Phóng
// ID: 1755806889 | Tọa độ: (20.98325, 105.841459)
// ════════════════════════════════════════════

        // Tạo user hub
        $hubUser = User::firstOrCreate(
            ['email' => 'hub_1755806889@example.com'],
            [
                'phone' => '024' . str_pad('1755806889', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu Cục Giải Phóng',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        // Tạo hub record
        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '1755806889',
                'hub_latitude' => '20.98325',
                'hub_longitude' => '105.841459',
                'hub_address' => 'Đường Giải Phóng',
            ]
        );

        // ════════════════════════════════════════════
// 3️⃣ TÀI XẾ CHO HUB: Bưu Cục Giải Phóng
// ════════════════════════════════════════════

        $driverAddresses_1755806889 = [
            [
                'full_address' => 'Phố Triệu Việt Vương',
                'latitude' => 21.01472,
                'longitude' => 105.850547,
            ],
            [
                'full_address' => 'Phố Phạm Ngọc Thạch',
                'latitude' => 21.008441,
                'longitude' => 105.834301,
            ],
            [
                'full_address' => 'restaurant',
                'latitude' => 21.023669,
                'longitude' => 105.855517,
            ],
        ];

        // Tạo 3 tài khoản tài xế cho hub Bưu Cục Giải Phóng
        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_1755806889_{$i}@example.com";
            $phone = "092806889{$i}";
            $fullName = "Driver Bưu Cục Giải Phóng #$i";

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

            DriverProfile::updateOrCreate(
                ['user_id' => $driverUser->id],
                [
                    'full_name' => $fullName,
                    'email' => $email,
                    'phone' => $phone,
                    'province_code' => 1,
                    'post_office_id' => '1755806889',
                    'post_office_name' => 'Bưu Cục Giải Phóng',
                    'post_office_address' => 'Đường Giải Phóng',
                    'post_office_lat' => '20.98325',
                    'post_office_lng' => '105.841459',
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

            $addr = $driverAddresses_1755806889[$i - 1];
            UserInfo::updateOrCreate(
                ['user_id' => $driverUser->id],
                [
                    'national_id' => '1755806889' . str_pad($i, 5, '0', STR_PAD_LEFT),
                    'tax_code' => 'TX1755806889' . str_pad($i, 3, '0', STR_PAD_LEFT),
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

        // ════════════════════════════════════════════
// HUB: Bưu Điện Cầu Giấy
// ID: 2545040241 | Tọa độ: (21.032534, 105.798427)
// ════════════════════════════════════════════

        // Tạo user hub
        $hubUser = User::firstOrCreate(
            ['email' => 'hub_2545040241@example.com'],
            [
                'phone' => '024' . str_pad('2545040241', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu Điện Cầu Giấy',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        // Tạo hub record
        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '2545040241',
                'hub_latitude' => '21.032534',
                'hub_longitude' => '105.798427',
                'hub_address' => 'Đường Cầu Giấy',
            ]
        );

        // ════════════════════════════════════════════
// 3️⃣ TÀI XẾ CHO HUB: Bưu Điện Cầu Giấy
// ════════════════════════════════════════════

        $driverAddresses_2545040241 = [
            [
                'full_address' => 'Phố Quảng Bá',
                'latitude' => 21.069992,
                'longitude' => 105.822858,
            ],
            [
                'full_address' => 'Street Sushi',
                'latitude' => 21.037671,
                'longitude' => 105.809552,
            ],
            [
                'full_address' => 'Phố Châu Long',
                'latitude' => 21.045646,
                'longitude' => 105.842715,
            ],
        ];

        // Tạo 3 tài khoản tài xế cho hub Bưu Điện Cầu Giấy
        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_2545040241_{$i}@example.com";
            $phone = "092040241{$i}";
            $fullName = "Driver Bưu Điện Cầu Giấy #$i";

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

            DriverProfile::updateOrCreate(
                ['user_id' => $driverUser->id],
                [
                    'full_name' => $fullName,
                    'email' => $email,
                    'phone' => $phone,
                    'province_code' => 1,
                    'post_office_id' => '2545040241',
                    'post_office_name' => 'Bưu Điện Cầu Giấy',
                    'post_office_address' => 'Đường Cầu Giấy',
                    'post_office_lat' => '21.032534',
                    'post_office_lng' => '105.798427',
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

            $addr = $driverAddresses_2545040241[$i - 1];
            UserInfo::updateOrCreate(
                ['user_id' => $driverUser->id],
                [
                    'national_id' => '2545040241' . str_pad($i, 5, '0', STR_PAD_LEFT),
                    'tax_code' => 'TX2545040241' . str_pad($i, 3, '0', STR_PAD_LEFT),
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

        // ════════════════════════════════════════════
// HUB: Bưu điện Quốc tế
// ID: 2810708201 | Tọa độ: (21.026171, 105.853613)
// ════════════════════════════════════════════

        // Tạo user hub
        $hubUser = User::firstOrCreate(
            ['email' => 'hub_2810708201@example.com'],
            [
                'phone' => '024' . str_pad('2810708201', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu điện Quốc tế',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        // Tạo hub record
        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '2810708201',
                'hub_latitude' => '21.026171',
                'hub_longitude' => '105.853613',
                'hub_address' => 'Phố Đinh Tiên Hoàng',
            ]
        );

        // ════════════════════════════════════════════
// 3️⃣ TÀI XẾ CHO HUB: Bưu điện Quốc tế
// ════════════════════════════════════════════

        $driverAddresses_2810708201 = [
            [
                'full_address' => 'VIB',
                'latitude' => 21.017638,
                'longitude' => 105.847396,
            ],
            [
                'full_address' => 'Nhà Hàng Hanoi Tan Tan',
                'latitude' => 21.026006,
                'longitude' => 105.850713,
            ],
            [
                'full_address' => 'Nhà Hàng Kaiser Kaffee',
                'latitude' => 21.024023,
                'longitude' => 105.850997,
            ],
        ];

        // Tạo 3 tài khoản tài xế cho hub Bưu điện Quốc tế
        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_2810708201_{$i}@example.com";
            $phone = "092708201{$i}";
            $fullName = "Driver Bưu điện Quốc tế #$i";

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

            DriverProfile::updateOrCreate(
                ['user_id' => $driverUser->id],
                [
                    'full_name' => $fullName,
                    'email' => $email,
                    'phone' => $phone,
                    'province_code' => 1,
                    'post_office_id' => '2810708201',
                    'post_office_name' => 'Bưu điện Quốc tế',
                    'post_office_address' => 'Phố Đinh Tiên Hoàng',
                    'post_office_lat' => '21.026171',
                    'post_office_lng' => '105.853613',
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

            $addr = $driverAddresses_2810708201[$i - 1];
            UserInfo::updateOrCreate(
                ['user_id' => $driverUser->id],
                [
                    'national_id' => '2810708201' . str_pad($i, 5, '0', STR_PAD_LEFT),
                    'tax_code' => 'TX2810708201' . str_pad($i, 3, '0', STR_PAD_LEFT),
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

        // ════════════════════════════════════════════
// HUB: Bưu điện Lương Văn Can
// ID: 4406493092 | Tọa độ: (21.031426, 105.850914)
// ════════════════════════════════════════════

        // Tạo user hub
        $hubUser = User::firstOrCreate(
            ['email' => 'hub_4406493092@example.com'],
            [
                'phone' => '024' . str_pad('4406493092', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu điện Lương Văn Can',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        // Tạo hub record
        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '4406493092',
                'hub_latitude' => '21.031426',
                'hub_longitude' => '105.850914',
                'hub_address' => 'Phố Lương Văn Can',
            ]
        );

        // ════════════════════════════════════════════
// 3️⃣ TÀI XẾ CHO HUB: Bưu điện Lương Văn Can
// ════════════════════════════════════════════

        $driverAddresses_4406493092 = [
            [
                'full_address' => 'HDBank',
                'latitude' => 21.020427,
                'longitude' => 105.854362,
            ],
            [
                'full_address' => 'Cà Phê L\'étage',
                'latitude' => 21.025481,
                'longitude' => 105.852819,
            ],
            [
                'full_address' => 'Phố Hoè Nhai',
                'latitude' => 21.042304,
                'longitude' => 105.847836,
            ],
        ];

        // Tạo 3 tài khoản tài xế cho hub Bưu điện Lương Văn Can
        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_4406493092_{$i}@example.com";
            $phone = "092493092{$i}";
            $fullName = "Driver Bưu điện Lương Văn Can #$i";

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

            DriverProfile::updateOrCreate(
                ['user_id' => $driverUser->id],
                [
                    'full_name' => $fullName,
                    'email' => $email,
                    'phone' => $phone,
                    'province_code' => 1,
                    'post_office_id' => '4406493092',
                    'post_office_name' => 'Bưu điện Lương Văn Can',
                    'post_office_address' => 'Phố Lương Văn Can',
                    'post_office_lat' => '21.031426',
                    'post_office_lng' => '105.850914',
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

            $addr = $driverAddresses_4406493092[$i - 1];
            UserInfo::updateOrCreate(
                ['user_id' => $driverUser->id],
                [
                    'national_id' => '4406493092' . str_pad($i, 5, '0', STR_PAD_LEFT),
                    'tax_code' => 'TX4406493092' . str_pad($i, 3, '0', STR_PAD_LEFT),
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

        // ════════════════════════════════════════════
// HUB: VIETTEL POST
// ID: 4536058095 | Tọa độ: (21.038059, 105.801133)
// ════════════════════════════════════════════

        // Tạo user hub
        $hubUser = User::firstOrCreate(
            ['email' => 'hub_4536058095@example.com'],
            [
                'phone' => '024' . str_pad('4536058095', 7, '0', STR_PAD_LEFT),
                'full_name' => 'VIETTEL POST',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        // Tạo hub record
        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '4536058095',
                'hub_latitude' => '21.038059',
                'hub_longitude' => '105.801133',
                'hub_address' => 'Ngõ 118 Nguyễn Khánh Toàn',
            ]
        );

        // ════════════════════════════════════════════
// 3️⃣ TÀI XẾ CHO HUB: VIETTEL POST
// ════════════════════════════════════════════

        $driverAddresses_4536058095 = [
            [
                'full_address' => 'Cà Phê Lovegan Kitchen',
                'latitude' => 21.038764,
                'longitude' => 105.828093,
            ],
            [
                'full_address' => 'Mixue',
                'latitude' => 21.029146,
                'longitude' => 105.844792,
            ],
            [
                'full_address' => 'bank',
                'latitude' => 21.038177,
                'longitude' => 105.847744,
            ],
        ];

        // Tạo 3 tài khoản tài xế cho hub VIETTEL POST
        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_4536058095_{$i}@example.com";
            $phone = "092058095{$i}";
            $fullName = "Driver VIETTEL POST #$i";

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

            DriverProfile::updateOrCreate(
                ['user_id' => $driverUser->id],
                [
                    'full_name' => $fullName,
                    'email' => $email,
                    'phone' => $phone,
                    'province_code' => 1,
                    'post_office_id' => '4536058095',
                    'post_office_name' => 'VIETTEL POST',
                    'post_office_address' => 'Ngõ 118 Nguyễn Khánh Toàn',
                    'post_office_lat' => '21.038059',
                    'post_office_lng' => '105.801133',
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

            $addr = $driverAddresses_4536058095[$i - 1];
            UserInfo::updateOrCreate(
                ['user_id' => $driverUser->id],
                [
                    'national_id' => '4536058095' . str_pad($i, 5, '0', STR_PAD_LEFT),
                    'tax_code' => 'TX4536058095' . str_pad($i, 3, '0', STR_PAD_LEFT),
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

        // ════════════════════════════════════════════
// HUB: Bưu điện Tràng Tiền
// ID: 4571873590 | Tọa độ: (21.02542, 105.853585)
// ════════════════════════════════════════════

        // Tạo user hub
        $hubUser = User::firstOrCreate(
            ['email' => 'hub_4571873590@example.com'],
            [
                'phone' => '024' . str_pad('4571873590', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu điện Tràng Tiền',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        // Tạo hub record
        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '4571873590',
                'hub_latitude' => '21.02542',
                'hub_longitude' => '105.853585',
                'hub_address' => 'Phố Tràng Tiền',
            ]
        );

        // ════════════════════════════════════════════
// 3️⃣ TÀI XẾ CHO HUB: Bưu điện Tràng Tiền
// ════════════════════════════════════════════

        $driverAddresses_4571873590 = [
            [
                'full_address' => 'cafe',
                'latitude' => 21.017791,
                'longitude' => 105.860273,
            ],
            [
                'full_address' => 'Nhà Lê',
                'latitude' => 21.030454,
                'longitude' => 105.844516,
            ],
            [
                'full_address' => 'Phố Bà Triệu',
                'latitude' => 21.01243,
                'longitude' => 105.849232,
            ],
        ];

        // Tạo 3 tài khoản tài xế cho hub Bưu điện Tràng Tiền
        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_4571873590_{$i}@example.com";
            $phone = "092873590{$i}";
            $fullName = "Driver Bưu điện Tràng Tiền #$i";

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

            DriverProfile::updateOrCreate(
                ['user_id' => $driverUser->id],
                [
                    'full_name' => $fullName,
                    'email' => $email,
                    'phone' => $phone,
                    'province_code' => 1,
                    'post_office_id' => '4571873590',
                    'post_office_name' => 'Bưu điện Tràng Tiền',
                    'post_office_address' => 'Phố Tràng Tiền',
                    'post_office_lat' => '21.02542',
                    'post_office_lng' => '105.853585',
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

            $addr = $driverAddresses_4571873590[$i - 1];
            UserInfo::updateOrCreate(
                ['user_id' => $driverUser->id],
                [
                    'national_id' => '4571873590' . str_pad($i, 5, '0', STR_PAD_LEFT),
                    'tax_code' => 'TX4571873590' . str_pad($i, 3, '0', STR_PAD_LEFT),
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

        // ════════════════════════════════════════════
// HUB: Bưu Cục Hàng Cót
// ID: 4734482024 | Tọa độ: (21.037995, 105.846254)
// ════════════════════════════════════════════

        // Tạo user hub
        $hubUser = User::firstOrCreate(
            ['email' => 'hub_4734482024@example.com'],
            [
                'phone' => '024' . str_pad('4734482024', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu Cục Hàng Cót',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        // Tạo hub record
        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '4734482024',
                'hub_latitude' => '21.037995',
                'hub_longitude' => '105.846254',
                'hub_address' => 'Phố Phùng Hưng',
            ]
        );

        // ════════════════════════════════════════════
// 3️⃣ TÀI XẾ CHO HUB: Bưu Cục Hàng Cót
// ════════════════════════════════════════════

        $driverAddresses_4734482024 = [
            [
                'full_address' => 'Nhà Hàng Hanoi Tan Tan',
                'latitude' => 21.025506,
                'longitude' => 105.852525,
            ],
            [
                'full_address' => 'Đường Nguyễn Văn Cừ',
                'latitude' => 21.046669,
                'longitude' => 105.878312,
            ],
            [
                'full_address' => 'Phố Bùi Thị Xuân',
                'latitude' => 21.013997,
                'longitude' => 105.849991,
            ],
        ];

        // Tạo 3 tài khoản tài xế cho hub Bưu Cục Hàng Cót
        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_4734482024_{$i}@example.com";
            $phone = "092482024{$i}";
            $fullName = "Driver Bưu Cục Hàng Cót #$i";

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

            DriverProfile::updateOrCreate(
                ['user_id' => $driverUser->id],
                [
                    'full_name' => $fullName,
                    'email' => $email,
                    'phone' => $phone,
                    'province_code' => 1,
                    'post_office_id' => '4734482024',
                    'post_office_name' => 'Bưu Cục Hàng Cót',
                    'post_office_address' => 'Phố Phùng Hưng',
                    'post_office_lat' => '21.037995',
                    'post_office_lng' => '105.846254',
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

            $addr = $driverAddresses_4734482024[$i - 1];
            UserInfo::updateOrCreate(
                ['user_id' => $driverUser->id],
                [
                    'national_id' => '4734482024' . str_pad($i, 5, '0', STR_PAD_LEFT),
                    'tax_code' => 'TX4734482024' . str_pad($i, 3, '0', STR_PAD_LEFT),
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

        // ════════════════════════════════════════════
// HUB: Bưu Cục EMS Tràng Tiền
// ID: 5239480022 | Tọa độ: (21.030761, 105.856039)
// ════════════════════════════════════════════

        // Tạo user hub
        $hubUser = User::firstOrCreate(
            ['email' => 'hub_5239480022@example.com'],
            [
                'phone' => '024' . str_pad('5239480022', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu Cục EMS Tràng Tiền',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        // Tạo hub record
        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '5239480022',
                'hub_latitude' => '21.030761',
                'hub_longitude' => '105.856039',
                'hub_address' => 'Phố Hàng Vôi',
            ]
        );

        // ════════════════════════════════════════════
// 3️⃣ TÀI XẾ CHO HUB: Bưu Cục EMS Tràng Tiền
// ════════════════════════════════════════════

        $driverAddresses_5239480022 = [
            [
                'full_address' => 'restaurant',
                'latitude' => 21.027794,
                'longitude' => 105.849923,
            ],
            [
                'full_address' => 'restaurant',
                'latitude' => 21.030166,
                'longitude' => 105.848238,
            ],
            [
                'full_address' => 'Phố Trần Nhân Tông',
                'latitude' => 21.017111,
                'longitude' => 105.84875,
            ],
        ];

        // Tạo 3 tài khoản tài xế cho hub Bưu Cục EMS Tràng Tiền
        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_5239480022_{$i}@example.com";
            $phone = "092480022{$i}";
            $fullName = "Driver Bưu Cục EMS Tràng Tiền #$i";

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

            DriverProfile::updateOrCreate(
                ['user_id' => $driverUser->id],
                [
                    'full_name' => $fullName,
                    'email' => $email,
                    'phone' => $phone,
                    'province_code' => 1,
                    'post_office_id' => '5239480022',
                    'post_office_name' => 'Bưu Cục EMS Tràng Tiền',
                    'post_office_address' => 'Phố Hàng Vôi',
                    'post_office_lat' => '21.030761',
                    'post_office_lng' => '105.856039',
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

            $addr = $driverAddresses_5239480022[$i - 1];
            UserInfo::updateOrCreate(
                ['user_id' => $driverUser->id],
                [
                    'national_id' => '5239480022' . str_pad($i, 5, '0', STR_PAD_LEFT),
                    'tax_code' => 'TX5239480022' . str_pad($i, 3, '0', STR_PAD_LEFT),
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

        // ════════════════════════════════════════════
// HUB: Bưu Cục Giảng Võ
// ID: 5485727933 | Tọa độ: (21.026417, 105.822812)
// ════════════════════════════════════════════

        // Tạo user hub
        $hubUser = User::firstOrCreate(
            ['email' => 'hub_5485727933@example.com'],
            [
                'phone' => '024' . str_pad('5485727933', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu Cục Giảng Võ',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        // Tạo hub record
        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '5485727933',
                'hub_latitude' => '21.026417',
                'hub_longitude' => '105.822812',
                'hub_address' => 'Giảng Võ',
            ]
        );

        // ════════════════════════════════════════════
// 3️⃣ TÀI XẾ CHO HUB: Bưu Cục Giảng Võ
// ════════════════════════════════════════════

        $driverAddresses_5485727933 = [
            [
                'full_address' => 'Happy Brew Coffee',
                'latitude' => 21.035369,
                'longitude' => 105.810588,
            ],
            [
                'full_address' => 'Phố Trần Hưng Đạo',
                'latitude' => 21.022343,
                'longitude' => 105.846943,
            ],
            [
                'full_address' => 'Phố Quán Thánh',
                'latitude' => 21.040499,
                'longitude' => 105.845542,
            ],
        ];

        // Tạo 3 tài khoản tài xế cho hub Bưu Cục Giảng Võ
        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_5485727933_{$i}@example.com";
            $phone = "092727933{$i}";
            $fullName = "Driver Bưu Cục Giảng Võ #$i";

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

            DriverProfile::updateOrCreate(
                ['user_id' => $driverUser->id],
                [
                    'full_name' => $fullName,
                    'email' => $email,
                    'phone' => $phone,
                    'province_code' => 1,
                    'post_office_id' => '5485727933',
                    'post_office_name' => 'Bưu Cục Giảng Võ',
                    'post_office_address' => 'Giảng Võ',
                    'post_office_lat' => '21.026417',
                    'post_office_lng' => '105.822812',
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

            $addr = $driverAddresses_5485727933[$i - 1];
            UserInfo::updateOrCreate(
                ['user_id' => $driverUser->id],
                [
                    'national_id' => '5485727933' . str_pad($i, 5, '0', STR_PAD_LEFT),
                    'tax_code' => 'TX5485727933' . str_pad($i, 3, '0', STR_PAD_LEFT),
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

        // ════════════════════════════════════════════
// HUB: ViettelPost
// ID: 5485799797 | Tọa độ: (20.974034, 105.835782)
// ════════════════════════════════════════════

        // Tạo user hub
        $hubUser = User::firstOrCreate(
            ['email' => 'hub_5485799797@example.com'],
            [
                'phone' => '024' . str_pad('5485799797', 7, '0', STR_PAD_LEFT),
                'full_name' => 'ViettelPost',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        // Tạo hub record
        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '5485799797',
                'hub_latitude' => '20.974034',
                'hub_longitude' => '105.835782',
                'hub_address' => 'Nguyễn Cảnh Dị',
            ]
        );

        // ════════════════════════════════════════════
// 3️⃣ TÀI XẾ CHO HUB: ViettelPost
// ════════════════════════════════════════════

        $driverAddresses_5485799797 = [
            [
                'full_address' => 'Trương Định',
                'latitude' => 20.980513,
                'longitude' => 105.844762,
            ],
            [
                'full_address' => 'Cà Phê Quang',
                'latitude' => 21.004686,
                'longitude' => 105.854916,
            ],
            [
                'full_address' => 'Phố Triệu Việt Vương',
                'latitude' => 21.01399,
                'longitude' => 105.850581,
            ],
        ];

        // Tạo 3 tài khoản tài xế cho hub ViettelPost
        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_5485799797_{$i}@example.com";
            $phone = "092799797{$i}";
            $fullName = "Driver ViettelPost #$i";

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

            DriverProfile::updateOrCreate(
                ['user_id' => $driverUser->id],
                [
                    'full_name' => $fullName,
                    'email' => $email,
                    'phone' => $phone,
                    'province_code' => 1,
                    'post_office_id' => '5485799797',
                    'post_office_name' => 'ViettelPost',
                    'post_office_address' => 'Nguyễn Cảnh Dị',
                    'post_office_lat' => '20.974034',
                    'post_office_lng' => '105.835782',
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

            $addr = $driverAddresses_5485799797[$i - 1];
            UserInfo::updateOrCreate(
                ['user_id' => $driverUser->id],
                [
                    'national_id' => '5485799797' . str_pad($i, 5, '0', STR_PAD_LEFT),
                    'tax_code' => 'TX5485799797' . str_pad($i, 3, '0', STR_PAD_LEFT),
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

        // ════════════════════════════════════════════
// HUB: Bưu Điện Trung Tâm 4
// ID: 6707397286 | Tọa độ: (21.034851, 105.826192)
// ════════════════════════════════════════════

        // Tạo user hub
        $hubUser = User::firstOrCreate(
            ['email' => 'hub_6707397286@example.com'],
            [
                'phone' => '024' . str_pad('6707397286', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu Điện Trung Tâm 4',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        // Tạo hub record
        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '6707397286',
                'hub_latitude' => '21.034851',
                'hub_longitude' => '105.826192',
                'hub_address' => 'Đội Cấn',
            ]
        );

        // ════════════════════════════════════════════
// 3️⃣ TÀI XẾ CHO HUB: Bưu Điện Trung Tâm 4
// ════════════════════════════════════════════

        $driverAddresses_6707397286 = [
            [
                'full_address' => 'Cà Phê Doppio Ristretto',
                'latitude' => 21.058463,
                'longitude' => 105.830296,
            ],
            [
                'full_address' => 'Cháo sườn sụn',
                'latitude' => 21.047367,
                'longitude' => 105.811034,
            ],
            [
                'full_address' => 'restaurant',
                'latitude' => 21.023449,
                'longitude' => 105.847966,
            ],
        ];

        // Tạo 3 tài khoản tài xế cho hub Bưu Điện Trung Tâm 4
        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_6707397286_{$i}@example.com";
            $phone = "092397286{$i}";
            $fullName = "Driver Bưu Điện Trung Tâm 4 #$i";

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

            DriverProfile::updateOrCreate(
                ['user_id' => $driverUser->id],
                [
                    'full_name' => $fullName,
                    'email' => $email,
                    'phone' => $phone,
                    'province_code' => 1,
                    'post_office_id' => '6707397286',
                    'post_office_name' => 'Bưu Điện Trung Tâm 4',
                    'post_office_address' => 'Đội Cấn',
                    'post_office_lat' => '21.034851',
                    'post_office_lng' => '105.826192',
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

            $addr = $driverAddresses_6707397286[$i - 1];
            UserInfo::updateOrCreate(
                ['user_id' => $driverUser->id],
                [
                    'national_id' => '6707397286' . str_pad($i, 5, '0', STR_PAD_LEFT),
                    'tax_code' => 'TX6707397286' . str_pad($i, 3, '0', STR_PAD_LEFT),
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

        // ════════════════════════════════════════════
// HUB: Bưu điện Đồng Xuân
// ID: 7178787481 | Tọa độ: (21.03799, 105.848714)
// ════════════════════════════════════════════

        // Tạo user hub
        $hubUser = User::firstOrCreate(
            ['email' => 'hub_7178787481@example.com'],
            [
                'phone' => '024' . str_pad('7178787481', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu điện Đồng Xuân',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        // Tạo hub record
        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '7178787481',
                'hub_latitude' => '21.03799',
                'hub_longitude' => '105.848714',
                'hub_address' => 'Phố Đồng Xuân',
            ]
        );

        // ════════════════════════════════════════════
// 3️⃣ TÀI XẾ CHO HUB: Bưu điện Đồng Xuân
// ════════════════════════════════════════════

        $driverAddresses_7178787481 = [
            [
                'full_address' => 'cafe',
                'latitude' => 21.037255,
                'longitude' => 105.84636,
            ],
            [
                'full_address' => 'Cà Phê Mai',
                'latitude' => 21.019024,
                'longitude' => 105.848221,
            ],
            [
                'full_address' => 'Phố Hàng Cá',
                'latitude' => 21.036064,
                'longitude' => 105.848876,
            ],
        ];

        // Tạo 3 tài khoản tài xế cho hub Bưu điện Đồng Xuân
        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_7178787481_{$i}@example.com";
            $phone = "092787481{$i}";
            $fullName = "Driver Bưu điện Đồng Xuân #$i";

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

            DriverProfile::updateOrCreate(
                ['user_id' => $driverUser->id],
                [
                    'full_name' => $fullName,
                    'email' => $email,
                    'phone' => $phone,
                    'province_code' => 1,
                    'post_office_id' => '7178787481',
                    'post_office_name' => 'Bưu điện Đồng Xuân',
                    'post_office_address' => 'Phố Đồng Xuân',
                    'post_office_lat' => '21.03799',
                    'post_office_lng' => '105.848714',
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

            $addr = $driverAddresses_7178787481[$i - 1];
            UserInfo::updateOrCreate(
                ['user_id' => $driverUser->id],
                [
                    'national_id' => '7178787481' . str_pad($i, 5, '0', STR_PAD_LEFT),
                    'tax_code' => 'TX7178787481' . str_pad($i, 3, '0', STR_PAD_LEFT),
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

        // ════════════════════════════════════════════
// HUB: Bưu Điện Phụng Châu
// ID: 7409549830 | Tọa độ: (20.948898, 105.707044)
// ════════════════════════════════════════════

        // Tạo user hub
        $hubUser = User::firstOrCreate(
            ['email' => 'hub_7409549830@example.com'],
            [
                'phone' => '024' . str_pad('7409549830', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu Điện Phụng Châu',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        // Tạo hub record
        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '7409549830',
                'hub_latitude' => '20.948898',
                'hub_longitude' => '105.707044',
                'hub_address' => 'Phượng Nghĩa',
            ]
        );

        // ════════════════════════════════════════════
// 3️⃣ TÀI XẾ CHO HUB: Bưu Điện Phụng Châu
// ════════════════════════════════════════════

        $driverAddresses_7409549830 = [
            [
                'full_address' => 'restaurant',
                'latitude' => 20.963888,
                'longitude' => 105.749322,
            ],
            [
                'full_address' => 'restaurant',
                'latitude' => 20.9641,
                'longitude' => 105.747647,
            ],
            [
                'full_address' => 'Trường Trung học cơ sở ĐạI Thành',
                'latitude' => 20.965247,
                'longitude' => 105.708377,
            ],
        ];

        // Tạo 3 tài khoản tài xế cho hub Bưu Điện Phụng Châu
        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_7409549830_{$i}@example.com";
            $phone = "092549830{$i}";
            $fullName = "Driver Bưu Điện Phụng Châu #$i";

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

            DriverProfile::updateOrCreate(
                ['user_id' => $driverUser->id],
                [
                    'full_name' => $fullName,
                    'email' => $email,
                    'phone' => $phone,
                    'province_code' => 1,
                    'post_office_id' => '7409549830',
                    'post_office_name' => 'Bưu Điện Phụng Châu',
                    'post_office_address' => 'Phượng Nghĩa',
                    'post_office_lat' => '20.948898',
                    'post_office_lng' => '105.707044',
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

            $addr = $driverAddresses_7409549830[$i - 1];
            UserInfo::updateOrCreate(
                ['user_id' => $driverUser->id],
                [
                    'national_id' => '7409549830' . str_pad($i, 5, '0', STR_PAD_LEFT),
                    'tax_code' => 'TX7409549830' . str_pad($i, 3, '0', STR_PAD_LEFT),
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

        // ════════════════════════════════════════════
// HUB: Bưu điện Kim Liên
// ID: 7612528481 | Tọa độ: (21.006522, 105.835382)
// ════════════════════════════════════════════

        // Tạo user hub
        $hubUser = User::firstOrCreate(
            ['email' => 'hub_7612528481@example.com'],
            [
                'phone' => '024' . str_pad('7612528481', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu điện Kim Liên',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        // Tạo hub record
        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '7612528481',
                'hub_latitude' => '21.006522',
                'hub_longitude' => '105.835382',
                'hub_address' => 'Phố Lương Định Của',
            ]
        );

        // ════════════════════════════════════════════
// 3️⃣ TÀI XẾ CHO HUB: Bưu điện Kim Liên
// ════════════════════════════════════════════

        $driverAddresses_7612528481 = [
            [
                'full_address' => 'Phùng Hưng',
                'latitude' => 21.037972,
                'longitude' => 105.84622,
            ],
            [
                'full_address' => 'Nhà Hàng Classico',
                'latitude' => 21.025414,
                'longitude' => 105.84514,
            ],
            [
                'full_address' => 'restaurant',
                'latitude' => 21.030956,
                'longitude' => 105.854705,
            ],
        ];

        // Tạo 3 tài khoản tài xế cho hub Bưu điện Kim Liên
        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_7612528481_{$i}@example.com";
            $phone = "092528481{$i}";
            $fullName = "Driver Bưu điện Kim Liên #$i";

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

            DriverProfile::updateOrCreate(
                ['user_id' => $driverUser->id],
                [
                    'full_name' => $fullName,
                    'email' => $email,
                    'phone' => $phone,
                    'province_code' => 1,
                    'post_office_id' => '7612528481',
                    'post_office_name' => 'Bưu điện Kim Liên',
                    'post_office_address' => 'Phố Lương Định Của',
                    'post_office_lat' => '21.006522',
                    'post_office_lng' => '105.835382',
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

            $addr = $driverAddresses_7612528481[$i - 1];
            UserInfo::updateOrCreate(
                ['user_id' => $driverUser->id],
                [
                    'national_id' => '7612528481' . str_pad($i, 5, '0', STR_PAD_LEFT),
                    'tax_code' => 'TX7612528481' . str_pad($i, 3, '0', STR_PAD_LEFT),
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

        // ════════════════════════════════════════════
// HUB: Bưu Cục Giao Dịch EMS Hồng Hà
// ID: 7856211553 | Tọa độ: (21.032925, 105.856203)
// ════════════════════════════════════════════

        // Tạo user hub
        $hubUser = User::firstOrCreate(
            ['email' => 'hub_7856211553@example.com'],
            [
                'phone' => '024' . str_pad('7856211553', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu Cục Giao Dịch EMS Hồng Hà',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        // Tạo hub record
        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '7856211553',
                'hub_latitude' => '21.032925',
                'hub_longitude' => '105.856203',
                'hub_address' => 'Đường Hồng Hà',
            ]
        );

        // ════════════════════════════════════════════
// 3️⃣ TÀI XẾ CHO HUB: Bưu Cục Giao Dịch EMS Hồng Hà
// ════════════════════════════════════════════

        $driverAddresses_7856211553 = [
            [
                'full_address' => 'cafe',
                'latitude' => 21.062984,
                'longitude' => 105.828775,
            ],
            [
                'full_address' => 'Phố Mã Mây',
                'latitude' => 21.035982,
                'longitude' => 105.853038,
            ],
            [
                'full_address' => 'Phố Nguyễn Hữu Huân',
                'latitude' => 21.034357,
                'longitude' => 105.854493,
            ],
        ];

        // Tạo 3 tài khoản tài xế cho hub Bưu Cục Giao Dịch EMS Hồng Hà
        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_7856211553_{$i}@example.com";
            $phone = "092211553{$i}";
            $fullName = "Driver Bưu Cục Giao Dịch EMS Hồng Hà #$i";

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

            DriverProfile::updateOrCreate(
                ['user_id' => $driverUser->id],
                [
                    'full_name' => $fullName,
                    'email' => $email,
                    'phone' => $phone,
                    'province_code' => 1,
                    'post_office_id' => '7856211553',
                    'post_office_name' => 'Bưu Cục Giao Dịch EMS Hồng Hà',
                    'post_office_address' => 'Đường Hồng Hà',
                    'post_office_lat' => '21.032925',
                    'post_office_lng' => '105.856203',
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

            $addr = $driverAddresses_7856211553[$i - 1];
            UserInfo::updateOrCreate(
                ['user_id' => $driverUser->id],
                [
                    'national_id' => '7856211553' . str_pad($i, 5, '0', STR_PAD_LEFT),
                    'tax_code' => 'TX7856211553' . str_pad($i, 3, '0', STR_PAD_LEFT),
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

        // ════════════════════════════════════════════
// HUB: Bưu điện Cửa Nam
// ID: 7856211555 | Tọa độ: (21.02789, 105.842421)
// ════════════════════════════════════════════

        // Tạo user hub
        $hubUser = User::firstOrCreate(
            ['email' => 'hub_7856211555@example.com'],
            [
                'phone' => '024' . str_pad('7856211555', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu điện Cửa Nam',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        // Tạo hub record
        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '7856211555',
                'hub_latitude' => '21.02789',
                'hub_longitude' => '105.842421',
                'hub_address' => 'Phố Cửa Nam',
            ]
        );

        // ════════════════════════════════════════════
// 3️⃣ TÀI XẾ CHO HUB: Bưu điện Cửa Nam
// ════════════════════════════════════════════

        $driverAddresses_7856211555 = [
            [
                'full_address' => 'Thái Hà',
                'latitude' => 21.014991,
                'longitude' => 105.815457,
            ],
            [
                'full_address' => 'Gia Ngư',
                'latitude' => 21.033104,
                'longitude' => 105.852529,
            ],
            [
                'full_address' => 'Phố Quán Sứ',
                'latitude' => 21.025282,
                'longitude' => 105.845214,
            ],
        ];

        // Tạo 3 tài khoản tài xế cho hub Bưu điện Cửa Nam
        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_7856211555_{$i}@example.com";
            $phone = "092211555{$i}";
            $fullName = "Driver Bưu điện Cửa Nam #$i";

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

            DriverProfile::updateOrCreate(
                ['user_id' => $driverUser->id],
                [
                    'full_name' => $fullName,
                    'email' => $email,
                    'phone' => $phone,
                    'province_code' => 1,
                    'post_office_id' => '7856211555',
                    'post_office_name' => 'Bưu điện Cửa Nam',
                    'post_office_address' => 'Phố Cửa Nam',
                    'post_office_lat' => '21.02789',
                    'post_office_lng' => '105.842421',
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

            $addr = $driverAddresses_7856211555[$i - 1];
            UserInfo::updateOrCreate(
                ['user_id' => $driverUser->id],
                [
                    'national_id' => '7856211555' . str_pad($i, 5, '0', STR_PAD_LEFT),
                    'tax_code' => 'TX7856211555' . str_pad($i, 3, '0', STR_PAD_LEFT),
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

        // ════════════════════════════════════════════
// HUB: Bưu Điện Trần Khát Chân
// ID: 7856350176 | Tọa độ: (21.008429, 105.851003)
// ════════════════════════════════════════════

        // Tạo user hub
        $hubUser = User::firstOrCreate(
            ['email' => 'hub_7856350176@example.com'],
            [
                'phone' => '024' . str_pad('7856350176', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu Điện Trần Khát Chân',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        // Tạo hub record
        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '7856350176',
                'hub_latitude' => '21.008429',
                'hub_longitude' => '105.851003',
                'hub_address' => 'Đường Trần Khát Chân',
            ]
        );

        // ════════════════════════════════════════════
// 3️⃣ TÀI XẾ CHO HUB: Bưu Điện Trần Khát Chân
// ════════════════════════════════════════════

        $driverAddresses_7856350176 = [
            [
                'full_address' => 'pharmacy',
                'latitude' => 21.017339,
                'longitude' => 105.848575,
            ],
            [
                'full_address' => 'cafe',
                'latitude' => 21.0307,
                'longitude' => 105.845858,
            ],
            [
                'full_address' => 'Cà Phê Fruits With Jogurt',
                'latitude' => 21.003665,
                'longitude' => 105.849968,
            ],
        ];

        // Tạo 3 tài khoản tài xế cho hub Bưu Điện Trần Khát Chân
        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_7856350176_{$i}@example.com";
            $phone = "092350176{$i}";
            $fullName = "Driver Bưu Điện Trần Khát Chân #$i";

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

            DriverProfile::updateOrCreate(
                ['user_id' => $driverUser->id],
                [
                    'full_name' => $fullName,
                    'email' => $email,
                    'phone' => $phone,
                    'province_code' => 1,
                    'post_office_id' => '7856350176',
                    'post_office_name' => 'Bưu Điện Trần Khát Chân',
                    'post_office_address' => 'Đường Trần Khát Chân',
                    'post_office_lat' => '21.008429',
                    'post_office_lng' => '105.851003',
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

            $addr = $driverAddresses_7856350176[$i - 1];
            UserInfo::updateOrCreate(
                ['user_id' => $driverUser->id],
                [
                    'national_id' => '7856350176' . str_pad($i, 5, '0', STR_PAD_LEFT),
                    'tax_code' => 'TX7856350176' . str_pad($i, 3, '0', STR_PAD_LEFT),
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

        // ════════════════════════════════════════════
// HUB: Bưu Cục Giao Dịch EMS Hai Bà Trưng 1
// ID: 7856350177 | Tọa độ: (21.008913, 105.86433)
// ════════════════════════════════════════════

        // Tạo user hub
        $hubUser = User::firstOrCreate(
            ['email' => 'hub_7856350177@example.com'],
            [
                'phone' => '024' . str_pad('7856350177', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu Cục Giao Dịch EMS Hai Bà Trưng 1',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        // Tạo hub record
        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '7856350177',
                'hub_latitude' => '21.008913',
                'hub_longitude' => '105.86433',
                'hub_address' => 'Đường Trần Khát Chân',
            ]
        );

        // ════════════════════════════════════════════
// 3️⃣ TÀI XẾ CHO HUB: Bưu Cục Giao Dịch EMS Hai Bà Trưng 1
// ════════════════════════════════════════════

        $driverAddresses_7856350177 = [
            [
                'full_address' => 'Pho Luong Van Can',
                'latitude' => 21.032809,
                'longitude' => 105.850528,
            ],
            [
                'full_address' => 'Hàng Buồm',
                'latitude' => 21.035994,
                'longitude' => 105.851812,
            ],
            [
                'full_address' => 'Phố Văn Miếu',
                'latitude' => 21.029114,
                'longitude' => 105.836713,
            ],
        ];

        // Tạo 3 tài khoản tài xế cho hub Bưu Cục Giao Dịch EMS Hai Bà Trưng 1
        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_7856350177_{$i}@example.com";
            $phone = "092350177{$i}";
            $fullName = "Driver Bưu Cục Giao Dịch EMS Hai Bà Trưng 1 #$i";

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

            DriverProfile::updateOrCreate(
                ['user_id' => $driverUser->id],
                [
                    'full_name' => $fullName,
                    'email' => $email,
                    'phone' => $phone,
                    'province_code' => 1,
                    'post_office_id' => '7856350177',
                    'post_office_name' => 'Bưu Cục Giao Dịch EMS Hai Bà Trưng 1',
                    'post_office_address' => 'Đường Trần Khát Chân',
                    'post_office_lat' => '21.008913',
                    'post_office_lng' => '105.86433',
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

            $addr = $driverAddresses_7856350177[$i - 1];
            UserInfo::updateOrCreate(
                ['user_id' => $driverUser->id],
                [
                    'national_id' => '7856350177' . str_pad($i, 5, '0', STR_PAD_LEFT),
                    'tax_code' => 'TX7856350177' . str_pad($i, 3, '0', STR_PAD_LEFT),
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

        // ════════════════════════════════════════════
// HUB: Bưu Cục Vân Hồ
// ID: 7856383102 | Tọa độ: (21.010339, 105.846454)
// ════════════════════════════════════════════

        // Tạo user hub
        $hubUser = User::firstOrCreate(
            ['email' => 'hub_7856383102@example.com'],
            [
                'phone' => '024' . str_pad('7856383102', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu Cục Vân Hồ',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        // Tạo hub record
        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '7856383102',
                'hub_latitude' => '21.010339',
                'hub_longitude' => '105.846454',
                'hub_address' => 'Phố Vân Hồ 3',
            ]
        );

        // ════════════════════════════════════════════
// 3️⃣ TÀI XẾ CHO HUB: Bưu Cục Vân Hồ
// ════════════════════════════════════════════

        $driverAddresses_7856383102 = [
            [
                'full_address' => 'Quán Bún Bò Nam Bộ',
                'latitude' => 21.03208,
                'longitude' => 105.846922,
            ],
            [
                'full_address' => 'Phố Gầm Cầu',
                'latitude' => 21.039136,
                'longitude' => 105.848228,
            ],
            [
                'full_address' => 'Bún chả que tre',
                'latitude' => 21.043744,
                'longitude' => 105.845876,
            ],
        ];

        // Tạo 3 tài khoản tài xế cho hub Bưu Cục Vân Hồ
        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_7856383102_{$i}@example.com";
            $phone = "092383102{$i}";
            $fullName = "Driver Bưu Cục Vân Hồ #$i";

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

            DriverProfile::updateOrCreate(
                ['user_id' => $driverUser->id],
                [
                    'full_name' => $fullName,
                    'email' => $email,
                    'phone' => $phone,
                    'province_code' => 1,
                    'post_office_id' => '7856383102',
                    'post_office_name' => 'Bưu Cục Vân Hồ',
                    'post_office_address' => 'Phố Vân Hồ 3',
                    'post_office_lat' => '21.010339',
                    'post_office_lng' => '105.846454',
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

            $addr = $driverAddresses_7856383102[$i - 1];
            UserInfo::updateOrCreate(
                ['user_id' => $driverUser->id],
                [
                    'national_id' => '7856383102' . str_pad($i, 5, '0', STR_PAD_LEFT),
                    'tax_code' => 'TX7856383102' . str_pad($i, 3, '0', STR_PAD_LEFT),
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

        // ════════════════════════════════════════════
// HUB: Bưu Điện Lò Đúc
// ID: 7856383104 | Tọa độ: (21.013282, 105.85777)
// ════════════════════════════════════════════

        // Tạo user hub
        $hubUser = User::firstOrCreate(
            ['email' => 'hub_7856383104@example.com'],
            [
                'phone' => '024' . str_pad('7856383104', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu Điện Lò Đúc',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        // Tạo hub record
        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '7856383104',
                'hub_latitude' => '21.013282',
                'hub_longitude' => '105.85777',
                'hub_address' => 'Phố Lò Đúc',
            ]
        );

        // ════════════════════════════════════════════
// 3️⃣ TÀI XẾ CHO HUB: Bưu Điện Lò Đúc
// ════════════════════════════════════════════

        $driverAddresses_7856383104 = [
            [
                'full_address' => 'restaurant',
                'latitude' => 21.013187,
                'longitude' => 105.851775,
            ],
            [
                'full_address' => 'Quan Pho Huong',
                'latitude' => 21.038221,
                'longitude' => 105.867775,
            ],
            [
                'full_address' => 'Phố Mai Hắc Đế',
                'latitude' => 21.013274,
                'longitude' => 105.850942,
            ],
        ];

        // Tạo 3 tài khoản tài xế cho hub Bưu Điện Lò Đúc
        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_7856383104_{$i}@example.com";
            $phone = "092383104{$i}";
            $fullName = "Driver Bưu Điện Lò Đúc #$i";

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

            DriverProfile::updateOrCreate(
                ['user_id' => $driverUser->id],
                [
                    'full_name' => $fullName,
                    'email' => $email,
                    'phone' => $phone,
                    'province_code' => 1,
                    'post_office_id' => '7856383104',
                    'post_office_name' => 'Bưu Điện Lò Đúc',
                    'post_office_address' => 'Phố Lò Đúc',
                    'post_office_lat' => '21.013282',
                    'post_office_lng' => '105.85777',
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

            $addr = $driverAddresses_7856383104[$i - 1];
            UserInfo::updateOrCreate(
                ['user_id' => $driverUser->id],
                [
                    'national_id' => '7856383104' . str_pad($i, 5, '0', STR_PAD_LEFT),
                    'tax_code' => 'TX7856383104' . str_pad($i, 3, '0', STR_PAD_LEFT),
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

        // ════════════════════════════════════════════
// HUB: Bưu cục Bách Khoa
// ID: 7856622549 | Tọa độ: (21.003708, 105.847806)
// ════════════════════════════════════════════

        // Tạo user hub
        $hubUser = User::firstOrCreate(
            ['email' => 'hub_7856622549@example.com'],
            [
                'phone' => '024' . str_pad('7856622549', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu cục Bách Khoa',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        // Tạo hub record
        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '7856622549',
                'hub_latitude' => '21.003708',
                'hub_longitude' => '105.847806',
                'hub_address' => 'Phố Tạ Quang Bửu',
            ]
        );

        // ════════════════════════════════════════════
// 3️⃣ TÀI XẾ CHO HUB: Bưu cục Bách Khoa
// ════════════════════════════════════════════

        $driverAddresses_7856622549 = [
            [
                'full_address' => 'Phố Bùi Thị Xuân',
                'latitude' => 21.012884,
                'longitude' => 105.849976,
            ],
            [
                'full_address' => 'Phố Minh Khai',
                'latitude' => 20.99962,
                'longitude' => 105.870416,
            ],
            [
                'full_address' => 'Đường Yên Phụ',
                'latitude' => 21.041456,
                'longitude' => 105.848997,
            ],
        ];

        // Tạo 3 tài khoản tài xế cho hub Bưu cục Bách Khoa
        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_7856622549_{$i}@example.com";
            $phone = "092622549{$i}";
            $fullName = "Driver Bưu cục Bách Khoa #$i";

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

            DriverProfile::updateOrCreate(
                ['user_id' => $driverUser->id],
                [
                    'full_name' => $fullName,
                    'email' => $email,
                    'phone' => $phone,
                    'province_code' => 1,
                    'post_office_id' => '7856622549',
                    'post_office_name' => 'Bưu cục Bách Khoa',
                    'post_office_address' => 'Phố Tạ Quang Bửu',
                    'post_office_lat' => '21.003708',
                    'post_office_lng' => '105.847806',
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

            $addr = $driverAddresses_7856622549[$i - 1];
            UserInfo::updateOrCreate(
                ['user_id' => $driverUser->id],
                [
                    'national_id' => '7856622549' . str_pad($i, 5, '0', STR_PAD_LEFT),
                    'tax_code' => 'TX7856622549' . str_pad($i, 3, '0', STR_PAD_LEFT),
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

        // ════════════════════════════════════════════
// HUB: Bưu Cục Chợ Mơ
// ID: 7856622550 | Tọa độ: (20.995698, 105.850091)
// ════════════════════════════════════════════

        // Tạo user hub
        $hubUser = User::firstOrCreate(
            ['email' => 'hub_7856622550@example.com'],
            [
                'phone' => '024' . str_pad('7856622550', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu Cục Chợ Mơ',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        // Tạo hub record
        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '7856622550',
                'hub_latitude' => '20.995698',
                'hub_longitude' => '105.850091',
                'hub_address' => 'Phố Minh Khai',
            ]
        );

        // ════════════════════════════════════════════
// 3️⃣ TÀI XẾ CHO HUB: Bưu Cục Chợ Mơ
// ════════════════════════════════════════════

        $driverAddresses_7856622550 = [
            [
                'full_address' => 'Quỳnh Lôi',
                'latitude' => 21.001005,
                'longitude' => 105.859948,
            ],
            [
                'full_address' => 'Phố Văn Miếu',
                'latitude' => 21.029173,
                'longitude' => 105.836737,
            ],
            [
                'full_address' => 'Đường Trần Quang Khải',
                'latitude' => 21.028031,
                'longitude' => 105.857846,
            ],
        ];

        // Tạo 3 tài khoản tài xế cho hub Bưu Cục Chợ Mơ
        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_7856622550_{$i}@example.com";
            $phone = "092622550{$i}";
            $fullName = "Driver Bưu Cục Chợ Mơ #$i";

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

            DriverProfile::updateOrCreate(
                ['user_id' => $driverUser->id],
                [
                    'full_name' => $fullName,
                    'email' => $email,
                    'phone' => $phone,
                    'province_code' => 1,
                    'post_office_id' => '7856622550',
                    'post_office_name' => 'Bưu Cục Chợ Mơ',
                    'post_office_address' => 'Phố Minh Khai',
                    'post_office_lat' => '20.995698',
                    'post_office_lng' => '105.850091',
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

            $addr = $driverAddresses_7856622550[$i - 1];
            UserInfo::updateOrCreate(
                ['user_id' => $driverUser->id],
                [
                    'national_id' => '7856622550' . str_pad($i, 5, '0', STR_PAD_LEFT),
                    'tax_code' => 'TX7856622550' . str_pad($i, 3, '0', STR_PAD_LEFT),
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

        // ════════════════════════════════════════════
// HUB: Bưu Cục KHL Hai Bà Trưng
// ID: 7856622551 | Tọa độ: (21.002519, 105.867234)
// ════════════════════════════════════════════

        // Tạo user hub
        $hubUser = User::firstOrCreate(
            ['email' => 'hub_7856622551@example.com'],
            [
                'phone' => '024' . str_pad('7856622551', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu Cục KHL Hai Bà Trưng',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        // Tạo hub record
        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '7856622551',
                'hub_latitude' => '21.002519',
                'hub_longitude' => '105.867234',
                'hub_address' => 'Phố Lạc Trung',
            ]
        );

        // ════════════════════════════════════════════
// 3️⃣ TÀI XẾ CHO HUB: Bưu Cục KHL Hai Bà Trưng
// ════════════════════════════════════════════

        $driverAddresses_7856622551 = [
            [
                'full_address' => 'Phố Ấu Triệu',
                'latitude' => 21.028884,
                'longitude' => 105.848712,
            ],
            [
                'full_address' => 'Phố Trần Nhân Tông',
                'latitude' => 21.016775,
                'longitude' => 105.850005,
            ],
            [
                'full_address' => 'Quan Ga Coi',
                'latitude' => 21.038145,
                'longitude' => 105.867834,
            ],
        ];

        // Tạo 3 tài khoản tài xế cho hub Bưu Cục KHL Hai Bà Trưng
        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_7856622551_{$i}@example.com";
            $phone = "092622551{$i}";
            $fullName = "Driver Bưu Cục KHL Hai Bà Trưng #$i";

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

            DriverProfile::updateOrCreate(
                ['user_id' => $driverUser->id],
                [
                    'full_name' => $fullName,
                    'email' => $email,
                    'phone' => $phone,
                    'province_code' => 1,
                    'post_office_id' => '7856622551',
                    'post_office_name' => 'Bưu Cục KHL Hai Bà Trưng',
                    'post_office_address' => 'Phố Lạc Trung',
                    'post_office_lat' => '21.002519',
                    'post_office_lng' => '105.867234',
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

            $addr = $driverAddresses_7856622551[$i - 1];
            UserInfo::updateOrCreate(
                ['user_id' => $driverUser->id],
                [
                    'national_id' => '7856622551' . str_pad($i, 5, '0', STR_PAD_LEFT),
                    'tax_code' => 'TX7856622551' . str_pad($i, 3, '0', STR_PAD_LEFT),
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

        // ════════════════════════════════════════════
// HUB: Bưu điện Đống Đa
// ID: 7856622554 | Tọa độ: (21.007116, 105.821162)
// ════════════════════════════════════════════

        // Tạo user hub
        $hubUser = User::firstOrCreate(
            ['email' => 'hub_7856622554@example.com'],
            [
                'phone' => '024' . str_pad('7856622554', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu điện Đống Đa',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        // Tạo hub record
        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '7856622554',
                'hub_latitude' => '21.007116',
                'hub_longitude' => '105.821162',
                'hub_address' => 'Phố Thái Thịnh',
            ]
        );

        // ════════════════════════════════════════════
// 3️⃣ TÀI XẾ CHO HUB: Bưu điện Đống Đa
// ════════════════════════════════════════════

        $driverAddresses_7856622554 = [
            [
                'full_address' => 'Phố Hàng Điếu',
                'latitude' => 21.03266,
                'longitude' => 105.846971,
            ],
            [
                'full_address' => 'Phố Giảng Võ',
                'latitude' => 21.026762,
                'longitude' => 105.821284,
            ],
            [
                'full_address' => 'Nhà Hàng Minci',
                'latitude' => 21.041978,
                'longitude' => 105.846837,
            ],
        ];

        // Tạo 3 tài khoản tài xế cho hub Bưu điện Đống Đa
        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_7856622554_{$i}@example.com";
            $phone = "092622554{$i}";
            $fullName = "Driver Bưu điện Đống Đa #$i";

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

            DriverProfile::updateOrCreate(
                ['user_id' => $driverUser->id],
                [
                    'full_name' => $fullName,
                    'email' => $email,
                    'phone' => $phone,
                    'province_code' => 1,
                    'post_office_id' => '7856622554',
                    'post_office_name' => 'Bưu điện Đống Đa',
                    'post_office_address' => 'Phố Thái Thịnh',
                    'post_office_lat' => '21.007116',
                    'post_office_lng' => '105.821162',
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

            $addr = $driverAddresses_7856622554[$i - 1];
            UserInfo::updateOrCreate(
                ['user_id' => $driverUser->id],
                [
                    'national_id' => '7856622554' . str_pad($i, 5, '0', STR_PAD_LEFT),
                    'tax_code' => 'TX7856622554' . str_pad($i, 3, '0', STR_PAD_LEFT),
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

        // ════════════════════════════════════════════
// HUB: Bưu Cục EMS Cát Linh
// ID: 7856622555 | Tọa độ: (21.028465, 105.83197)
// ════════════════════════════════════════════

        // Tạo user hub
        $hubUser = User::firstOrCreate(
            ['email' => 'hub_7856622555@example.com'],
            [
                'phone' => '024' . str_pad('7856622555', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu Cục EMS Cát Linh',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        // Tạo hub record
        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '7856622555',
                'hub_latitude' => '21.028465',
                'hub_longitude' => '105.83197',
                'hub_address' => 'Phố Cát Linh',
            ]
        );

        // ════════════════════════════════════════════
// 3️⃣ TÀI XẾ CHO HUB: Bưu Cục EMS Cát Linh
// ════════════════════════════════════════════

        $driverAddresses_7856622555 = [
            [
                'full_address' => 'Phố Lý Nam Đế',
                'latitude' => 21.033147,
                'longitude' => 105.844695,
            ],
            [
                'full_address' => 'restaurant',
                'latitude' => 21.029691,
                'longitude' => 105.843946,
            ],
            [
                'full_address' => 'quỳnh mai',
                'latitude' => 21.000982,
                'longitude' => 105.859022,
            ],
        ];

        // Tạo 3 tài khoản tài xế cho hub Bưu Cục EMS Cát Linh
        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_7856622555_{$i}@example.com";
            $phone = "092622555{$i}";
            $fullName = "Driver Bưu Cục EMS Cát Linh #$i";

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

            DriverProfile::updateOrCreate(
                ['user_id' => $driverUser->id],
                [
                    'full_name' => $fullName,
                    'email' => $email,
                    'phone' => $phone,
                    'province_code' => 1,
                    'post_office_id' => '7856622555',
                    'post_office_name' => 'Bưu Cục EMS Cát Linh',
                    'post_office_address' => 'Phố Cát Linh',
                    'post_office_lat' => '21.028465',
                    'post_office_lng' => '105.83197',
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

            $addr = $driverAddresses_7856622555[$i - 1];
            UserInfo::updateOrCreate(
                ['user_id' => $driverUser->id],
                [
                    'national_id' => '7856622555' . str_pad($i, 5, '0', STR_PAD_LEFT),
                    'tax_code' => 'TX7856622555' . str_pad($i, 3, '0', STR_PAD_LEFT),
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

        // ════════════════════════════════════════════
// HUB: Bưu điện Láng Trung
// ID: 8709095325 | Tọa độ: (21.019007, 105.808138)
// ════════════════════════════════════════════

        // Tạo user hub
        $hubUser = User::firstOrCreate(
            ['email' => 'hub_8709095325@example.com'],
            [
                'phone' => '024' . str_pad('8709095325', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu điện Láng Trung',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        // Tạo hub record
        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '8709095325',
                'hub_latitude' => '21.019007',
                'hub_longitude' => '105.808138',
                'hub_address' => 'Đường Nguyễn Chí Thanh',
            ]
        );

        // ════════════════════════════════════════════
// 3️⃣ TÀI XẾ CHO HUB: Bưu điện Láng Trung
// ════════════════════════════════════════════

        $driverAddresses_8709095325 = [
            [
                'full_address' => 'Quán Bia Hơi Cường Hói',
                'latitude' => 21.044853,
                'longitude' => 105.816819,
            ],
            [
                'full_address' => 'Ngõ Xã Đàn 2',
                'latitude' => 21.013179,
                'longitude' => 105.831759,
            ],
            [
                'full_address' => 'Đường Trường Chinh',
                'latitude' => 21.000446,
                'longitude' => 105.830716,
            ],
        ];

        // Tạo 3 tài khoản tài xế cho hub Bưu điện Láng Trung
        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_8709095325_{$i}@example.com";
            $phone = "092095325{$i}";
            $fullName = "Driver Bưu điện Láng Trung #$i";

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

            DriverProfile::updateOrCreate(
                ['user_id' => $driverUser->id],
                [
                    'full_name' => $fullName,
                    'email' => $email,
                    'phone' => $phone,
                    'province_code' => 1,
                    'post_office_id' => '8709095325',
                    'post_office_name' => 'Bưu điện Láng Trung',
                    'post_office_address' => 'Đường Nguyễn Chí Thanh',
                    'post_office_lat' => '21.019007',
                    'post_office_lng' => '105.808138',
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

            $addr = $driverAddresses_8709095325[$i - 1];
            UserInfo::updateOrCreate(
                ['user_id' => $driverUser->id],
                [
                    'national_id' => '8709095325' . str_pad($i, 5, '0', STR_PAD_LEFT),
                    'tax_code' => 'TX8709095325' . str_pad($i, 3, '0', STR_PAD_LEFT),
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

        // ════════════════════════════════════════════
// HUB: Bưu Điện Cống Vị
// ID: 9420107280 | Tọa độ: (21.035236, 105.820054)
// ════════════════════════════════════════════

        // Tạo user hub
        $hubUser = User::firstOrCreate(
            ['email' => 'hub_9420107280@example.com'],
            [
                'phone' => '024' . str_pad('9420107280', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu Điện Cống Vị',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        // Tạo hub record
        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '9420107280',
                'hub_latitude' => '21.035236',
                'hub_longitude' => '105.820054',
                'hub_address' => 'Phố Đội Cấn',
            ]
        );

        // ════════════════════════════════════════════
// 3️⃣ TÀI XẾ CHO HUB: Bưu Điện Cống Vị
// ════════════════════════════════════════════

        $driverAddresses_9420107280 = [
            [
                'full_address' => 'Ngõ Xã Đàn 2',
                'latitude' => 21.014251,
                'longitude' => 105.831388,
            ],
            [
                'full_address' => 'Trần Nhân Tông',
                'latitude' => 21.016952,
                'longitude' => 105.850332,
            ],
            [
                'full_address' => 'ACB',
                'latitude' => 21.021116,
                'longitude' => 105.844014,
            ],
        ];

        // Tạo 3 tài khoản tài xế cho hub Bưu Điện Cống Vị
        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_9420107280_{$i}@example.com";
            $phone = "092107280{$i}";
            $fullName = "Driver Bưu Điện Cống Vị #$i";

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

            DriverProfile::updateOrCreate(
                ['user_id' => $driverUser->id],
                [
                    'full_name' => $fullName,
                    'email' => $email,
                    'phone' => $phone,
                    'province_code' => 1,
                    'post_office_id' => '9420107280',
                    'post_office_name' => 'Bưu Điện Cống Vị',
                    'post_office_address' => 'Phố Đội Cấn',
                    'post_office_lat' => '21.035236',
                    'post_office_lng' => '105.820054',
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

            $addr = $driverAddresses_9420107280[$i - 1];
            UserInfo::updateOrCreate(
                ['user_id' => $driverUser->id],
                [
                    'national_id' => '9420107280' . str_pad($i, 5, '0', STR_PAD_LEFT),
                    'tax_code' => 'TX9420107280' . str_pad($i, 3, '0', STR_PAD_LEFT),
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

        // ════════════════════════════════════════════
// HUB: Bưu cục Đặng Tiến Đông
// ID: 9420432152 | Tọa độ: (21.011343, 105.824441)
// ════════════════════════════════════════════

        // Tạo user hub
        $hubUser = User::firstOrCreate(
            ['email' => 'hub_9420432152@example.com'],
            [
                'phone' => '024' . str_pad('9420432152', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu cục Đặng Tiến Đông',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        // Tạo hub record
        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '9420432152',
                'hub_latitude' => '21.011343',
                'hub_longitude' => '105.824441',
                'hub_address' => 'Phố Đặng Tiến Đông',
            ]
        );

        // ════════════════════════════════════════════
// 3️⃣ TÀI XẾ CHO HUB: Bưu cục Đặng Tiến Đông
// ════════════════════════════════════════════

        $driverAddresses_9420432152 = [
            [
                'full_address' => 'An An Coffee',
                'latitude' => 21.030453,
                'longitude' => 105.844341,
            ],
            [
                'full_address' => 'Phố Trần Nhân Tông',
                'latitude' => 21.016732,
                'longitude' => 105.851164,
            ],
            [
                'full_address' => 'Đường Điện Biên Phủ',
                'latitude' => 21.029665,
                'longitude' => 105.842298,
            ],
        ];

        // Tạo 3 tài khoản tài xế cho hub Bưu cục Đặng Tiến Đông
        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_9420432152_{$i}@example.com";
            $phone = "092432152{$i}";
            $fullName = "Driver Bưu cục Đặng Tiến Đông #$i";

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

            DriverProfile::updateOrCreate(
                ['user_id' => $driverUser->id],
                [
                    'full_name' => $fullName,
                    'email' => $email,
                    'phone' => $phone,
                    'province_code' => 1,
                    'post_office_id' => '9420432152',
                    'post_office_name' => 'Bưu cục Đặng Tiến Đông',
                    'post_office_address' => 'Phố Đặng Tiến Đông',
                    'post_office_lat' => '21.011343',
                    'post_office_lng' => '105.824441',
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

            $addr = $driverAddresses_9420432152[$i - 1];
            UserInfo::updateOrCreate(
                ['user_id' => $driverUser->id],
                [
                    'national_id' => '9420432152' . str_pad($i, 5, '0', STR_PAD_LEFT),
                    'tax_code' => 'TX9420432152' . str_pad($i, 3, '0', STR_PAD_LEFT),
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

        // ════════════════════════════════════════════
// HUB: Bưu Điện Khương Mai
// ID: 9420432154 | Tọa độ: (20.997397, 105.826758)
// ════════════════════════════════════════════

        // Tạo user hub
        $hubUser = User::firstOrCreate(
            ['email' => 'hub_9420432154@example.com'],
            [
                'phone' => '024' . str_pad('9420432154', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu Điện Khương Mai',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        // Tạo hub record
        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '9420432154',
                'hub_latitude' => '20.997397',
                'hub_longitude' => '105.826758',
                'hub_address' => 'Phố Nguyễn Ngọc Nại',
            ]
        );

        // ════════════════════════════════════════════
// 3️⃣ TÀI XẾ CHO HUB: Bưu Điện Khương Mai
// ════════════════════════════════════════════

        $driverAddresses_9420432154 = [
            [
                'full_address' => 'Quán Bún Lòng A Cay',
                'latitude' => 21.023033,
                'longitude' => 105.84902,
            ],
            [
                'full_address' => 'trần kim xuyến',
                'latitude' => 21.017532,
                'longitude' => 105.797042,
            ],
            [
                'full_address' => 'Bún Chả Kim Oanh',
                'latitude' => 21.015053,
                'longitude' => 105.833868,
            ],
        ];

        // Tạo 3 tài khoản tài xế cho hub Bưu Điện Khương Mai
        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_9420432154_{$i}@example.com";
            $phone = "092432154{$i}";
            $fullName = "Driver Bưu Điện Khương Mai #$i";

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

            DriverProfile::updateOrCreate(
                ['user_id' => $driverUser->id],
                [
                    'full_name' => $fullName,
                    'email' => $email,
                    'phone' => $phone,
                    'province_code' => 1,
                    'post_office_id' => '9420432154',
                    'post_office_name' => 'Bưu Điện Khương Mai',
                    'post_office_address' => 'Phố Nguyễn Ngọc Nại',
                    'post_office_lat' => '20.997397',
                    'post_office_lng' => '105.826758',
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

            $addr = $driverAddresses_9420432154[$i - 1];
            UserInfo::updateOrCreate(
                ['user_id' => $driverUser->id],
                [
                    'national_id' => '9420432154' . str_pad($i, 5, '0', STR_PAD_LEFT),
                    'tax_code' => 'TX9420432154' . str_pad($i, 3, '0', STR_PAD_LEFT),
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

        // ════════════════════════════════════════════
// HUB: Bưu Điện Khương Đình
// ID: 9420438150 | Tọa độ: (20.997695, 105.813458)
// ════════════════════════════════════════════

        // Tạo user hub
        $hubUser = User::firstOrCreate(
            ['email' => 'hub_9420438150@example.com'],
            [
                'phone' => '024' . str_pad('9420438150', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu Điện Khương Đình',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        // Tạo hub record
        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '9420438150',
                'hub_latitude' => '20.997695',
                'hub_longitude' => '105.813458',
                'hub_address' => 'Đường Khương Đình',
            ]
        );

        // ════════════════════════════════════════════
// 3️⃣ TÀI XẾ CHO HUB: Bưu Điện Khương Đình
// ════════════════════════════════════════════

        $driverAddresses_9420438150 = [
            [
                'full_address' => 'Cầu Giấy',
                'latitude' => 21.033195,
                'longitude' => 105.801228,
            ],
            [
                'full_address' => 'Cà Phê StarBucks',
                'latitude' => 21.010716,
                'longitude' => 105.848969,
            ],
            [
                'full_address' => 'Ngõ 120 Trường Chinh',
                'latitude' => 20.99953,
                'longitude' => 105.835869,
            ],
        ];

        // Tạo 3 tài khoản tài xế cho hub Bưu Điện Khương Đình
        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_9420438150_{$i}@example.com";
            $phone = "092438150{$i}";
            $fullName = "Driver Bưu Điện Khương Đình #$i";

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

            DriverProfile::updateOrCreate(
                ['user_id' => $driverUser->id],
                [
                    'full_name' => $fullName,
                    'email' => $email,
                    'phone' => $phone,
                    'province_code' => 1,
                    'post_office_id' => '9420438150',
                    'post_office_name' => 'Bưu Điện Khương Đình',
                    'post_office_address' => 'Đường Khương Đình',
                    'post_office_lat' => '20.997695',
                    'post_office_lng' => '105.813458',
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

            $addr = $driverAddresses_9420438150[$i - 1];
            UserInfo::updateOrCreate(
                ['user_id' => $driverUser->id],
                [
                    'national_id' => '9420438150' . str_pad($i, 5, '0', STR_PAD_LEFT),
                    'tax_code' => 'TX9420438150' . str_pad($i, 3, '0', STR_PAD_LEFT),
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

        // ════════════════════════════════════════════
// HUB: Bưu Điện Hoàng Quốc Việt
// ID: 9420492209 | Tọa độ: (21.04587, 105.79117)
// ════════════════════════════════════════════

        // Tạo user hub
        $hubUser = User::firstOrCreate(
            ['email' => 'hub_9420492209@example.com'],
            [
                'phone' => '024' . str_pad('9420492209', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu Điện Hoàng Quốc Việt',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        // Tạo hub record
        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '9420492209',
                'hub_latitude' => '21.04587',
                'hub_longitude' => '105.79117',
                'hub_address' => 'Đường Hoàng Quốc Việt',
            ]
        );

        // ════════════════════════════════════════════
// 3️⃣ TÀI XẾ CHO HUB: Bưu Điện Hoàng Quốc Việt
// ════════════════════════════════════════════

        $driverAddresses_9420492209 = [
            [
                'full_address' => 'Đường Xuân Diệu',
                'latitude' => 21.061287,
                'longitude' => 105.831793,
            ],
            [
                'full_address' => 'TwitterBeans Coffee',
                'latitude' => 21.065283,
                'longitude' => 105.798069,
            ],
            [
                'full_address' => 'Ngõ 33 Phạm Tuấn Tài',
                'latitude' => 21.044334,
                'longitude' => 105.785846,
            ],
        ];

        // Tạo 3 tài khoản tài xế cho hub Bưu Điện Hoàng Quốc Việt
        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_9420492209_{$i}@example.com";
            $phone = "092492209{$i}";
            $fullName = "Driver Bưu Điện Hoàng Quốc Việt #$i";

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

            DriverProfile::updateOrCreate(
                ['user_id' => $driverUser->id],
                [
                    'full_name' => $fullName,
                    'email' => $email,
                    'phone' => $phone,
                    'province_code' => 1,
                    'post_office_id' => '9420492209',
                    'post_office_name' => 'Bưu Điện Hoàng Quốc Việt',
                    'post_office_address' => 'Đường Hoàng Quốc Việt',
                    'post_office_lat' => '21.04587',
                    'post_office_lng' => '105.79117',
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

            $addr = $driverAddresses_9420492209[$i - 1];
            UserInfo::updateOrCreate(
                ['user_id' => $driverUser->id],
                [
                    'national_id' => '9420492209' . str_pad($i, 5, '0', STR_PAD_LEFT),
                    'tax_code' => 'TX9420492209' . str_pad($i, 3, '0', STR_PAD_LEFT),
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

        // ════════════════════════════════════════════
// HUB: Bưu Điện Yên Phụ
// ID: 9422173848 | Tọa độ: (21.051152, 105.838953)
// ════════════════════════════════════════════

        // Tạo user hub
        $hubUser = User::firstOrCreate(
            ['email' => 'hub_9422173848@example.com'],
            [
                'phone' => '024' . str_pad('9422173848', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu Điện Yên Phụ',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        // Tạo hub record
        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '9422173848',
                'hub_latitude' => '21.051152',
                'hub_longitude' => '105.838953',
                'hub_address' => 'Phố Yên Phụ',
            ]
        );

        // ════════════════════════════════════════════
// 3️⃣ TÀI XẾ CHO HUB: Bưu Điện Yên Phụ
// ════════════════════════════════════════════

        $driverAddresses_9422173848 = [
            [
                'full_address' => 'Phố Văn Miếu',
                'latitude' => 21.027956,
                'longitude' => 105.836307,
            ],
            [
                'full_address' => 'Đường Hoàng Quốc Việt',
                'latitude' => 21.045974,
                'longitude' => 105.803635,
            ],
            [
                'full_address' => 'Phố Trần Hưng Đạo',
                'latitude' => 21.022343,
                'longitude' => 105.846943,
            ],
        ];

        // Tạo 3 tài khoản tài xế cho hub Bưu Điện Yên Phụ
        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_9422173848_{$i}@example.com";
            $phone = "092173848{$i}";
            $fullName = "Driver Bưu Điện Yên Phụ #$i";

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

            DriverProfile::updateOrCreate(
                ['user_id' => $driverUser->id],
                [
                    'full_name' => $fullName,
                    'email' => $email,
                    'phone' => $phone,
                    'province_code' => 1,
                    'post_office_id' => '9422173848',
                    'post_office_name' => 'Bưu Điện Yên Phụ',
                    'post_office_address' => 'Phố Yên Phụ',
                    'post_office_lat' => '21.051152',
                    'post_office_lng' => '105.838953',
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

            $addr = $driverAddresses_9422173848[$i - 1];
            UserInfo::updateOrCreate(
                ['user_id' => $driverUser->id],
                [
                    'national_id' => '9422173848' . str_pad($i, 5, '0', STR_PAD_LEFT),
                    'tax_code' => 'TX9422173848' . str_pad($i, 3, '0', STR_PAD_LEFT),
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

        // ════════════════════════════════════════════
// HUB: Bưu Điện Ngọc Lâm
// ID: 9422173849 | Tọa độ: (21.048082, 105.874248)
// ════════════════════════════════════════════

        // Tạo user hub
        $hubUser = User::firstOrCreate(
            ['email' => 'hub_9422173849@example.com'],
            [
                'phone' => '024' . str_pad('9422173849', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu Điện Ngọc Lâm',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        // Tạo hub record
        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '9422173849',
                'hub_latitude' => '21.048082',
                'hub_longitude' => '105.874248',
                'hub_address' => 'Phố Ngọc Lâm',
            ]
        );

        // ════════════════════════════════════════════
// 3️⃣ TÀI XẾ CHO HUB: Bưu Điện Ngọc Lâm
// ════════════════════════════════════════════

        $driverAddresses_9422173849 = [
            [
                'full_address' => 'Phố Quang Trung',
                'latitude' => 21.026919,
                'longitude' => 105.850213,
            ],
            [
                'full_address' => 'Phố Bùi Thị Xuân',
                'latitude' => 21.014104,
                'longitude' => 105.849988,
            ],
            [
                'full_address' => 'Phố Triệu Việt Vương',
                'latitude' => 21.013241,
                'longitude' => 105.850542,
            ],
        ];

        // Tạo 3 tài khoản tài xế cho hub Bưu Điện Ngọc Lâm
        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_9422173849_{$i}@example.com";
            $phone = "092173849{$i}";
            $fullName = "Driver Bưu Điện Ngọc Lâm #$i";

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

            DriverProfile::updateOrCreate(
                ['user_id' => $driverUser->id],
                [
                    'full_name' => $fullName,
                    'email' => $email,
                    'phone' => $phone,
                    'province_code' => 1,
                    'post_office_id' => '9422173849',
                    'post_office_name' => 'Bưu Điện Ngọc Lâm',
                    'post_office_address' => 'Phố Ngọc Lâm',
                    'post_office_lat' => '21.048082',
                    'post_office_lng' => '105.874248',
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

            $addr = $driverAddresses_9422173849[$i - 1];
            UserInfo::updateOrCreate(
                ['user_id' => $driverUser->id],
                [
                    'national_id' => '9422173849' . str_pad($i, 5, '0', STR_PAD_LEFT),
                    'tax_code' => 'TX9422173849' . str_pad($i, 3, '0', STR_PAD_LEFT),
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

        // ════════════════════════════════════════════
// HUB: Bưu điện Việt Nam
// ID: 9422173850 | Tọa độ: (20.988763, 105.84682)
// ════════════════════════════════════════════

        // Tạo user hub
        $hubUser = User::firstOrCreate(
            ['email' => 'hub_9422173850@example.com'],
            [
                'phone' => '024' . str_pad('9422173850', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu điện Việt Nam',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        // Tạo hub record
        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '9422173850',
                'hub_latitude' => '20.988763',
                'hub_longitude' => '105.84682',
                'hub_address' => 'Phố Nguyễn An Ninh',
            ]
        );

        // ════════════════════════════════════════════
// 3️⃣ TÀI XẾ CHO HUB: Bưu điện Việt Nam
// ════════════════════════════════════════════

        $driverAddresses_9422173850 = [
            [
                'full_address' => 'Cà Phê Cơm Chay',
                'latitude' => 21.015332,
                'longitude' => 105.812735,
            ],
            [
                'full_address' => 'Oceanbank',
                'latitude' => 21.026304,
                'longitude' => 105.837253,
            ],
            [
                'full_address' => 'cafe',
                'latitude' => 20.999755,
                'longitude' => 105.802875,
            ],
        ];

        // Tạo 3 tài khoản tài xế cho hub Bưu điện Việt Nam
        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_9422173850_{$i}@example.com";
            $phone = "092173850{$i}";
            $fullName = "Driver Bưu điện Việt Nam #$i";

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

            DriverProfile::updateOrCreate(
                ['user_id' => $driverUser->id],
                [
                    'full_name' => $fullName,
                    'email' => $email,
                    'phone' => $phone,
                    'province_code' => 1,
                    'post_office_id' => '9422173850',
                    'post_office_name' => 'Bưu điện Việt Nam',
                    'post_office_address' => 'Phố Nguyễn An Ninh',
                    'post_office_lat' => '20.988763',
                    'post_office_lng' => '105.84682',
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

            $addr = $driverAddresses_9422173850[$i - 1];
            UserInfo::updateOrCreate(
                ['user_id' => $driverUser->id],
                [
                    'national_id' => '9422173850' . str_pad($i, 5, '0', STR_PAD_LEFT),
                    'tax_code' => 'TX9422173850' . str_pad($i, 3, '0', STR_PAD_LEFT),
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

        // ════════════════════════════════════════════
// HUB: Bưu điện Trung tâm 1
// ID: 9791862120 | Tọa độ: (21.026082, 105.853731)
// ════════════════════════════════════════════

        // Tạo user hub
        $hubUser = User::firstOrCreate(
            ['email' => 'hub_9791862120@example.com'],
            [
                'phone' => '024' . str_pad('9791862120', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu điện Trung tâm 1',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        // Tạo hub record
        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '9791862120',
                'hub_latitude' => '21.026082',
                'hub_longitude' => '105.853731',
                'hub_address' => 'Phố Đinh Tiên Hoàng',
            ]
        );

        // ════════════════════════════════════════════
// 3️⃣ TÀI XẾ CHO HUB: Bưu điện Trung tâm 1
// ════════════════════════════════════════════

        $driverAddresses_9791862120 = [
            [
                'full_address' => '하노이 부라더스',
                'latitude' => 21.028914,
                'longitude' => 105.840857,
            ],
            [
                'full_address' => 'Cà Phê Mystic',
                'latitude' => 21.035643,
                'longitude' => 105.853865,
            ],
            [
                'full_address' => 'Giang My',
                'latitude' => 21.023681,
                'longitude' => 105.850725,
            ],
        ];

        // Tạo 3 tài khoản tài xế cho hub Bưu điện Trung tâm 1
        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_9791862120_{$i}@example.com";
            $phone = "092862120{$i}";
            $fullName = "Driver Bưu điện Trung tâm 1 #$i";

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

            DriverProfile::updateOrCreate(
                ['user_id' => $driverUser->id],
                [
                    'full_name' => $fullName,
                    'email' => $email,
                    'phone' => $phone,
                    'province_code' => 1,
                    'post_office_id' => '9791862120',
                    'post_office_name' => 'Bưu điện Trung tâm 1',
                    'post_office_address' => 'Phố Đinh Tiên Hoàng',
                    'post_office_lat' => '21.026082',
                    'post_office_lng' => '105.853731',
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

            $addr = $driverAddresses_9791862120[$i - 1];
            UserInfo::updateOrCreate(
                ['user_id' => $driverUser->id],
                [
                    'national_id' => '9791862120' . str_pad($i, 5, '0', STR_PAD_LEFT),
                    'tax_code' => 'TX9791862120' . str_pad($i, 3, '0', STR_PAD_LEFT),
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

        // ════════════════════════════════════════════
// HUB: AAL Express
// ID: 10248628298 | Tọa độ: (21.041674, 105.782155)
// ════════════════════════════════════════════

        // Tạo user hub
        $hubUser = User::firstOrCreate(
            ['email' => 'hub_10248628298@example.com'],
            [
                'phone' => '024' . str_pad('10248628298', 7, '0', STR_PAD_LEFT),
                'full_name' => 'AAL Express',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        // Tạo hub record
        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '10248628298',
                'hub_latitude' => '21.041674',
                'hub_longitude' => '105.782155',
                'hub_address' => 'Phố Trần Quốc Hoàn',
            ]
        );

        // ════════════════════════════════════════════
// 3️⃣ TÀI XẾ CHO HUB: AAL Express
// ════════════════════════════════════════════

        $driverAddresses_10248628298 = [
            [
                'full_address' => 'Ngõ 176 Phố Văn Hội',
                'latitude' => 21.08006,
                'longitude' => 105.773064,
            ],
            [
                'full_address' => 'Duy Tân',
                'latitude' => 21.030864,
                'longitude' => 105.785242,
            ],
            [
                'full_address' => 'Hồ Tùng Mậu',
                'latitude' => 21.03722,
                'longitude' => 105.779767,
            ],
        ];

        // Tạo 3 tài khoản tài xế cho hub AAL Express
        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_10248628298_{$i}@example.com";
            $phone = "092628298{$i}";
            $fullName = "Driver AAL Express #$i";

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

            DriverProfile::updateOrCreate(
                ['user_id' => $driverUser->id],
                [
                    'full_name' => $fullName,
                    'email' => $email,
                    'phone' => $phone,
                    'province_code' => 1,
                    'post_office_id' => '10248628298',
                    'post_office_name' => 'AAL Express',
                    'post_office_address' => 'Phố Trần Quốc Hoàn',
                    'post_office_lat' => '21.041674',
                    'post_office_lng' => '105.782155',
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

            $addr = $driverAddresses_10248628298[$i - 1];
            UserInfo::updateOrCreate(
                ['user_id' => $driverUser->id],
                [
                    'national_id' => '10248628298' . str_pad($i, 5, '0', STR_PAD_LEFT),
                    'tax_code' => 'TX10248628298' . str_pad($i, 3, '0', STR_PAD_LEFT),
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

        // ════════════════════════════════════════════
// HUB: Bưu Điện Yên Thái
// ID: 10846733402 | Tọa độ: (21.048481, 105.807964)
// ════════════════════════════════════════════

        // Tạo user hub
        $hubUser = User::firstOrCreate(
            ['email' => 'hub_10846733402@example.com'],
            [
                'phone' => '024' . str_pad('10846733402', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu Điện Yên Thái',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        // Tạo hub record
        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '10846733402',
                'hub_latitude' => '21.048481',
                'hub_longitude' => '105.807964',
                'hub_address' => 'Đường Thụy Khuê',
            ]
        );

        // ════════════════════════════════════════════
// 3️⃣ TÀI XẾ CHO HUB: Bưu Điện Yên Thái
// ════════════════════════════════════════════

        $driverAddresses_10846733402 = [
            [
                'full_address' => 'Thinh Vuong',
                'latitude' => 21.0351,
                'longitude' => 105.8521,
            ],
            [
                'full_address' => 'Phố Vũ Phạm Hàm',
                'latitude' => 21.017713,
                'longitude' => 105.798038,
            ],
            [
                'full_address' => 'Xoi Cat Lam',
                'latitude' => 21.032181,
                'longitude' => 105.846734,
            ],
        ];

        // Tạo 3 tài khoản tài xế cho hub Bưu Điện Yên Thái
        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_10846733402_{$i}@example.com";
            $phone = "092733402{$i}";
            $fullName = "Driver Bưu Điện Yên Thái #$i";

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

            DriverProfile::updateOrCreate(
                ['user_id' => $driverUser->id],
                [
                    'full_name' => $fullName,
                    'email' => $email,
                    'phone' => $phone,
                    'province_code' => 1,
                    'post_office_id' => '10846733402',
                    'post_office_name' => 'Bưu Điện Yên Thái',
                    'post_office_address' => 'Đường Thụy Khuê',
                    'post_office_lat' => '21.048481',
                    'post_office_lng' => '105.807964',
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

            $addr = $driverAddresses_10846733402[$i - 1];
            UserInfo::updateOrCreate(
                ['user_id' => $driverUser->id],
                [
                    'national_id' => '10846733402' . str_pad($i, 5, '0', STR_PAD_LEFT),
                    'tax_code' => 'TX10846733402' . str_pad($i, 3, '0', STR_PAD_LEFT),
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

        // ════════════════════════════════════════════
// HUB: DHL
// ID: 11212149507 | Tọa độ: (21.00955, 105.835515)
// ════════════════════════════════════════════

        // Tạo user hub
        $hubUser = User::firstOrCreate(
            ['email' => 'hub_11212149507@example.com'],
            [
                'phone' => '024' . str_pad('11212149507', 7, '0', STR_PAD_LEFT),
                'full_name' => 'DHL',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        // Tạo hub record
        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '11212149507',
                'hub_latitude' => '21.00955',
                'hub_longitude' => '105.835515',
                'hub_address' => 'Phố Phạm Ngọc Thạch',
            ]
        );

        // ════════════════════════════════════════════
// 3️⃣ TÀI XẾ CHO HUB: DHL
// ════════════════════════════════════════════

        $driverAddresses_11212149507 = [
            [
                'full_address' => 'Yên Phụ',
                'latitude' => 21.051459,
                'longitude' => 105.838786,
            ],
            [
                'full_address' => 'Phố Triệu Việt Vương',
                'latitude' => 21.014001,
                'longitude' => 105.850339,
            ],
            [
                'full_address' => 'Phố Trấn Vũ',
                'latitude' => 21.044623,
                'longitude' => 105.838118,
            ],
        ];

        // Tạo 3 tài khoản tài xế cho hub DHL
        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_11212149507_{$i}@example.com";
            $phone = "092149507{$i}";
            $fullName = "Driver DHL #$i";

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

            DriverProfile::updateOrCreate(
                ['user_id' => $driverUser->id],
                [
                    'full_name' => $fullName,
                    'email' => $email,
                    'phone' => $phone,
                    'province_code' => 1,
                    'post_office_id' => '11212149507',
                    'post_office_name' => 'DHL',
                    'post_office_address' => 'Phố Phạm Ngọc Thạch',
                    'post_office_lat' => '21.00955',
                    'post_office_lng' => '105.835515',
                    'post_office_phone' => '+84 24 3775 6937',
                    'vehicle_type' => 'Xe máy',
                    'license_number' => "$i$i$i$i$i$i$i$i",
                    'license_image' => 'license_image.png',
                    'identity_image' => 'identity_image.png',
                    'experience' => rand(1, 5) . ' năm',
                    'status' => 'approved',
                    'approved_at' => Carbon::now(),
                ]
            );

            $addr = $driverAddresses_11212149507[$i - 1];
            UserInfo::updateOrCreate(
                ['user_id' => $driverUser->id],
                [
                    'national_id' => '11212149507' . str_pad($i, 5, '0', STR_PAD_LEFT),
                    'tax_code' => 'TX11212149507' . str_pad($i, 3, '0', STR_PAD_LEFT),
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

        // ════════════════════════════════════════════
// HUB: Chuyển phát nhanh Văn Minh
// ID: 11263842275 | Tọa độ: (21.015283, 105.832786)
// ════════════════════════════════════════════

        // Tạo user hub
        $hubUser = User::firstOrCreate(
            ['email' => 'hub_11263842275@example.com'],
            [
                'phone' => '024' . str_pad('11263842275', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Chuyển phát nhanh Văn Minh',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        // Tạo hub record
        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '11263842275',
                'hub_latitude' => '21.015283',
                'hub_longitude' => '105.832786',
                'hub_address' => 'Phố Xã Đàn',
            ]
        );

        // ════════════════════════════════════════════
// 3️⃣ TÀI XẾ CHO HUB: Chuyển phát nhanh Văn Minh
// ════════════════════════════════════════════

        $driverAddresses_11263842275 = [
            [
                'full_address' => 'Quảng Khanh',
                'latitude' => 21.055691,
                'longitude' => 105.82126,
            ],
            [
                'full_address' => 'Đường Lê Văn Lương',
                'latitude' => 21.00596,
                'longitude' => 105.805056,
            ],
            [
                'full_address' => 'Phố Lê Thái Tổ',
                'latitude' => 21.031132,
                'longitude' => 105.85078,
            ],
        ];

        // Tạo 3 tài khoản tài xế cho hub Chuyển phát nhanh Văn Minh
        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_11263842275_{$i}@example.com";
            $phone = "092842275{$i}";
            $fullName = "Driver Chuyển phát nhanh Văn Minh #$i";

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

            DriverProfile::updateOrCreate(
                ['user_id' => $driverUser->id],
                [
                    'full_name' => $fullName,
                    'email' => $email,
                    'phone' => $phone,
                    'province_code' => 1,
                    'post_office_id' => '11263842275',
                    'post_office_name' => 'Chuyển phát nhanh Văn Minh',
                    'post_office_address' => 'Phố Xã Đàn',
                    'post_office_lat' => '21.015283',
                    'post_office_lng' => '105.832786',
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

            $addr = $driverAddresses_11263842275[$i - 1];
            UserInfo::updateOrCreate(
                ['user_id' => $driverUser->id],
                [
                    'national_id' => '11263842275' . str_pad($i, 5, '0', STR_PAD_LEFT),
                    'tax_code' => 'TX11263842275' . str_pad($i, 3, '0', STR_PAD_LEFT),
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

        // ════════════════════════════════════════════
// HUB: Bưu điện Thành phố Hà Nội
// ID: 11285982899 | Tọa độ: (21.026698, 105.853673)
// ════════════════════════════════════════════

        // Tạo user hub
        $hubUser = User::firstOrCreate(
            ['email' => 'hub_11285982899@example.com'],
            [
                'phone' => '024' . str_pad('11285982899', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu điện Thành phố Hà Nội',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        // Tạo hub record
        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '11285982899',
                'hub_latitude' => '21.026698',
                'hub_longitude' => '105.853673',
                'hub_address' => 'Phố Đinh Tiên Hoàng',
            ]
        );

        // ════════════════════════════════════════════
// 3️⃣ TÀI XẾ CHO HUB: Bưu điện Thành phố Hà Nội
// ════════════════════════════════════════════

        $driverAddresses_11285982899 = [
            [
                'full_address' => 'Đường Số 5',
                'latitude' => 20.996558,
                'longitude' => 105.86986,
            ],
            [
                'full_address' => 'Phố Đội Cấn',
                'latitude' => 21.034761,
                'longitude' => 105.826361,
            ],
            [
                'full_address' => 'Văn Cao',
                'latitude' => 21.040944,
                'longitude' => 105.816402,
            ],
        ];

        // Tạo 3 tài khoản tài xế cho hub Bưu điện Thành phố Hà Nội
        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_11285982899_{$i}@example.com";
            $phone = "092982899{$i}";
            $fullName = "Driver Bưu điện Thành phố Hà Nội #$i";

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

            DriverProfile::updateOrCreate(
                ['user_id' => $driverUser->id],
                [
                    'full_name' => $fullName,
                    'email' => $email,
                    'phone' => $phone,
                    'province_code' => 1,
                    'post_office_id' => '11285982899',
                    'post_office_name' => 'Bưu điện Thành phố Hà Nội',
                    'post_office_address' => 'Phố Đinh Tiên Hoàng',
                    'post_office_lat' => '21.026698',
                    'post_office_lng' => '105.853673',
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

            $addr = $driverAddresses_11285982899[$i - 1];
            UserInfo::updateOrCreate(
                ['user_id' => $driverUser->id],
                [
                    'national_id' => '11285982899' . str_pad($i, 5, '0', STR_PAD_LEFT),
                    'tax_code' => 'TX11285982899' . str_pad($i, 3, '0', STR_PAD_LEFT),
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

        // ════════════════════════════════════════════
// HUB: Bưu cục Giao dịch EMS Hoàn Kiếm
// ID: 11461525769 | Tọa độ: (21.024801, 105.856027)
// ════════════════════════════════════════════

        // Tạo user hub
        $hubUser = User::firstOrCreate(
            ['email' => 'hub_11461525769@example.com'],
            [
                'phone' => '024' . str_pad('11461525769', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu cục Giao dịch EMS Hoàn Kiếm',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        // Tạo hub record
        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '11461525769',
                'hub_latitude' => '21.024801',
                'hub_longitude' => '105.856027',
                'hub_address' => 'Phố Tràng Tiền',
            ]
        );

        // ════════════════════════════════════════════
// 3️⃣ TÀI XẾ CHO HUB: Bưu cục Giao dịch EMS Hoàn Kiếm
// ════════════════════════════════════════════

        $driverAddresses_11461525769 = [
            [
                'full_address' => 'CAFE & BAR',
                'latitude' => 21.02594,
                'longitude' => 105.858808,
            ],
            [
                'full_address' => 'KFW Development Bank Vietnam Office',
                'latitude' => 21.025695,
                'longitude' => 105.846099,
            ],
            [
                'full_address' => 'Khương Trung',
                'latitude' => 20.999783,
                'longitude' => 105.818686,
            ],
        ];

        // Tạo 3 tài khoản tài xế cho hub Bưu cục Giao dịch EMS Hoàn Kiếm
        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_11461525769_{$i}@example.com";
            $phone = "092525769{$i}";
            $fullName = "Driver Bưu cục Giao dịch EMS Hoàn Kiếm #$i";

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

            DriverProfile::updateOrCreate(
                ['user_id' => $driverUser->id],
                [
                    'full_name' => $fullName,
                    'email' => $email,
                    'phone' => $phone,
                    'province_code' => 1,
                    'post_office_id' => '11461525769',
                    'post_office_name' => 'Bưu cục Giao dịch EMS Hoàn Kiếm',
                    'post_office_address' => 'Phố Tràng Tiền',
                    'post_office_lat' => '21.024801',
                    'post_office_lng' => '105.856027',
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

            $addr = $driverAddresses_11461525769[$i - 1];
            UserInfo::updateOrCreate(
                ['user_id' => $driverUser->id],
                [
                    'national_id' => '11461525769' . str_pad($i, 5, '0', STR_PAD_LEFT),
                    'tax_code' => 'TX11461525769' . str_pad($i, 3, '0', STR_PAD_LEFT),
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

        // ════════════════════════════════════════════
// HUB: 247Express
// ID: 11564316606 | Tọa độ: (21.043931, 105.786054)
// ════════════════════════════════════════════

        // Tạo user hub
        $hubUser = User::firstOrCreate(
            ['email' => 'hub_11564316606@example.com'],
            [
                'phone' => '024' . str_pad('11564316606', 7, '0', STR_PAD_LEFT),
                'full_name' => '247Express',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        // Tạo hub record
        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '11564316606',
                'hub_latitude' => '21.043931',
                'hub_longitude' => '105.786054',
                'hub_address' => 'Đường Đặng Thùy Trâm',
            ]
        );
// ════════════════════════════════════════════
// 3️⃣ TÀI XẾ CHO HUB: Viettel Post
// ════════════════════════════════════════════

        $driverAddresses_11568907802 = [
            [
                'full_address' => 'Cà Phê Feeling Tea',
                'latitude' => 21.022994,
                'longitude' => 105.801531,
            ],
            [
                'full_address' => 'Đường Hoàng Tăng Bí',
                'latitude' => 21.083634,
                'longitude' => 105.775905,
            ],
            [
                'full_address' => 'Chill-Tiệm Trà Chanh',
                'latitude' => 21.073922,
                'longitude' => 105.774285,
            ],
        ];

        // Tạo 3 tài khoản tài xế cho hub Viettel Post
        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_11568907802_{$i}@example.com";
            $phone = "092907802{$i}";
            $fullName = "Driver Viettel Post #$i";

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

            DriverProfile::updateOrCreate(
                ['user_id' => $driverUser->id],
                [
                    'full_name' => $fullName,
                    'email' => $email,
                    'phone' => $phone,
                    'province_code' => 1,
                    'post_office_id' => '11568907802',
                    'post_office_name' => 'Viettel Post',
                    'post_office_address' => 'Phố Đặng Thùy Trâm',
                    'post_office_lat' => '21.043851',
                    'post_office_lng' => '105.785706',
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

            $addr = $driverAddresses_11568907802[$i - 1];
            UserInfo::updateOrCreate(
                ['user_id' => $driverUser->id],
                [
                    'national_id' => '11568907802' . str_pad($i, 5, '0', STR_PAD_LEFT),
                    'tax_code' => 'TX11568907802' . str_pad($i, 3, '0', STR_PAD_LEFT),
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

        // ════════════════════════════════════════════
// HUB: J&T Express
// ID: 11571556924 | Tọa độ: (21.043881, 105.784743)
// ════════════════════════════════════════════

        // Tạo user hub
        $hubUser = User::firstOrCreate(
            ['email' => 'hub_11571556924@example.com'],
            [
                'phone' => '024' . str_pad('11571556924', 7, '0', STR_PAD_LEFT),
                'full_name' => 'J&T Express',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        // Tạo hub record
        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '11571556924',
                'hub_latitude' => '21.043881',
                'hub_longitude' => '105.784743',
                'hub_address' => 'Ngõ 3 Phạm Tuấn Tài',
            ]
        );

        // ════════════════════════════════════════════
// 3️⃣ TÀI XẾ CHO HUB: J&T Express
// ════════════════════════════════════════════

        $driverAddresses_11571556924 = [
            [
                'full_address' => 'Hồ Tùng Mậu',
                'latitude' => 21.03722,
                'longitude' => 105.779767,
            ],
            [
                'full_address' => 'cafe',
                'latitude' => 21.025837,
                'longitude' => 105.822172,
            ],
            [
                'full_address' => 'cafe',
                'latitude' => 21.048167,
                'longitude' => 105.795792,
            ],
        ];

        // Tạo 3 tài khoản tài xế cho hub J&T Express
        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_11571556924_{$i}@example.com";
            $phone = "092556924{$i}";
            $fullName = "Driver J&T Express #$i";

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

            DriverProfile::updateOrCreate(
                ['user_id' => $driverUser->id],
                [
                    'full_name' => $fullName,
                    'email' => $email,
                    'phone' => $phone,
                    'province_code' => 1,
                    'post_office_id' => '11571556924',
                    'post_office_name' => 'J&T Express',
                    'post_office_address' => 'Ngõ 3 Phạm Tuấn Tài',
                    'post_office_lat' => '21.043881',
                    'post_office_lng' => '105.784743',
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

            $addr = $driverAddresses_11571556924[$i - 1];
            UserInfo::updateOrCreate(
                ['user_id' => $driverUser->id],
                [
                    'national_id' => '11571556924' . str_pad($i, 5, '0', STR_PAD_LEFT),
                    'tax_code' => 'TX11571556924' . str_pad($i, 3, '0', STR_PAD_LEFT),
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

        // ════════════════════════════════════════════
// HUB: Viettel Post
// ID: 11672848994 | Tọa độ: (21.009772, 105.850316)
// ════════════════════════════════════════════

        // Tạo user hub
        $hubUser = User::firstOrCreate(
            ['email' => 'hub_11672848994@example.com'],
            [
                'phone' => '024' . str_pad('11672848994', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Viettel Post',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        // Tạo hub record
        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '11672848994',
                'hub_latitude' => '21.009772',
                'hub_longitude' => '105.850316',
                'hub_address' => 'Phố Lê Đại Hành',
            ]
        );

        // ════════════════════════════════════════════
// 3️⃣ TÀI XẾ CHO HUB: Viettel Post
// ════════════════════════════════════════════

        $driverAddresses_11672848994 = [
            [
                'full_address' => 'cafe',
                'latitude' => 21.036786,
                'longitude' => 105.84941,
            ],
            [
                'full_address' => 'Phố Trần Nhân Tông',
                'latitude' => 21.01685,
                'longitude' => 105.849742,
            ],
            [
                'full_address' => 'Phố Cao Bá Quát',
                'latitude' => 21.030469,
                'longitude' => 105.838346,
            ],
        ];

        // Tạo 3 tài khoản tài xế cho hub Viettel Post
        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_11672848994_{$i}@example.com";
            $phone = "092848994{$i}";
            $fullName = "Driver Viettel Post #$i";

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

            DriverProfile::updateOrCreate(
                ['user_id' => $driverUser->id],
                [
                    'full_name' => $fullName,
                    'email' => $email,
                    'phone' => $phone,
                    'province_code' => 1,
                    'post_office_id' => '11672848994',
                    'post_office_name' => 'Viettel Post',
                    'post_office_address' => 'Phố Lê Đại Hành',
                    'post_office_lat' => '21.009772',
                    'post_office_lng' => '105.850316',
                    'post_office_phone' => '+84352036739',
                    'vehicle_type' => 'Xe máy',
                    'license_number' => "$i$i$i$i$i$i$i$i",
                    'license_image' => 'license_image.png',
                    'identity_image' => 'identity_image.png',
                    'experience' => rand(1, 5) . ' năm',
                    'status' => 'approved',
                    'approved_at' => Carbon::now(),
                ]
            );

            $addr = $driverAddresses_11672848994[$i - 1];
            UserInfo::updateOrCreate(
                ['user_id' => $driverUser->id],
                [
                    'national_id' => '11672848994' . str_pad($i, 5, '0', STR_PAD_LEFT),
                    'tax_code' => 'TX11672848994' . str_pad($i, 3, '0', STR_PAD_LEFT),
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

        // ════════════════════════════════════════════
// HUB: Bưu điện CP16
// ID: 12082718342 | Tọa độ: (21.033627, 105.833826)
// ════════════════════════════════════════════

        // Tạo user hub
        $hubUser = User::firstOrCreate(
            ['email' => 'hub_12082718342@example.com'],
            [
                'phone' => '024' . str_pad('12082718342', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu điện CP16',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        // Tạo hub record
        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '12082718342',
                'hub_latitude' => '21.033627',
                'hub_longitude' => '105.833826',
                'hub_address' => 'Phố Ông Ích Khiêm',
            ]
        );

        // ════════════════════════════════════════════
// 3️⃣ TÀI XẾ CHO HUB: Bưu điện CP16
// ════════════════════════════════════════════

        $driverAddresses_12082718342 = [
            [
                'full_address' => 'Đường Giải Phóng',
                'latitude' => 20.993491,
                'longitude' => 105.840678,
            ],
            [
                'full_address' => 'Phố Triệu Việt Vương',
                'latitude' => 21.014936,
                'longitude' => 105.850522,
            ],
            [
                'full_address' => 'Phố Bùi Thị Xuân',
                'latitude' => 21.016709,
                'longitude' => 105.849758,
            ],
        ];

        // Tạo 3 tài khoản tài xế cho hub Bưu điện CP16
        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_12082718342_{$i}@example.com";
            $phone = "092718342{$i}";
            $fullName = "Driver Bưu điện CP16 #$i";

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

            DriverProfile::updateOrCreate(
                ['user_id' => $driverUser->id],
                [
                    'full_name' => $fullName,
                    'email' => $email,
                    'phone' => $phone,
                    'province_code' => 1,
                    'post_office_id' => '12082718342',
                    'post_office_name' => 'Bưu điện CP16',
                    'post_office_address' => 'Phố Ông Ích Khiêm',
                    'post_office_lat' => '21.033627',
                    'post_office_lng' => '105.833826',
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

            $addr = $driverAddresses_12082718342[$i - 1];
            UserInfo::updateOrCreate(
                ['user_id' => $driverUser->id],
                [
                    'national_id' => '12082718342' . str_pad($i, 5, '0', STR_PAD_LEFT),
                    'tax_code' => 'TX12082718342' . str_pad($i, 3, '0', STR_PAD_LEFT),
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
