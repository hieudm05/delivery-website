<?php

namespace Database\Seeders;

use App\Models\Customer\Dashboard\Accounts\UserInfo;
use App\Models\Driver\DriverProfile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class NewPostOfficeSeeder extends Seeder
{
    /**
     * Generated: 12/20/2025, 11:19:09 AM
     * Total hubs: 100
     * Total drivers: 300
     */
    public function run(): void
    {

        // ════════════════════════════════════════════
        // HUB: Bưu điện văn hóa xã Hòa Tiến
        // ID: 419402268 | Sóc Sơn | (21.199897, 105.91829)
        // ════════════════════════════════════════════

        $hubUser = User::firstOrCreate(
            ['email' => 'hub_419402268@example.com'],
            [
                'phone' => '024' . str_pad('419402268', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu điện văn hóa xã Hòa Tiến',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '419402268',
                'hub_latitude' => '21.199897',
                'hub_longitude' => '105.91829',
                'hub_address' => 'Sóc Sơn, Hà Nội',
            ]
        );

        $driverAddresses_419402268 = [
            ['full_address' => 'Trường Trung học cơ sở Vọng Nguyệt', 'latitude' => 21.227992, 'longitude' => 105.951116],
            ['full_address' => 'Nhà hàng sinh thái Phước Long', 'latitude' => 21.206498, 'longitude' => 105.959197],
            ['full_address' => 'Trường Trung học cơ sở Xuân Thu', 'latitude' => 21.1951, 'longitude' => 105.894298],
        ];

        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_419402268_{$i}@example.com";
            $phone = "092" . substr(str_pad('419402268', 9, '0', STR_PAD_LEFT), -6) . $i;
            $fullName = "Driver Bưu điện văn hóa xã Hòa Tiến #{$i}";

            $driverUser = User::firstOrCreate(['email' => $email], [
                'phone' => $phone,
                'full_name' => $fullName,
                'role' => 'driver',
                'status' => 'active',
                'password_hash' => Hash::make('123456'),
            ]);

            DriverProfile::updateOrCreate(['user_id' => $driverUser->id], [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'province_code' => 1,
                'post_office_id' => '419402268',
                'post_office_name' => 'Bưu điện văn hóa xã Hòa Tiến',
                'post_office_address' => 'Sóc Sơn, Hà Nội',
                'post_office_lat' => '21.199897',
                'post_office_lng' => '105.91829',
                'post_office_phone' => '0241234567',
                'vehicle_type' => 'Xe máy',
                'license_number' => str_repeat($i, 8),
                'license_image' => 'license_image.png',
                'identity_image' => 'identity_image.png',
                'experience' => rand(1, 5) . ' năm',
                'status' => 'approved',
                'approved_at' => Carbon::now(),
            ]);

            $addr = $driverAddresses_419402268[$i - 1];
            UserInfo::updateOrCreate(['user_id' => $driverUser->id], [
                'national_id' => '419402268' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'tax_code' => 'TX419402268' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_of_birth' => '1995-01-0' . (($i % 9) + 1),
                'full_address' => $addr['full_address'],
                'address_detail' => 'Khu vực giao hàng chính',
                'latitude' => $addr['latitude'],
                'longitude' => $addr['longitude'],
                'province_code' => null,
                'district_code' => null,
                'ward_code' => null,
            ]);
        }

        // ════════════════════════════════════════════
        // HUB: Bưu cục Đông Anh
        // ID: 7178789277 | Đông Anh | (21.111715, 105.876786)
        // ════════════════════════════════════════════

        $hubUser = User::firstOrCreate(
            ['email' => 'hub_7178789277@example.com'],
            [
                'phone' => '024' . str_pad('7178789277', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu cục Đông Anh',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '7178789277',
                'hub_latitude' => '21.111715',
                'hub_longitude' => '105.876786',
                'hub_address' => 'Đông Anh, Hà Nội',
            ]
        );

        $driverAddresses_7178789277 = [
            ['full_address' => 'restaurant', 'latitude' => 21.092934, 'longitude' => 105.894539],
            ['full_address' => 'cafe', 'latitude' => 21.111861, 'longitude' => 105.873952],
            ['full_address' => 'Cà Phê Jmax', 'latitude' => 21.112084, 'longitude' => 105.872605],
        ];

        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_7178789277_{$i}@example.com";
            $phone = "092" . substr(str_pad('7178789277', 9, '0', STR_PAD_LEFT), -6) . $i;
            $fullName = "Driver Bưu cục Đông Anh #{$i}";

            $driverUser = User::firstOrCreate(['email' => $email], [
                'phone' => $phone,
                'full_name' => $fullName,
                'role' => 'driver',
                'status' => 'active',
                'password_hash' => Hash::make('123456'),
            ]);

            DriverProfile::updateOrCreate(['user_id' => $driverUser->id], [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'province_code' => 1,
                'post_office_id' => '7178789277',
                'post_office_name' => 'Bưu cục Đông Anh',
                'post_office_address' => 'Đông Anh, Hà Nội',
                'post_office_lat' => '21.111715',
                'post_office_lng' => '105.876786',
                'post_office_phone' => '0241234567',
                'vehicle_type' => 'Xe máy',
                'license_number' => str_repeat($i, 8),
                'license_image' => 'license_image.png',
                'identity_image' => 'identity_image.png',
                'experience' => rand(1, 5) . ' năm',
                'status' => 'approved',
                'approved_at' => Carbon::now(),
            ]);

            $addr = $driverAddresses_7178789277[$i - 1];
            UserInfo::updateOrCreate(['user_id' => $driverUser->id], [
                'national_id' => '7178789277' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'tax_code' => 'TX7178789277' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_of_birth' => '1995-01-0' . (($i % 9) + 1),
                'full_address' => $addr['full_address'],
                'address_detail' => 'Khu vực giao hàng chính',
                'latitude' => $addr['latitude'],
                'longitude' => $addr['longitude'],
                'province_code' => null,
                'district_code' => null,
                'ward_code' => null,
            ]);
        }

        // ════════════════════════════════════════════
        // HUB: Bưu cục Đông Anh
        // ID: 7178811275 | Đông Anh | (21.100403, 105.885349)
        // ════════════════════════════════════════════

        $hubUser = User::firstOrCreate(
            ['email' => 'hub_7178811275@example.com'],
            [
                'phone' => '024' . str_pad('7178811275', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu cục Đông Anh',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '7178811275',
                'hub_latitude' => '21.100403',
                'hub_longitude' => '105.885349',
                'hub_address' => 'Đông Anh, Hà Nội',
            ]
        );

        $driverAddresses_7178811275 = [
            ['full_address' => 'Cà Phê Mọc', 'latitude' => 21.065583, 'longitude' => 105.907614],
            ['full_address' => 'cafe', 'latitude' => 21.111861, 'longitude' => 105.873952],
            ['full_address' => 'cafe', 'latitude' => 21.063194, 'longitude' => 105.91125],
        ];

        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_7178811275_{$i}@example.com";
            $phone = "092" . substr(str_pad('7178811275', 9, '0', STR_PAD_LEFT), -6) . $i;
            $fullName = "Driver Bưu cục Đông Anh #{$i}";

            $driverUser = User::firstOrCreate(['email' => $email], [
                'phone' => $phone,
                'full_name' => $fullName,
                'role' => 'driver',
                'status' => 'active',
                'password_hash' => Hash::make('123456'),
            ]);

            DriverProfile::updateOrCreate(['user_id' => $driverUser->id], [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'province_code' => 1,
                'post_office_id' => '7178811275',
                'post_office_name' => 'Bưu cục Đông Anh',
                'post_office_address' => 'Đông Anh, Hà Nội',
                'post_office_lat' => '21.100403',
                'post_office_lng' => '105.885349',
                'post_office_phone' => '0241234567',
                'vehicle_type' => 'Xe máy',
                'license_number' => str_repeat($i, 8),
                'license_image' => 'license_image.png',
                'identity_image' => 'identity_image.png',
                'experience' => rand(1, 5) . ' năm',
                'status' => 'approved',
                'approved_at' => Carbon::now(),
            ]);

            $addr = $driverAddresses_7178811275[$i - 1];
            UserInfo::updateOrCreate(['user_id' => $driverUser->id], [
                'national_id' => '7178811275' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'tax_code' => 'TX7178811275' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_of_birth' => '1995-01-0' . (($i % 9) + 1),
                'full_address' => $addr['full_address'],
                'address_detail' => 'Khu vực giao hàng chính',
                'latitude' => $addr['latitude'],
                'longitude' => $addr['longitude'],
                'province_code' => null,
                'district_code' => null,
                'ward_code' => null,
            ]);
        }

        // ════════════════════════════════════════════
        // HUB: Bưu điện văn hóa xã Hòa Tiến
        // ID: 049596594 | Đông Anh | (21.199897, 105.91829)
        // ════════════════════════════════════════════

        $hubUser = User::firstOrCreate(
            ['email' => 'hub_049596594@example.com'],
            [
                'phone' => '024' . str_pad('049596594', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu điện văn hóa xã Hòa Tiến',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '049596594',
                'hub_latitude' => '21.199897',
                'hub_longitude' => '105.91829',
                'hub_address' => 'Đông Anh, Hà Nội',
            ]
        );

        $driverAddresses_049596594 = [
            ['full_address' => 'Quan Độ', 'latitude' => 21.16641, 'longitude' => 105.929408],
            ['full_address' => 'Nhà hàng sinh thái Phước Long', 'latitude' => 21.206498, 'longitude' => 105.959197],
            ['full_address' => 'Gần Bưu điện văn hóa xã Hòa Tiến', 'latitude' => 21.197836, 'longitude' => 105.916229],
        ];

        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_049596594_{$i}@example.com";
            $phone = "092" . substr(str_pad('049596594', 9, '0', STR_PAD_LEFT), -6) . $i;
            $fullName = "Driver Bưu điện văn hóa xã Hòa Tiến #{$i}";

            $driverUser = User::firstOrCreate(['email' => $email], [
                'phone' => $phone,
                'full_name' => $fullName,
                'role' => 'driver',
                'status' => 'active',
                'password_hash' => Hash::make('123456'),
            ]);

            DriverProfile::updateOrCreate(['user_id' => $driverUser->id], [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'province_code' => 1,
                'post_office_id' => '049596594',
                'post_office_name' => 'Bưu điện văn hóa xã Hòa Tiến',
                'post_office_address' => 'Đông Anh, Hà Nội',
                'post_office_lat' => '21.199897',
                'post_office_lng' => '105.91829',
                'post_office_phone' => '0241234567',
                'vehicle_type' => 'Xe máy',
                'license_number' => str_repeat($i, 8),
                'license_image' => 'license_image.png',
                'identity_image' => 'identity_image.png',
                'experience' => rand(1, 5) . ' năm',
                'status' => 'approved',
                'approved_at' => Carbon::now(),
            ]);

            $addr = $driverAddresses_049596594[$i - 1];
            UserInfo::updateOrCreate(['user_id' => $driverUser->id], [
                'national_id' => '049596594' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'tax_code' => 'TX049596594' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_of_birth' => '1995-01-0' . (($i % 9) + 1),
                'full_address' => $addr['full_address'],
                'address_detail' => 'Khu vực giao hàng chính',
                'latitude' => $addr['latitude'],
                'longitude' => $addr['longitude'],
                'province_code' => null,
                'district_code' => null,
                'ward_code' => null,
            ]);
        }

        // ════════════════════════════════════════════
        // HUB: Bưu cục Đông Anh
        // ID: 1272771553 | Đông Anh | (21.141394, 105.846251)
        // ════════════════════════════════════════════

        $hubUser = User::firstOrCreate(
            ['email' => 'hub_1272771553@example.com'],
            [
                'phone' => '024' . str_pad('1272771553', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu cục Đông Anh',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '1272771553',
                'hub_latitude' => '21.141394',
                'hub_longitude' => '105.846251',
                'hub_address' => 'Đường Cao Lỗ',
            ]
        );

        $driverAddresses_1272771553 = [
            ['full_address' => 'Trường cao đẳng nghề Việt Nam-Hàn Quốc Thành phố Hà Nội', 'latitude' => 21.175135, 'longitude' => 105.856856],
            ['full_address' => 'Agribank', 'latitude' => 21.111167, 'longitude' => 105.876815],
            ['full_address' => 'Gần Bưu cục Đông Anh', 'latitude' => 21.138629, 'longitude' => 105.843486],
        ];

        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_1272771553_{$i}@example.com";
            $phone = "092" . substr(str_pad('1272771553', 9, '0', STR_PAD_LEFT), -6) . $i;
            $fullName = "Driver Bưu cục Đông Anh #{$i}";

            $driverUser = User::firstOrCreate(['email' => $email], [
                'phone' => $phone,
                'full_name' => $fullName,
                'role' => 'driver',
                'status' => 'active',
                'password_hash' => Hash::make('123456'),
            ]);

            DriverProfile::updateOrCreate(['user_id' => $driverUser->id], [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'province_code' => 1,
                'post_office_id' => '1272771553',
                'post_office_name' => 'Bưu cục Đông Anh',
                'post_office_address' => 'Đường Cao Lỗ',
                'post_office_lat' => '21.141394',
                'post_office_lng' => '105.846251',
                'post_office_phone' => '0241234567',
                'vehicle_type' => 'Xe máy',
                'license_number' => str_repeat($i, 8),
                'license_image' => 'license_image.png',
                'identity_image' => 'identity_image.png',
                'experience' => rand(1, 5) . ' năm',
                'status' => 'approved',
                'approved_at' => Carbon::now(),
            ]);

            $addr = $driverAddresses_1272771553[$i - 1];
            UserInfo::updateOrCreate(['user_id' => $driverUser->id], [
                'national_id' => '1272771553' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'tax_code' => 'TX1272771553' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_of_birth' => '1995-01-0' . (($i % 9) + 1),
                'full_address' => $addr['full_address'],
                'address_detail' => 'Khu vực giao hàng chính',
                'latitude' => $addr['latitude'],
                'longitude' => $addr['longitude'],
                'province_code' => null,
                'district_code' => null,
                'ward_code' => null,
            ]);
        }

        // ════════════════════════════════════════════
        // HUB: Bưu Cục Giải Phóng
        // ID: 158118741 | Thanh Trì | (20.98325, 105.841459)
        // ════════════════════════════════════════════

        $hubUser = User::firstOrCreate(
            ['email' => 'hub_158118741@example.com'],
            [
                'phone' => '024' . str_pad('158118741', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu Cục Giải Phóng',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '158118741',
                'hub_latitude' => '20.98325',
                'hub_longitude' => '105.841459',
                'hub_address' => 'Đường Giải Phóng',
            ]
        );

        $driverAddresses_158118741 = [
            ['full_address' => 'Supremacy', 'latitude' => 20.987776, 'longitude' => 105.796982],
            ['full_address' => 'Phố Lê Đại Hành', 'latitude' => 21.009926, 'longitude' => 105.849986],
            ['full_address' => 'Võ Thị Sáu', 'latitude' => 21.00345, 'longitude' => 105.854851],
        ];

        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_158118741_{$i}@example.com";
            $phone = "092" . substr(str_pad('158118741', 9, '0', STR_PAD_LEFT), -6) . $i;
            $fullName = "Driver Bưu Cục Giải Phóng #{$i}";

            $driverUser = User::firstOrCreate(['email' => $email], [
                'phone' => $phone,
                'full_name' => $fullName,
                'role' => 'driver',
                'status' => 'active',
                'password_hash' => Hash::make('123456'),
            ]);

            DriverProfile::updateOrCreate(['user_id' => $driverUser->id], [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'province_code' => 1,
                'post_office_id' => '158118741',
                'post_office_name' => 'Bưu Cục Giải Phóng',
                'post_office_address' => 'Đường Giải Phóng',
                'post_office_lat' => '20.98325',
                'post_office_lng' => '105.841459',
                'post_office_phone' => '0241234567',
                'vehicle_type' => 'Xe máy',
                'license_number' => str_repeat($i, 8),
                'license_image' => 'license_image.png',
                'identity_image' => 'identity_image.png',
                'experience' => rand(1, 5) . ' năm',
                'status' => 'approved',
                'approved_at' => Carbon::now(),
            ]);

            $addr = $driverAddresses_158118741[$i - 1];
            UserInfo::updateOrCreate(['user_id' => $driverUser->id], [
                'national_id' => '158118741' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'tax_code' => 'TX158118741' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_of_birth' => '1995-01-0' . (($i % 9) + 1),
                'full_address' => $addr['full_address'],
                'address_detail' => 'Khu vực giao hàng chính',
                'latitude' => $addr['latitude'],
                'longitude' => $addr['longitude'],
                'province_code' => null,
                'district_code' => null,
                'ward_code' => null,
            ]);
        }

        // ════════════════════════════════════════════
        // HUB: VNPT VINAPHONE KHU VỰC 6
        // ID: 4945035121 | Thanh Trì | (20.91393, 105.852101)
        // ════════════════════════════════════════════

        $hubUser = User::firstOrCreate(
            ['email' => 'hub_4945035121@example.com'],
            [
                'phone' => '024' . str_pad('4945035121', 7, '0', STR_PAD_LEFT),
                'full_name' => 'VNPT VINAPHONE KHU VỰC 6',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '4945035121',
                'hub_latitude' => '20.91393',
                'hub_longitude' => '105.852101',
                'hub_address' => 'Khu Công Nghiệp Ngọc Hồi- Thanh Trì - Hà Nội',
            ]
        );

        $driverAddresses_4945035121 = [
            ['full_address' => 'restaurant', 'latitude' => 20.870509, 'longitude' => 105.863912],
            ['full_address' => 'MB', 'latitude' => 20.919058, 'longitude' => 105.851969],
            ['full_address' => 'Cafe Dinh', 'latitude' => 20.940082, 'longitude' => 105.854357],
        ];

        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_4945035121_{$i}@example.com";
            $phone = "092" . substr(str_pad('4945035121', 9, '0', STR_PAD_LEFT), -6) . $i;
            $fullName = "Driver VNPT VINAPHONE KHU VỰC 6 #{$i}";

            $driverUser = User::firstOrCreate(['email' => $email], [
                'phone' => $phone,
                'full_name' => $fullName,
                'role' => 'driver',
                'status' => 'active',
                'password_hash' => Hash::make('123456'),
            ]);

            DriverProfile::updateOrCreate(['user_id' => $driverUser->id], [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'province_code' => 1,
                'post_office_id' => '4945035121',
                'post_office_name' => 'VNPT VINAPHONE KHU VỰC 6',
                'post_office_address' => 'Khu Công Nghiệp Ngọc Hồi- Thanh Trì - Hà Nội',
                'post_office_lat' => '20.91393',
                'post_office_lng' => '105.852101',
                'post_office_phone' => '+84 24 3684 0271',
                'vehicle_type' => 'Xe máy',
                'license_number' => str_repeat($i, 8),
                'license_image' => 'license_image.png',
                'identity_image' => 'identity_image.png',
                'experience' => rand(1, 5) . ' năm',
                'status' => 'approved',
                'approved_at' => Carbon::now(),
            ]);

            $addr = $driverAddresses_4945035121[$i - 1];
            UserInfo::updateOrCreate(['user_id' => $driverUser->id], [
                'national_id' => '4945035121' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'tax_code' => 'TX4945035121' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_of_birth' => '1995-01-0' . (($i % 9) + 1),
                'full_address' => $addr['full_address'],
                'address_detail' => 'Khu vực giao hàng chính',
                'latitude' => $addr['latitude'],
                'longitude' => $addr['longitude'],
                'province_code' => null,
                'district_code' => null,
                'ward_code' => null,
            ]);
        }

        // ════════════════════════════════════════════
        // HUB: ViettelPost
        // ID: 713983512 | Thanh Trì | (20.974034, 105.835782)
        // ════════════════════════════════════════════

        $hubUser = User::firstOrCreate(
            ['email' => 'hub_713983512@example.com'],
            [
                'phone' => '024' . str_pad('713983512', 7, '0', STR_PAD_LEFT),
                'full_name' => 'ViettelPost',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '713983512',
                'hub_latitude' => '20.974034',
                'hub_longitude' => '105.835782',
                'hub_address' => 'Nguyễn Cảnh Dị',
            ]
        );

        $driverAddresses_713983512 = [
            ['full_address' => 'Cà Phê Hanoi 1990', 'latitude' => 21.017161, 'longitude' => 105.841155],
            ['full_address' => 'Quỳnh Lôi', 'latitude' => 21.001005, 'longitude' => 105.859948],
            ['full_address' => 'Phố Hoàng Cầu', 'latitude' => 21.016746, 'longitude' => 105.823955],
        ];

        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_713983512_{$i}@example.com";
            $phone = "092" . substr(str_pad('713983512', 9, '0', STR_PAD_LEFT), -6) . $i;
            $fullName = "Driver ViettelPost #{$i}";

            $driverUser = User::firstOrCreate(['email' => $email], [
                'phone' => $phone,
                'full_name' => $fullName,
                'role' => 'driver',
                'status' => 'active',
                'password_hash' => Hash::make('123456'),
            ]);

            DriverProfile::updateOrCreate(['user_id' => $driverUser->id], [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'province_code' => 1,
                'post_office_id' => '713983512',
                'post_office_name' => 'ViettelPost',
                'post_office_address' => 'Nguyễn Cảnh Dị',
                'post_office_lat' => '20.974034',
                'post_office_lng' => '105.835782',
                'post_office_phone' => '0241234567',
                'vehicle_type' => 'Xe máy',
                'license_number' => str_repeat($i, 8),
                'license_image' => 'license_image.png',
                'identity_image' => 'identity_image.png',
                'experience' => rand(1, 5) . ' năm',
                'status' => 'approved',
                'approved_at' => Carbon::now(),
            ]);

            $addr = $driverAddresses_713983512[$i - 1];
            UserInfo::updateOrCreate(['user_id' => $driverUser->id], [
                'national_id' => '713983512' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'tax_code' => 'TX713983512' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_of_birth' => '1995-01-0' . (($i % 9) + 1),
                'full_address' => $addr['full_address'],
                'address_detail' => 'Khu vực giao hàng chính',
                'latitude' => $addr['latitude'],
                'longitude' => $addr['longitude'],
                'province_code' => null,
                'district_code' => null,
                'ward_code' => null,
            ]);
        }

        // ════════════════════════════════════════════
        // HUB: Bưu Điện Yên Mỹ
        // ID: 6346457447 | Thanh Trì | (20.94548, 105.873301)
        // ════════════════════════════════════════════

        $hubUser = User::firstOrCreate(
            ['email' => 'hub_6346457447@example.com'],
            [
                'phone' => '024' . str_pad('6346457447', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu Điện Yên Mỹ',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '6346457447',
                'hub_latitude' => '20.94548',
                'hub_longitude' => '105.873301',
                'hub_address' => 'Thanh Trì, Hà Nội',
            ]
        );

        $driverAddresses_6346457447 = [
            ['full_address' => 'Ngõ 168 Phan Trọng Tuệ', 'latitude' => 20.950141, 'longitude' => 105.830846],
            ['full_address' => 'Ngõ 168 Phan Trọng Tuệ', 'latitude' => 20.950203, 'longitude' => 105.830421],
            ['full_address' => 'Trường Trung học cơ sở Thịnh Liệt', 'latitude' => 20.97436, 'longitude' => 105.846065],
        ];

        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_6346457447_{$i}@example.com";
            $phone = "092" . substr(str_pad('6346457447', 9, '0', STR_PAD_LEFT), -6) . $i;
            $fullName = "Driver Bưu Điện Yên Mỹ #{$i}";

            $driverUser = User::firstOrCreate(['email' => $email], [
                'phone' => $phone,
                'full_name' => $fullName,
                'role' => 'driver',
                'status' => 'active',
                'password_hash' => Hash::make('123456'),
            ]);

            DriverProfile::updateOrCreate(['user_id' => $driverUser->id], [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'province_code' => 1,
                'post_office_id' => '6346457447',
                'post_office_name' => 'Bưu Điện Yên Mỹ',
                'post_office_address' => 'Thanh Trì, Hà Nội',
                'post_office_lat' => '20.94548',
                'post_office_lng' => '105.873301',
                'post_office_phone' => '0241234567',
                'vehicle_type' => 'Xe máy',
                'license_number' => str_repeat($i, 8),
                'license_image' => 'license_image.png',
                'identity_image' => 'identity_image.png',
                'experience' => rand(1, 5) . ' năm',
                'status' => 'approved',
                'approved_at' => Carbon::now(),
            ]);

            $addr = $driverAddresses_6346457447[$i - 1];
            UserInfo::updateOrCreate(['user_id' => $driverUser->id], [
                'national_id' => '6346457447' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'tax_code' => 'TX6346457447' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_of_birth' => '1995-01-0' . (($i % 9) + 1),
                'full_address' => $addr['full_address'],
                'address_detail' => 'Khu vực giao hàng chính',
                'latitude' => $addr['latitude'],
                'longitude' => $addr['longitude'],
                'province_code' => null,
                'district_code' => null,
                'ward_code' => null,
            ]);
        }

        // ════════════════════════════════════════════
        // HUB: Bưu Cục Chợ Mơ
        // ID: 845580972 | Thanh Trì | (20.995698, 105.850091)
        // ════════════════════════════════════════════

        $hubUser = User::firstOrCreate(
            ['email' => 'hub_845580972@example.com'],
            [
                'phone' => '024' . str_pad('845580972', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu Cục Chợ Mơ',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '845580972',
                'hub_latitude' => '20.995698',
                'hub_longitude' => '105.850091',
                'hub_address' => 'Phố Minh Khai',
            ]
        );

        $driverAddresses_845580972 = [
            ['full_address' => 'Cofeli', 'latitude' => 21.025206, 'longitude' => 105.84536],
            ['full_address' => 'cafe', 'latitude' => 21.016941, 'longitude' => 105.848567],
            ['full_address' => 'Phố Nhà Thờ', 'latitude' => 21.028814, 'longitude' => 105.849647],
        ];

        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_845580972_{$i}@example.com";
            $phone = "092" . substr(str_pad('845580972', 9, '0', STR_PAD_LEFT), -6) . $i;
            $fullName = "Driver Bưu Cục Chợ Mơ #{$i}";

            $driverUser = User::firstOrCreate(['email' => $email], [
                'phone' => $phone,
                'full_name' => $fullName,
                'role' => 'driver',
                'status' => 'active',
                'password_hash' => Hash::make('123456'),
            ]);

            DriverProfile::updateOrCreate(['user_id' => $driverUser->id], [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'province_code' => 1,
                'post_office_id' => '845580972',
                'post_office_name' => 'Bưu Cục Chợ Mơ',
                'post_office_address' => 'Phố Minh Khai',
                'post_office_lat' => '20.995698',
                'post_office_lng' => '105.850091',
                'post_office_phone' => '0241234567',
                'vehicle_type' => 'Xe máy',
                'license_number' => str_repeat($i, 8),
                'license_image' => 'license_image.png',
                'identity_image' => 'identity_image.png',
                'experience' => rand(1, 5) . ' năm',
                'status' => 'approved',
                'approved_at' => Carbon::now(),
            ]);

            $addr = $driverAddresses_845580972[$i - 1];
            UserInfo::updateOrCreate(['user_id' => $driverUser->id], [
                'national_id' => '845580972' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'tax_code' => 'TX845580972' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_of_birth' => '1995-01-0' . (($i % 9) + 1),
                'full_address' => $addr['full_address'],
                'address_detail' => 'Khu vực giao hàng chính',
                'latitude' => $addr['latitude'],
                'longitude' => $addr['longitude'],
                'province_code' => null,
                'district_code' => null,
                'ward_code' => null,
            ]);
        }

        // ════════════════════════════════════════════
        // HUB: Bưu Điện Khương Mai
        // ID: 690525893 | Thanh Trì | (20.997397, 105.826758)
        // ════════════════════════════════════════════

        $hubUser = User::firstOrCreate(
            ['email' => 'hub_690525893@example.com'],
            [
                'phone' => '024' . str_pad('690525893', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu Điện Khương Mai',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '690525893',
                'hub_latitude' => '20.997397',
                'hub_longitude' => '105.826758',
                'hub_address' => 'Phố Nguyễn Ngọc Nại',
            ]
        );

        $driverAddresses_690525893 = [
            ['full_address' => 'cafe', 'latitude' => 21.036736, 'longitude' => 105.847967],
            ['full_address' => 'Lãn Ông', 'latitude' => 21.035342, 'longitude' => 105.849859],
            ['full_address' => 'Highlands Coffee', 'latitude' => 21.013514, 'longitude' => 105.803246],
        ];

        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_690525893_{$i}@example.com";
            $phone = "092" . substr(str_pad('690525893', 9, '0', STR_PAD_LEFT), -6) . $i;
            $fullName = "Driver Bưu Điện Khương Mai #{$i}";

            $driverUser = User::firstOrCreate(['email' => $email], [
                'phone' => $phone,
                'full_name' => $fullName,
                'role' => 'driver',
                'status' => 'active',
                'password_hash' => Hash::make('123456'),
            ]);

            DriverProfile::updateOrCreate(['user_id' => $driverUser->id], [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'province_code' => 1,
                'post_office_id' => '690525893',
                'post_office_name' => 'Bưu Điện Khương Mai',
                'post_office_address' => 'Phố Nguyễn Ngọc Nại',
                'post_office_lat' => '20.997397',
                'post_office_lng' => '105.826758',
                'post_office_phone' => '0241234567',
                'vehicle_type' => 'Xe máy',
                'license_number' => str_repeat($i, 8),
                'license_image' => 'license_image.png',
                'identity_image' => 'identity_image.png',
                'experience' => rand(1, 5) . ' năm',
                'status' => 'approved',
                'approved_at' => Carbon::now(),
            ]);

            $addr = $driverAddresses_690525893[$i - 1];
            UserInfo::updateOrCreate(['user_id' => $driverUser->id], [
                'national_id' => '690525893' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'tax_code' => 'TX690525893' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_of_birth' => '1995-01-0' . (($i % 9) + 1),
                'full_address' => $addr['full_address'],
                'address_detail' => 'Khu vực giao hàng chính',
                'latitude' => $addr['latitude'],
                'longitude' => $addr['longitude'],
                'province_code' => null,
                'district_code' => null,
                'ward_code' => null,
            ]);
        }

        // ════════════════════════════════════════════
        // HUB: Bưu Điện Thanh Xuân Bắc
        // ID: 9420438149 | Thanh Trì | (20.991017, 105.801926)
        // ════════════════════════════════════════════

        $hubUser = User::firstOrCreate(
            ['email' => 'hub_9420438149@example.com'],
            [
                'phone' => '024' . str_pad('9420438149', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu Điện Thanh Xuân Bắc',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '9420438149',
                'hub_latitude' => '20.991017',
                'hub_longitude' => '105.801926',
                'hub_address' => 'Thanh Trì, Hà Nội',
            ]
        );

        $driverAddresses_9420438149 = [
            ['full_address' => 'Phố Hoàng Cầu', 'latitude' => 21.019129, 'longitude' => 105.825176],
            ['full_address' => 'Cơm Nấm Việt Hà Thành', 'latitude' => 21.018967, 'longitude' => 105.828475],
            ['full_address' => 'Phố Xã Đàn', 'latitude' => 21.017034, 'longitude' => 105.831713],
        ];

        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_9420438149_{$i}@example.com";
            $phone = "092" . substr(str_pad('9420438149', 9, '0', STR_PAD_LEFT), -6) . $i;
            $fullName = "Driver Bưu Điện Thanh Xuân Bắc #{$i}";

            $driverUser = User::firstOrCreate(['email' => $email], [
                'phone' => $phone,
                'full_name' => $fullName,
                'role' => 'driver',
                'status' => 'active',
                'password_hash' => Hash::make('123456'),
            ]);

            DriverProfile::updateOrCreate(['user_id' => $driverUser->id], [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'province_code' => 1,
                'post_office_id' => '9420438149',
                'post_office_name' => 'Bưu Điện Thanh Xuân Bắc',
                'post_office_address' => 'Thanh Trì, Hà Nội',
                'post_office_lat' => '20.991017',
                'post_office_lng' => '105.801926',
                'post_office_phone' => '0241234567',
                'vehicle_type' => 'Xe máy',
                'license_number' => str_repeat($i, 8),
                'license_image' => 'license_image.png',
                'identity_image' => 'identity_image.png',
                'experience' => rand(1, 5) . ' năm',
                'status' => 'approved',
                'approved_at' => Carbon::now(),
            ]);

            $addr = $driverAddresses_9420438149[$i - 1];
            UserInfo::updateOrCreate(['user_id' => $driverUser->id], [
                'national_id' => '9420438149' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'tax_code' => 'TX9420438149' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_of_birth' => '1995-01-0' . (($i % 9) + 1),
                'full_address' => $addr['full_address'],
                'address_detail' => 'Khu vực giao hàng chính',
                'latitude' => $addr['latitude'],
                'longitude' => $addr['longitude'],
                'province_code' => null,
                'district_code' => null,
                'ward_code' => null,
            ]);
        }

        // ════════════════════════════════════════════
        // HUB: Bưu Điện Khương Đình
        // ID: 552474966 | Thanh Trì | (20.997695, 105.813458)
        // ════════════════════════════════════════════

        $hubUser = User::firstOrCreate(
            ['email' => 'hub_552474966@example.com'],
            [
                'phone' => '024' . str_pad('552474966', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu Điện Khương Đình',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '552474966',
                'hub_latitude' => '20.997695',
                'hub_longitude' => '105.813458',
                'hub_address' => 'Đường Khương Đình',
            ]
        );

        $driverAddresses_552474966 = [
            ['full_address' => 'Five Star Mỹ Đình', 'latitude' => 21.018451, 'longitude' => 105.775517],
            ['full_address' => 'Cà Phê Fimo Sp', 'latitude' => 21.019507, 'longitude' => 105.785899],
            ['full_address' => 'Phố Trần Thái Tông', 'latitude' => 21.034271, 'longitude' => 105.78921],
        ];

        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_552474966_{$i}@example.com";
            $phone = "092" . substr(str_pad('552474966', 9, '0', STR_PAD_LEFT), -6) . $i;
            $fullName = "Driver Bưu Điện Khương Đình #{$i}";

            $driverUser = User::firstOrCreate(['email' => $email], [
                'phone' => $phone,
                'full_name' => $fullName,
                'role' => 'driver',
                'status' => 'active',
                'password_hash' => Hash::make('123456'),
            ]);

            DriverProfile::updateOrCreate(['user_id' => $driverUser->id], [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'province_code' => 1,
                'post_office_id' => '552474966',
                'post_office_name' => 'Bưu Điện Khương Đình',
                'post_office_address' => 'Đường Khương Đình',
                'post_office_lat' => '20.997695',
                'post_office_lng' => '105.813458',
                'post_office_phone' => '0241234567',
                'vehicle_type' => 'Xe máy',
                'license_number' => str_repeat($i, 8),
                'license_image' => 'license_image.png',
                'identity_image' => 'identity_image.png',
                'experience' => rand(1, 5) . ' năm',
                'status' => 'approved',
                'approved_at' => Carbon::now(),
            ]);

            $addr = $driverAddresses_552474966[$i - 1];
            UserInfo::updateOrCreate(['user_id' => $driverUser->id], [
                'national_id' => '552474966' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'tax_code' => 'TX552474966' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_of_birth' => '1995-01-0' . (($i % 9) + 1),
                'full_address' => $addr['full_address'],
                'address_detail' => 'Khu vực giao hàng chính',
                'latitude' => $addr['latitude'],
                'longitude' => $addr['longitude'],
                'province_code' => null,
                'district_code' => null,
                'ward_code' => null,
            ]);
        }

        // ════════════════════════════════════════════
        // HUB: Bưu điện Việt Nam
        // ID: 058803836 | Thanh Trì | (20.988763, 105.84682)
        // ════════════════════════════════════════════

        $hubUser = User::firstOrCreate(
            ['email' => 'hub_058803836@example.com'],
            [
                'phone' => '024' . str_pad('058803836', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu điện Việt Nam',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '058803836',
                'hub_latitude' => '20.988763',
                'hub_longitude' => '105.84682',
                'hub_address' => 'Phố Nguyễn An Ninh',
            ]
        );

        $driverAddresses_058803836 = [
            ['full_address' => 'vân châu ( p102 e7 thanh xuân bắc)', 'latitude' => 20.991163, 'longitude' => 105.799139],
            ['full_address' => 'Nhà ăn sinh viên quốc tế', 'latitude' => 21.005433, 'longitude' => 105.846409],
            ['full_address' => 'HSBC', 'latitude' => 21.029292, 'longitude' => 105.850411],
        ];

        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_058803836_{$i}@example.com";
            $phone = "092" . substr(str_pad('058803836', 9, '0', STR_PAD_LEFT), -6) . $i;
            $fullName = "Driver Bưu điện Việt Nam #{$i}";

            $driverUser = User::firstOrCreate(['email' => $email], [
                'phone' => $phone,
                'full_name' => $fullName,
                'role' => 'driver',
                'status' => 'active',
                'password_hash' => Hash::make('123456'),
            ]);

            DriverProfile::updateOrCreate(['user_id' => $driverUser->id], [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'province_code' => 1,
                'post_office_id' => '058803836',
                'post_office_name' => 'Bưu điện Việt Nam',
                'post_office_address' => 'Phố Nguyễn An Ninh',
                'post_office_lat' => '20.988763',
                'post_office_lng' => '105.84682',
                'post_office_phone' => '0241234567',
                'vehicle_type' => 'Xe máy',
                'license_number' => str_repeat($i, 8),
                'license_image' => 'license_image.png',
                'identity_image' => 'identity_image.png',
                'experience' => rand(1, 5) . ' năm',
                'status' => 'approved',
                'approved_at' => Carbon::now(),
            ]);

            $addr = $driverAddresses_058803836[$i - 1];
            UserInfo::updateOrCreate(['user_id' => $driverUser->id], [
                'national_id' => '058803836' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'tax_code' => 'TX058803836' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_of_birth' => '1995-01-0' . (($i % 9) + 1),
                'full_address' => $addr['full_address'],
                'address_detail' => 'Khu vực giao hàng chính',
                'latitude' => $addr['latitude'],
                'longitude' => $addr['longitude'],
                'province_code' => null,
                'district_code' => null,
                'ward_code' => null,
            ]);
        }

        // ════════════════════════════════════════════
        // HUB: Bưu Điện Việt Nam
        // ID: 9683330882 | Thanh Trì | (20.969713, 105.826821)
        // ════════════════════════════════════════════

        $hubUser = User::firstOrCreate(
            ['email' => 'hub_9683330882@example.com'],
            [
                'phone' => '024' . str_pad('9683330882', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu Điện Việt Nam',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '9683330882',
                'hub_latitude' => '20.969713',
                'hub_longitude' => '105.826821',
                'hub_address' => 'Thanh Trì, Hà Nội',
            ]
        );

        $driverAddresses_9683330882 = [
            ['full_address' => 'Phố Chùa Bộc', 'latitude' => 21.006366, 'longitude' => 105.830526],
            ['full_address' => 'Ngõ 192 Lê Trọng Tấn', 'latitude' => 20.988054, 'longitude' => 105.826167],
            ['full_address' => 'Starbucks', 'latitude' => 20.987193, 'longitude' => 105.786059],
        ];

        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_9683330882_{$i}@example.com";
            $phone = "092" . substr(str_pad('9683330882', 9, '0', STR_PAD_LEFT), -6) . $i;
            $fullName = "Driver Bưu Điện Việt Nam #{$i}";

            $driverUser = User::firstOrCreate(['email' => $email], [
                'phone' => $phone,
                'full_name' => $fullName,
                'role' => 'driver',
                'status' => 'active',
                'password_hash' => Hash::make('123456'),
            ]);

            DriverProfile::updateOrCreate(['user_id' => $driverUser->id], [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'province_code' => 1,
                'post_office_id' => '9683330882',
                'post_office_name' => 'Bưu Điện Việt Nam',
                'post_office_address' => 'Thanh Trì, Hà Nội',
                'post_office_lat' => '20.969713',
                'post_office_lng' => '105.826821',
                'post_office_phone' => '+84 243 641 4342',
                'vehicle_type' => 'Xe máy',
                'license_number' => str_repeat($i, 8),
                'license_image' => 'license_image.png',
                'identity_image' => 'identity_image.png',
                'experience' => rand(1, 5) . ' năm',
                'status' => 'approved',
                'approved_at' => Carbon::now(),
            ]);

            $addr = $driverAddresses_9683330882[$i - 1];
            UserInfo::updateOrCreate(['user_id' => $driverUser->id], [
                'national_id' => '9683330882' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'tax_code' => 'TX9683330882' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_of_birth' => '1995-01-0' . (($i % 9) + 1),
                'full_address' => $addr['full_address'],
                'address_detail' => 'Khu vực giao hàng chính',
                'latitude' => $addr['latitude'],
                'longitude' => $addr['longitude'],
                'province_code' => null,
                'district_code' => null,
                'ward_code' => null,
            ]);
        }

        // ════════════════════════════════════════════
        // HUB: Bưu Cục Hạ Đình
        // ID: 1040467292 | Thanh Trì | (20.989545, 105.807634)
        // ════════════════════════════════════════════

        $hubUser = User::firstOrCreate(
            ['email' => 'hub_1040467292@example.com'],
            [
                'phone' => '024' . str_pad('1040467292', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu Cục Hạ Đình',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '1040467292',
                'hub_latitude' => '20.989545',
                'hub_longitude' => '105.807634',
                'hub_address' => 'Thanh Trì, Hà Nội',
            ]
        );

        $driverAddresses_1040467292 = [
            ['full_address' => 'school', 'latitude' => 21.009168, 'longitude' => 105.806542],
            ['full_address' => 'Phó Thành Công', 'latitude' => 21.019787, 'longitude' => 105.816214],
            ['full_address' => 'Trường Trung học cơ sở Tô Hiến Thành', 'latitude' => 21.01829, 'longitude' => 105.827483],
        ];

        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_1040467292_{$i}@example.com";
            $phone = "092" . substr(str_pad('1040467292', 9, '0', STR_PAD_LEFT), -6) . $i;
            $fullName = "Driver Bưu Cục Hạ Đình #{$i}";

            $driverUser = User::firstOrCreate(['email' => $email], [
                'phone' => $phone,
                'full_name' => $fullName,
                'role' => 'driver',
                'status' => 'active',
                'password_hash' => Hash::make('123456'),
            ]);

            DriverProfile::updateOrCreate(['user_id' => $driverUser->id], [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'province_code' => 1,
                'post_office_id' => '1040467292',
                'post_office_name' => 'Bưu Cục Hạ Đình',
                'post_office_address' => 'Thanh Trì, Hà Nội',
                'post_office_lat' => '20.989545',
                'post_office_lng' => '105.807634',
                'post_office_phone' => '0241234567',
                'vehicle_type' => 'Xe máy',
                'license_number' => str_repeat($i, 8),
                'license_image' => 'license_image.png',
                'identity_image' => 'identity_image.png',
                'experience' => rand(1, 5) . ' năm',
                'status' => 'approved',
                'approved_at' => Carbon::now(),
            ]);

            $addr = $driverAddresses_1040467292[$i - 1];
            UserInfo::updateOrCreate(['user_id' => $driverUser->id], [
                'national_id' => '1040467292' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'tax_code' => 'TX1040467292' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_of_birth' => '1995-01-0' . (($i % 9) + 1),
                'full_address' => $addr['full_address'],
                'address_detail' => 'Khu vực giao hàng chính',
                'latitude' => $addr['latitude'],
                'longitude' => $addr['longitude'],
                'province_code' => null,
                'district_code' => null,
                'ward_code' => null,
            ]);
        }

        // ════════════════════════════════════════════
        // HUB: Bưu điện Định Công
        // ID: 1168580666 | Thanh Trì | (20.987737, 105.83176)
        // ════════════════════════════════════════════

        $hubUser = User::firstOrCreate(
            ['email' => 'hub_1168580666@example.com'],
            [
                'phone' => '024' . str_pad('1168580666', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu điện Định Công',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '1168580666',
                'hub_latitude' => '20.987737',
                'hub_longitude' => '105.83176',
                'hub_address' => 'Thanh Trì, Hà Nội',
            ]
        );

        $driverAddresses_1168580666 = [
            ['full_address' => 'Phố Ngô Quyền', 'latitude' => 21.021422, 'longitude' => 105.854253],
            ['full_address' => 'Nhà Hàng Classico', 'latitude' => 21.025414, 'longitude' => 105.84514],
            ['full_address' => 'Nhà hàng Nam Phương', 'latitude' => 21.021755, 'longitude' => 105.856386],
        ];

        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_1168580666_{$i}@example.com";
            $phone = "092" . substr(str_pad('1168580666', 9, '0', STR_PAD_LEFT), -6) . $i;
            $fullName = "Driver Bưu điện Định Công #{$i}";

            $driverUser = User::firstOrCreate(['email' => $email], [
                'phone' => $phone,
                'full_name' => $fullName,
                'role' => 'driver',
                'status' => 'active',
                'password_hash' => Hash::make('123456'),
            ]);

            DriverProfile::updateOrCreate(['user_id' => $driverUser->id], [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'province_code' => 1,
                'post_office_id' => '1168580666',
                'post_office_name' => 'Bưu điện Định Công',
                'post_office_address' => 'Thanh Trì, Hà Nội',
                'post_office_lat' => '20.987737',
                'post_office_lng' => '105.83176',
                'post_office_phone' => '0241234567',
                'vehicle_type' => 'Xe máy',
                'license_number' => str_repeat($i, 8),
                'license_image' => 'license_image.png',
                'identity_image' => 'identity_image.png',
                'experience' => rand(1, 5) . ' năm',
                'status' => 'approved',
                'approved_at' => Carbon::now(),
            ]);

            $addr = $driverAddresses_1168580666[$i - 1];
            UserInfo::updateOrCreate(['user_id' => $driverUser->id], [
                'national_id' => '1168580666' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'tax_code' => 'TX1168580666' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_of_birth' => '1995-01-0' . (($i % 9) + 1),
                'full_address' => $addr['full_address'],
                'address_detail' => 'Khu vực giao hàng chính',
                'latitude' => $addr['latitude'],
                'longitude' => $addr['longitude'],
                'province_code' => null,
                'district_code' => null,
                'ward_code' => null,
            ]);
        }

        // ════════════════════════════════════════════
        // HUB: Bưu điện Thanh Xuân
        // ID: 1236192820 | Thanh Trì | (20.997924, 105.809241)
        // ════════════════════════════════════════════

        $hubUser = User::firstOrCreate(
            ['email' => 'hub_1236192820@example.com'],
            [
                'phone' => '024' . str_pad('1236192820', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu điện Thanh Xuân',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '1236192820',
                'hub_latitude' => '20.997924',
                'hub_longitude' => '105.809241',
                'hub_address' => 'Thanh Trì, Hà Nội',
            ]
        );

        $driverAddresses_1236192820 = [
            ['full_address' => 'Phố Lưu Quang Vũ', 'latitude' => 21.015697, 'longitude' => 105.800953],
            ['full_address' => 'Thái Hà', 'latitude' => 21.014991, 'longitude' => 105.815457],
            ['full_address' => 'Phố Bùi Thị Xuân', 'latitude' => 21.014353, 'longitude' => 105.849972],
        ];

        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_1236192820_{$i}@example.com";
            $phone = "092" . substr(str_pad('1236192820', 9, '0', STR_PAD_LEFT), -6) . $i;
            $fullName = "Driver Bưu điện Thanh Xuân #{$i}";

            $driverUser = User::firstOrCreate(['email' => $email], [
                'phone' => $phone,
                'full_name' => $fullName,
                'role' => 'driver',
                'status' => 'active',
                'password_hash' => Hash::make('123456'),
            ]);

            DriverProfile::updateOrCreate(['user_id' => $driverUser->id], [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'province_code' => 1,
                'post_office_id' => '1236192820',
                'post_office_name' => 'Bưu điện Thanh Xuân',
                'post_office_address' => 'Thanh Trì, Hà Nội',
                'post_office_lat' => '20.997924',
                'post_office_lng' => '105.809241',
                'post_office_phone' => '0241234567',
                'vehicle_type' => 'Xe máy',
                'license_number' => str_repeat($i, 8),
                'license_image' => 'license_image.png',
                'identity_image' => 'identity_image.png',
                'experience' => rand(1, 5) . ' năm',
                'status' => 'approved',
                'approved_at' => Carbon::now(),
            ]);

            $addr = $driverAddresses_1236192820[$i - 1];
            UserInfo::updateOrCreate(['user_id' => $driverUser->id], [
                'national_id' => '1236192820' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'tax_code' => 'TX1236192820' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_of_birth' => '1995-01-0' . (($i % 9) + 1),
                'full_address' => $addr['full_address'],
                'address_detail' => 'Khu vực giao hàng chính',
                'latitude' => $addr['latitude'],
                'longitude' => $addr['longitude'],
                'province_code' => null,
                'district_code' => null,
                'ward_code' => null,
            ]);
        }

        // ════════════════════════════════════════════
        // HUB: Bưu điện huyện Thanh Trì
        // ID: 1267912606 | Thanh Trì | (20.945443, 105.845203)
        // ════════════════════════════════════════════

        $hubUser = User::firstOrCreate(
            ['email' => 'hub_1267912606@example.com'],
            [
                'phone' => '024' . str_pad('1267912606', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu điện huyện Thanh Trì',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '1267912606',
                'hub_latitude' => '20.945443',
                'hub_longitude' => '105.845203',
                'hub_address' => 'Đường Nguyễn Bặc',
            ]
        );

        $driverAddresses_1267912606 = [
            ['full_address' => 'Khu đô thị Tây Nam Linh Đàm', 'latitude' => 20.963617, 'longitude' => 105.822852],
            ['full_address' => 'Phố Trần Nguyên Đán', 'latitude' => 20.986735, 'longitude' => 105.83225],
            ['full_address' => 'Highlands Coffee', 'latitude' => 20.963955, 'longitude' => 105.825487],
        ];

        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_1267912606_{$i}@example.com";
            $phone = "092" . substr(str_pad('1267912606', 9, '0', STR_PAD_LEFT), -6) . $i;
            $fullName = "Driver Bưu điện huyện Thanh Trì #{$i}";

            $driverUser = User::firstOrCreate(['email' => $email], [
                'phone' => $phone,
                'full_name' => $fullName,
                'role' => 'driver',
                'status' => 'active',
                'password_hash' => Hash::make('123456'),
            ]);

            DriverProfile::updateOrCreate(['user_id' => $driverUser->id], [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'province_code' => 1,
                'post_office_id' => '1267912606',
                'post_office_name' => 'Bưu điện huyện Thanh Trì',
                'post_office_address' => 'Đường Nguyễn Bặc',
                'post_office_lat' => '20.945443',
                'post_office_lng' => '105.845203',
                'post_office_phone' => '+84 024 3861 5218',
                'vehicle_type' => 'Xe máy',
                'license_number' => str_repeat($i, 8),
                'license_image' => 'license_image.png',
                'identity_image' => 'identity_image.png',
                'experience' => rand(1, 5) . ' năm',
                'status' => 'approved',
                'approved_at' => Carbon::now(),
            ]);

            $addr = $driverAddresses_1267912606[$i - 1];
            UserInfo::updateOrCreate(['user_id' => $driverUser->id], [
                'national_id' => '1267912606' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'tax_code' => 'TX1267912606' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_of_birth' => '1995-01-0' . (($i % 9) + 1),
                'full_address' => $addr['full_address'],
                'address_detail' => 'Khu vực giao hàng chính',
                'latitude' => $addr['latitude'],
                'longitude' => $addr['longitude'],
                'province_code' => null,
                'district_code' => null,
                'ward_code' => null,
            ]);
        }

        // ════════════════════════════════════════════
        // HUB: Bưu điện Đông Mỹ
        // ID: 1267912610 | Thanh Trì | (20.917772, 105.866754)
        // ════════════════════════════════════════════

        $hubUser = User::firstOrCreate(
            ['email' => 'hub_1267912610@example.com'],
            [
                'phone' => '024' . str_pad('1267912610', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu điện Đông Mỹ',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '1267912610',
                'hub_latitude' => '20.917772',
                'hub_longitude' => '105.866754',
                'hub_address' => 'Thanh Trì, Hà Nội',
            ]
        );

        $driverAddresses_1267912610 = [
            ['full_address' => 'Ngọc Thạch Quán', 'latitude' => 20.873274, 'longitude' => 105.86072],
            ['full_address' => 'Cafe Dinh', 'latitude' => 20.940082, 'longitude' => 105.854357],
            ['full_address' => 'Trường Trung học cơ sở Văn Bình', 'latitude' => 20.880293, 'longitude' => 105.863765],
        ];

        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_1267912610_{$i}@example.com";
            $phone = "092" . substr(str_pad('1267912610', 9, '0', STR_PAD_LEFT), -6) . $i;
            $fullName = "Driver Bưu điện Đông Mỹ #{$i}";

            $driverUser = User::firstOrCreate(['email' => $email], [
                'phone' => $phone,
                'full_name' => $fullName,
                'role' => 'driver',
                'status' => 'active',
                'password_hash' => Hash::make('123456'),
            ]);

            DriverProfile::updateOrCreate(['user_id' => $driverUser->id], [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'province_code' => 1,
                'post_office_id' => '1267912610',
                'post_office_name' => 'Bưu điện Đông Mỹ',
                'post_office_address' => 'Thanh Trì, Hà Nội',
                'post_office_lat' => '20.917772',
                'post_office_lng' => '105.866754',
                'post_office_phone' => '0241234567',
                'vehicle_type' => 'Xe máy',
                'license_number' => str_repeat($i, 8),
                'license_image' => 'license_image.png',
                'identity_image' => 'identity_image.png',
                'experience' => rand(1, 5) . ' năm',
                'status' => 'approved',
                'approved_at' => Carbon::now(),
            ]);

            $addr = $driverAddresses_1267912610[$i - 1];
            UserInfo::updateOrCreate(['user_id' => $driverUser->id], [
                'national_id' => '1267912610' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'tax_code' => 'TX1267912610' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_of_birth' => '1995-01-0' . (($i % 9) + 1),
                'full_address' => $addr['full_address'],
                'address_detail' => 'Khu vực giao hàng chính',
                'latitude' => $addr['latitude'],
                'longitude' => $addr['longitude'],
                'province_code' => null,
                'district_code' => null,
                'ward_code' => null,
            ]);
        }

        // ════════════════════════════════════════════
        // HUB: Bưu điện huyện Thường Tín
        // ID: 1267912611 | Thường Tín | (20.869593, 105.865362)
        // ════════════════════════════════════════════

        $hubUser = User::firstOrCreate(
            ['email' => 'hub_1267912611@example.com'],
            [
                'phone' => '024' . str_pad('1267912611', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu điện huyện Thường Tín',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '1267912611',
                'hub_latitude' => '20.869593',
                'hub_longitude' => '105.865362',
                'hub_address' => 'Thường Tín, Hà Nội',
            ]
        );

        $driverAddresses_1267912611 = [
            ['full_address' => 'Bún bò Huế O Hường', 'latitude' => 20.867641, 'longitude' => 105.857856],
            ['full_address' => 'Trường Trung học cơ sở Thư Phú', 'latitude' => 20.86046, 'longitude' => 105.906146],
            ['full_address' => 'restaurant', 'latitude' => 20.870509, 'longitude' => 105.863912],
        ];

        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_1267912611_{$i}@example.com";
            $phone = "092" . substr(str_pad('1267912611', 9, '0', STR_PAD_LEFT), -6) . $i;
            $fullName = "Driver Bưu điện huyện Thường Tín #{$i}";

            $driverUser = User::firstOrCreate(['email' => $email], [
                'phone' => $phone,
                'full_name' => $fullName,
                'role' => 'driver',
                'status' => 'active',
                'password_hash' => Hash::make('123456'),
            ]);

            DriverProfile::updateOrCreate(['user_id' => $driverUser->id], [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'province_code' => 1,
                'post_office_id' => '1267912611',
                'post_office_name' => 'Bưu điện huyện Thường Tín',
                'post_office_address' => 'Thường Tín, Hà Nội',
                'post_office_lat' => '20.869593',
                'post_office_lng' => '105.865362',
                'post_office_phone' => '0241234567',
                'vehicle_type' => 'Xe máy',
                'license_number' => str_repeat($i, 8),
                'license_image' => 'license_image.png',
                'identity_image' => 'identity_image.png',
                'experience' => rand(1, 5) . ' năm',
                'status' => 'approved',
                'approved_at' => Carbon::now(),
            ]);

            $addr = $driverAddresses_1267912611[$i - 1];
            UserInfo::updateOrCreate(['user_id' => $driverUser->id], [
                'national_id' => '1267912611' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'tax_code' => 'TX1267912611' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_of_birth' => '1995-01-0' . (($i % 9) + 1),
                'full_address' => $addr['full_address'],
                'address_detail' => 'Khu vực giao hàng chính',
                'latitude' => $addr['latitude'],
                'longitude' => $addr['longitude'],
                'province_code' => null,
                'district_code' => null,
                'ward_code' => null,
            ]);
        }

        // ════════════════════════════════════════════
        // HUB: Bưu cục Đức Hợp
        // ID: 7858023071 | Phú Xuyên | (20.725727, 105.993557)
        // ════════════════════════════════════════════

        $hubUser = User::firstOrCreate(
            ['email' => 'hub_7858023071@example.com'],
            [
                'phone' => '024' . str_pad('7858023071', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu cục Đức Hợp',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '7858023071',
                'hub_latitude' => '20.725727',
                'hub_longitude' => '105.993557',
                'hub_address' => 'Phú Xuyên, Hà Nội',
            ]
        );

        $driverAddresses_7858023071 = [
            ['full_address' => 'Quán ăn Hà hà', 'latitude' => 20.759927, 'longitude' => 105.988524],
            ['full_address' => 'Trường Trung học cơ sở Khai Thái', 'latitude' => 20.723167, 'longitude' => 105.958395],
            ['full_address' => 'Trường Trung học cơ sở Tri Thủy', 'latitude' => 20.70035, 'longitude' => 105.955216],
        ];

        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_7858023071_{$i}@example.com";
            $phone = "092" . substr(str_pad('7858023071', 9, '0', STR_PAD_LEFT), -6) . $i;
            $fullName = "Driver Bưu cục Đức Hợp #{$i}";

            $driverUser = User::firstOrCreate(['email' => $email], [
                'phone' => $phone,
                'full_name' => $fullName,
                'role' => 'driver',
                'status' => 'active',
                'password_hash' => Hash::make('123456'),
            ]);

            DriverProfile::updateOrCreate(['user_id' => $driverUser->id], [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'province_code' => 1,
                'post_office_id' => '7858023071',
                'post_office_name' => 'Bưu cục Đức Hợp',
                'post_office_address' => 'Phú Xuyên, Hà Nội',
                'post_office_lat' => '20.725727',
                'post_office_lng' => '105.993557',
                'post_office_phone' => '0241234567',
                'vehicle_type' => 'Xe máy',
                'license_number' => str_repeat($i, 8),
                'license_image' => 'license_image.png',
                'identity_image' => 'identity_image.png',
                'experience' => rand(1, 5) . ' năm',
                'status' => 'approved',
                'approved_at' => Carbon::now(),
            ]);

            $addr = $driverAddresses_7858023071[$i - 1];
            UserInfo::updateOrCreate(['user_id' => $driverUser->id], [
                'national_id' => '7858023071' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'tax_code' => 'TX7858023071' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_of_birth' => '1995-01-0' . (($i % 9) + 1),
                'full_address' => $addr['full_address'],
                'address_detail' => 'Khu vực giao hàng chính',
                'latitude' => $addr['latitude'],
                'longitude' => $addr['longitude'],
                'province_code' => null,
                'district_code' => null,
                'ward_code' => null,
            ]);
        }

        // ════════════════════════════════════════════
        // HUB: Điểm BĐVHX Phú Cường
        // ID: 9416913319 | Phú Xuyên | (20.701411, 106.02884)
        // ════════════════════════════════════════════

        $hubUser = User::firstOrCreate(
            ['email' => 'hub_9416913319@example.com'],
            [
                'phone' => '024' . str_pad('9416913319', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Điểm BĐVHX Phú Cường',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '9416913319',
                'hub_latitude' => '20.701411',
                'hub_longitude' => '106.02884',
                'hub_address' => 'Phú Xuyên, Hà Nội',
            ]
        );

        $driverAddresses_9416913319 = [
            ['full_address' => 'Cơm', 'latitude' => 20.682941, 'longitude' => 106.060543],
            ['full_address' => 'Gần Điểm BĐVHX Phú Cường', 'latitude' => 20.703839, 'longitude' => 106.031268],
            ['full_address' => 'Gần Điểm BĐVHX Phú Cường', 'latitude' => 20.697748, 'longitude' => 106.025177],
        ];

        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_9416913319_{$i}@example.com";
            $phone = "092" . substr(str_pad('9416913319', 9, '0', STR_PAD_LEFT), -6) . $i;
            $fullName = "Driver Điểm BĐVHX Phú Cường #{$i}";

            $driverUser = User::firstOrCreate(['email' => $email], [
                'phone' => $phone,
                'full_name' => $fullName,
                'role' => 'driver',
                'status' => 'active',
                'password_hash' => Hash::make('123456'),
            ]);

            DriverProfile::updateOrCreate(['user_id' => $driverUser->id], [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'province_code' => 1,
                'post_office_id' => '9416913319',
                'post_office_name' => 'Điểm BĐVHX Phú Cường',
                'post_office_address' => 'Phú Xuyên, Hà Nội',
                'post_office_lat' => '20.701411',
                'post_office_lng' => '106.02884',
                'post_office_phone' => '0241234567',
                'vehicle_type' => 'Xe máy',
                'license_number' => str_repeat($i, 8),
                'license_image' => 'license_image.png',
                'identity_image' => 'identity_image.png',
                'experience' => rand(1, 5) . ' năm',
                'status' => 'approved',
                'approved_at' => Carbon::now(),
            ]);

            $addr = $driverAddresses_9416913319[$i - 1];
            UserInfo::updateOrCreate(['user_id' => $driverUser->id], [
                'national_id' => '9416913319' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'tax_code' => 'TX9416913319' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_of_birth' => '1995-01-0' . (($i % 9) + 1),
                'full_address' => $addr['full_address'],
                'address_detail' => 'Khu vực giao hàng chính',
                'latitude' => $addr['latitude'],
                'longitude' => $addr['longitude'],
                'province_code' => null,
                'district_code' => null,
                'ward_code' => null,
            ]);
        }

        // ════════════════════════════════════════════
        // HUB: Bưu Điện Tản Lĩnh
        // ID: 2888528101 | Ba Vì | (21.125102, 105.391669)
        // ════════════════════════════════════════════

        $hubUser = User::firstOrCreate(
            ['email' => 'hub_2888528101@example.com'],
            [
                'phone' => '024' . str_pad('2888528101', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu Điện Tản Lĩnh',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '2888528101',
                'hub_latitude' => '21.125102',
                'hub_longitude' => '105.391669',
                'hub_address' => 'Ba Vì, Hà Nội',
            ]
        );

        $driverAddresses_2888528101 = [
            ['full_address' => 'Bến Thủy', 'latitude' => 21.164558, 'longitude' => 105.376123],
            ['full_address' => 'Agribank', 'latitude' => 21.124962, 'longitude' => 105.391101],
            ['full_address' => 'Gần Bưu Điện Tản Lĩnh', 'latitude' => 21.120925, 'longitude' => 105.387492],
        ];

        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_2888528101_{$i}@example.com";
            $phone = "092" . substr(str_pad('2888528101', 9, '0', STR_PAD_LEFT), -6) . $i;
            $fullName = "Driver Bưu Điện Tản Lĩnh #{$i}";

            $driverUser = User::firstOrCreate(['email' => $email], [
                'phone' => $phone,
                'full_name' => $fullName,
                'role' => 'driver',
                'status' => 'active',
                'password_hash' => Hash::make('123456'),
            ]);

            DriverProfile::updateOrCreate(['user_id' => $driverUser->id], [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'province_code' => 1,
                'post_office_id' => '2888528101',
                'post_office_name' => 'Bưu Điện Tản Lĩnh',
                'post_office_address' => 'Ba Vì, Hà Nội',
                'post_office_lat' => '21.125102',
                'post_office_lng' => '105.391669',
                'post_office_phone' => '0241234567',
                'vehicle_type' => 'Xe máy',
                'license_number' => str_repeat($i, 8),
                'license_image' => 'license_image.png',
                'identity_image' => 'identity_image.png',
                'experience' => rand(1, 5) . ' năm',
                'status' => 'approved',
                'approved_at' => Carbon::now(),
            ]);

            $addr = $driverAddresses_2888528101[$i - 1];
            UserInfo::updateOrCreate(['user_id' => $driverUser->id], [
                'national_id' => '2888528101' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'tax_code' => 'TX2888528101' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_of_birth' => '1995-01-0' . (($i % 9) + 1),
                'full_address' => $addr['full_address'],
                'address_detail' => 'Khu vực giao hàng chính',
                'latitude' => $addr['latitude'],
                'longitude' => $addr['longitude'],
                'province_code' => null,
                'district_code' => null,
                'ward_code' => null,
            ]);
        }

        // ════════════════════════════════════════════
        // HUB: Bưu Điện Viettel Tây Đằng
        // ID: 4532521393 | Ba Vì | (21.187022, 105.438665)
        // ════════════════════════════════════════════

        $hubUser = User::firstOrCreate(
            ['email' => 'hub_4532521393@example.com'],
            [
                'phone' => '024' . str_pad('4532521393', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu Điện Viettel Tây Đằng',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '4532521393',
                'hub_latitude' => '21.187022',
                'hub_longitude' => '105.438665',
                'hub_address' => 'Ba Vì, Hà Nội',
            ]
        );

        $driverAddresses_4532521393 = [
            ['full_address' => 'Hương Quỳnh - Trâu Ngon 36 Món', 'latitude' => 21.21493, 'longitude' => 105.403835],
            ['full_address' => 'restaurant', 'latitude' => 21.20779, 'longitude' => 105.41518],
            ['full_address' => 'Gần Bưu Điện Viettel Tây Đằng', 'latitude' => 21.188155, 'longitude' => 105.439798],
        ];

        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_4532521393_{$i}@example.com";
            $phone = "092" . substr(str_pad('4532521393', 9, '0', STR_PAD_LEFT), -6) . $i;
            $fullName = "Driver Bưu Điện Viettel Tây Đằng #{$i}";

            $driverUser = User::firstOrCreate(['email' => $email], [
                'phone' => $phone,
                'full_name' => $fullName,
                'role' => 'driver',
                'status' => 'active',
                'password_hash' => Hash::make('123456'),
            ]);

            DriverProfile::updateOrCreate(['user_id' => $driverUser->id], [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'province_code' => 1,
                'post_office_id' => '4532521393',
                'post_office_name' => 'Bưu Điện Viettel Tây Đằng',
                'post_office_address' => 'Ba Vì, Hà Nội',
                'post_office_lat' => '21.187022',
                'post_office_lng' => '105.438665',
                'post_office_phone' => '0241234567',
                'vehicle_type' => 'Xe máy',
                'license_number' => str_repeat($i, 8),
                'license_image' => 'license_image.png',
                'identity_image' => 'identity_image.png',
                'experience' => rand(1, 5) . ' năm',
                'status' => 'approved',
                'approved_at' => Carbon::now(),
            ]);

            $addr = $driverAddresses_4532521393[$i - 1];
            UserInfo::updateOrCreate(['user_id' => $driverUser->id], [
                'national_id' => '4532521393' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'tax_code' => 'TX4532521393' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_of_birth' => '1995-01-0' . (($i % 9) + 1),
                'full_address' => $addr['full_address'],
                'address_detail' => 'Khu vực giao hàng chính',
                'latitude' => $addr['latitude'],
                'longitude' => $addr['longitude'],
                'province_code' => null,
                'district_code' => null,
                'ward_code' => null,
            ]);
        }

        // ════════════════════════════════════════════
        // HUB: Bưu điện huyện Ba Vì
        // ID: 7856622552 | Ba Vì | (21.199278, 105.423484)
        // ════════════════════════════════════════════

        $hubUser = User::firstOrCreate(
            ['email' => 'hub_7856622552@example.com'],
            [
                'phone' => '024' . str_pad('7856622552', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu điện huyện Ba Vì',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '7856622552',
                'hub_latitude' => '21.199278',
                'hub_longitude' => '105.423484',
                'hub_address' => 'Ba Vì, Hà Nội',
            ]
        );

        $driverAddresses_7856622552 = [
            ['full_address' => 'Gần Bưu điện huyện Ba Vì', 'latitude' => 21.198877, 'longitude' => 105.423083],
            ['full_address' => 'Gần Bưu điện huyện Ba Vì', 'latitude' => 21.202968, 'longitude' => 105.427174],
            ['full_address' => 'Gần Bưu điện huyện Ba Vì', 'latitude' => 21.197697, 'longitude' => 105.421903],
        ];

        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_7856622552_{$i}@example.com";
            $phone = "092" . substr(str_pad('7856622552', 9, '0', STR_PAD_LEFT), -6) . $i;
            $fullName = "Driver Bưu điện huyện Ba Vì #{$i}";

            $driverUser = User::firstOrCreate(['email' => $email], [
                'phone' => $phone,
                'full_name' => $fullName,
                'role' => 'driver',
                'status' => 'active',
                'password_hash' => Hash::make('123456'),
            ]);

            DriverProfile::updateOrCreate(['user_id' => $driverUser->id], [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'province_code' => 1,
                'post_office_id' => '7856622552',
                'post_office_name' => 'Bưu điện huyện Ba Vì',
                'post_office_address' => 'Ba Vì, Hà Nội',
                'post_office_lat' => '21.199278',
                'post_office_lng' => '105.423484',
                'post_office_phone' => '0241234567',
                'vehicle_type' => 'Xe máy',
                'license_number' => str_repeat($i, 8),
                'license_image' => 'license_image.png',
                'identity_image' => 'identity_image.png',
                'experience' => rand(1, 5) . ' năm',
                'status' => 'approved',
                'approved_at' => Carbon::now(),
            ]);

            $addr = $driverAddresses_7856622552[$i - 1];
            UserInfo::updateOrCreate(['user_id' => $driverUser->id], [
                'national_id' => '7856622552' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'tax_code' => 'TX7856622552' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_of_birth' => '1995-01-0' . (($i % 9) + 1),
                'full_address' => $addr['full_address'],
                'address_detail' => 'Khu vực giao hàng chính',
                'latitude' => $addr['latitude'],
                'longitude' => $addr['longitude'],
                'province_code' => null,
                'district_code' => null,
                'ward_code' => null,
            ]);
        }

        // ════════════════════════════════════════════
        // HUB: Bưu điện Văn hoá xã Ba Vì
        // ID: 951945610 | Ba Vì | (21.081647, 105.329408)
        // ════════════════════════════════════════════

        $hubUser = User::firstOrCreate(
            ['email' => 'hub_951945610@example.com'],
            [
                'phone' => '024' . str_pad('951945610', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu điện Văn hoá xã Ba Vì',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '951945610',
                'hub_latitude' => '21.081647',
                'hub_longitude' => '105.329408',
                'hub_address' => 'Ba Vì, Hà Nội',
            ]
        );

        $driverAddresses_951945610 = [
            ['full_address' => 'restaurant', 'latitude' => 21.083893, 'longitude' => 105.371849],
            ['full_address' => 'cafe', 'latitude' => 21.060882, 'longitude' => 105.363492],
            ['full_address' => 'Nhà hàng Bản Coốc Garden', 'latitude' => 21.066218, 'longitude' => 105.325837],
        ];

        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_951945610_{$i}@example.com";
            $phone = "092" . substr(str_pad('951945610', 9, '0', STR_PAD_LEFT), -6) . $i;
            $fullName = "Driver Bưu điện Văn hoá xã Ba Vì #{$i}";

            $driverUser = User::firstOrCreate(['email' => $email], [
                'phone' => $phone,
                'full_name' => $fullName,
                'role' => 'driver',
                'status' => 'active',
                'password_hash' => Hash::make('123456'),
            ]);

            DriverProfile::updateOrCreate(['user_id' => $driverUser->id], [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'province_code' => 1,
                'post_office_id' => '951945610',
                'post_office_name' => 'Bưu điện Văn hoá xã Ba Vì',
                'post_office_address' => 'Ba Vì, Hà Nội',
                'post_office_lat' => '21.081647',
                'post_office_lng' => '105.329408',
                'post_office_phone' => '0241234567',
                'vehicle_type' => 'Xe máy',
                'license_number' => str_repeat($i, 8),
                'license_image' => 'license_image.png',
                'identity_image' => 'identity_image.png',
                'experience' => rand(1, 5) . ' năm',
                'status' => 'approved',
                'approved_at' => Carbon::now(),
            ]);

            $addr = $driverAddresses_951945610[$i - 1];
            UserInfo::updateOrCreate(['user_id' => $driverUser->id], [
                'national_id' => '951945610' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'tax_code' => 'TX951945610' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_of_birth' => '1995-01-0' . (($i % 9) + 1),
                'full_address' => $addr['full_address'],
                'address_detail' => 'Khu vực giao hàng chính',
                'latitude' => $addr['latitude'],
                'longitude' => $addr['longitude'],
                'province_code' => null,
                'district_code' => null,
                'ward_code' => null,
            ]);
        }

        // ════════════════════════════════════════════
        // HUB: Bưu Điện Thị Xã Sơn Tây
        // ID: 1711396823 | Phúc Thọ | (21.138348, 105.506722)
        // ════════════════════════════════════════════

        $hubUser = User::firstOrCreate(
            ['email' => 'hub_1711396823@example.com'],
            [
                'phone' => '024' . str_pad('1711396823', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu Điện Thị Xã Sơn Tây',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '1711396823',
                'hub_latitude' => '21.138348',
                'hub_longitude' => '105.506722',
                'hub_address' => 'Phúc Thọ, Hà Nội',
            ]
        );

        $driverAddresses_1711396823 = [
            ['full_address' => 'Đường Phú Hà', 'latitude' => 21.143123, 'longitude' => 105.503261],
            ['full_address' => 'Nhà Hàng Lâm Ký', 'latitude' => 21.134227, 'longitude' => 105.503133],
            ['full_address' => 'Phố Đinh Tiên Hoàng', 'latitude' => 21.142984, 'longitude' => 105.503281],
        ];

        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_1711396823_{$i}@example.com";
            $phone = "092" . substr(str_pad('1711396823', 9, '0', STR_PAD_LEFT), -6) . $i;
            $fullName = "Driver Bưu Điện Thị Xã Sơn Tây #{$i}";

            $driverUser = User::firstOrCreate(['email' => $email], [
                'phone' => $phone,
                'full_name' => $fullName,
                'role' => 'driver',
                'status' => 'active',
                'password_hash' => Hash::make('123456'),
            ]);

            DriverProfile::updateOrCreate(['user_id' => $driverUser->id], [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'province_code' => 1,
                'post_office_id' => '1711396823',
                'post_office_name' => 'Bưu Điện Thị Xã Sơn Tây',
                'post_office_address' => 'Phúc Thọ, Hà Nội',
                'post_office_lat' => '21.138348',
                'post_office_lng' => '105.506722',
                'post_office_phone' => '0241234567',
                'vehicle_type' => 'Xe máy',
                'license_number' => str_repeat($i, 8),
                'license_image' => 'license_image.png',
                'identity_image' => 'identity_image.png',
                'experience' => rand(1, 5) . ' năm',
                'status' => 'approved',
                'approved_at' => Carbon::now(),
            ]);

            $addr = $driverAddresses_1711396823[$i - 1];
            UserInfo::updateOrCreate(['user_id' => $driverUser->id], [
                'national_id' => '1711396823' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'tax_code' => 'TX1711396823' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_of_birth' => '1995-01-0' . (($i % 9) + 1),
                'full_address' => $addr['full_address'],
                'address_detail' => 'Khu vực giao hàng chính',
                'latitude' => $addr['latitude'],
                'longitude' => $addr['longitude'],
                'province_code' => null,
                'district_code' => null,
                'ward_code' => null,
            ]);
        }

        // ════════════════════════════════════════════
        // HUB: Bưu Điện Tản Lĩnh
        // ID: 522357112 | Phúc Thọ | (21.125102, 105.391669)
        // ════════════════════════════════════════════

        $hubUser = User::firstOrCreate(
            ['email' => 'hub_522357112@example.com'],
            [
                'phone' => '024' . str_pad('522357112', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu Điện Tản Lĩnh',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '522357112',
                'hub_latitude' => '21.125102',
                'hub_longitude' => '105.391669',
                'hub_address' => 'Phúc Thọ, Hà Nội',
            ]
        );

        $driverAddresses_522357112 = [
            ['full_address' => 'Bến Thủy', 'latitude' => 21.164558, 'longitude' => 105.376123],
            ['full_address' => 'restaurant', 'latitude' => 21.097677, 'longitude' => 105.402168],
            ['full_address' => 'Agribank', 'latitude' => 21.124962, 'longitude' => 105.391101],
        ];

        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_522357112_{$i}@example.com";
            $phone = "092" . substr(str_pad('522357112', 9, '0', STR_PAD_LEFT), -6) . $i;
            $fullName = "Driver Bưu Điện Tản Lĩnh #{$i}";

            $driverUser = User::firstOrCreate(['email' => $email], [
                'phone' => $phone,
                'full_name' => $fullName,
                'role' => 'driver',
                'status' => 'active',
                'password_hash' => Hash::make('123456'),
            ]);

            DriverProfile::updateOrCreate(['user_id' => $driverUser->id], [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'province_code' => 1,
                'post_office_id' => '522357112',
                'post_office_name' => 'Bưu Điện Tản Lĩnh',
                'post_office_address' => 'Phúc Thọ, Hà Nội',
                'post_office_lat' => '21.125102',
                'post_office_lng' => '105.391669',
                'post_office_phone' => '0241234567',
                'vehicle_type' => 'Xe máy',
                'license_number' => str_repeat($i, 8),
                'license_image' => 'license_image.png',
                'identity_image' => 'identity_image.png',
                'experience' => rand(1, 5) . ' năm',
                'status' => 'approved',
                'approved_at' => Carbon::now(),
            ]);

            $addr = $driverAddresses_522357112[$i - 1];
            UserInfo::updateOrCreate(['user_id' => $driverUser->id], [
                'national_id' => '522357112' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'tax_code' => 'TX522357112' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_of_birth' => '1995-01-0' . (($i % 9) + 1),
                'full_address' => $addr['full_address'],
                'address_detail' => 'Khu vực giao hàng chính',
                'latitude' => $addr['latitude'],
                'longitude' => $addr['longitude'],
                'province_code' => null,
                'district_code' => null,
                'ward_code' => null,
            ]);
        }

        // ════════════════════════════════════════════
        // HUB: Bưu Điện Viettel Tây Đằng
        // ID: 753011614 | Phúc Thọ | (21.187022, 105.438665)
        // ════════════════════════════════════════════

        $hubUser = User::firstOrCreate(
            ['email' => 'hub_753011614@example.com'],
            [
                'phone' => '024' . str_pad('753011614', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu Điện Viettel Tây Đằng',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '753011614',
                'hub_latitude' => '21.187022',
                'hub_longitude' => '105.438665',
                'hub_address' => 'Phúc Thọ, Hà Nội',
            ]
        );

        $driverAddresses_753011614 = [
            ['full_address' => 'Hương Quỳnh - Trâu Ngon 36 Món', 'latitude' => 21.21493, 'longitude' => 105.403835],
            ['full_address' => 'restaurant', 'latitude' => 21.20779, 'longitude' => 105.41518],
            ['full_address' => 'Gần Bưu Điện Viettel Tây Đằng', 'latitude' => 21.19163, 'longitude' => 105.443273],
        ];

        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_753011614_{$i}@example.com";
            $phone = "092" . substr(str_pad('753011614', 9, '0', STR_PAD_LEFT), -6) . $i;
            $fullName = "Driver Bưu Điện Viettel Tây Đằng #{$i}";

            $driverUser = User::firstOrCreate(['email' => $email], [
                'phone' => $phone,
                'full_name' => $fullName,
                'role' => 'driver',
                'status' => 'active',
                'password_hash' => Hash::make('123456'),
            ]);

            DriverProfile::updateOrCreate(['user_id' => $driverUser->id], [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'province_code' => 1,
                'post_office_id' => '753011614',
                'post_office_name' => 'Bưu Điện Viettel Tây Đằng',
                'post_office_address' => 'Phúc Thọ, Hà Nội',
                'post_office_lat' => '21.187022',
                'post_office_lng' => '105.438665',
                'post_office_phone' => '0241234567',
                'vehicle_type' => 'Xe máy',
                'license_number' => str_repeat($i, 8),
                'license_image' => 'license_image.png',
                'identity_image' => 'identity_image.png',
                'experience' => rand(1, 5) . ' năm',
                'status' => 'approved',
                'approved_at' => Carbon::now(),
            ]);

            $addr = $driverAddresses_753011614[$i - 1];
            UserInfo::updateOrCreate(['user_id' => $driverUser->id], [
                'national_id' => '753011614' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'tax_code' => 'TX753011614' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_of_birth' => '1995-01-0' . (($i % 9) + 1),
                'full_address' => $addr['full_address'],
                'address_detail' => 'Khu vực giao hàng chính',
                'latitude' => $addr['latitude'],
                'longitude' => $addr['longitude'],
                'province_code' => null,
                'district_code' => null,
                'ward_code' => null,
            ]);
        }

        // ════════════════════════════════════════════
        // HUB: Bưu điện huyện Ba Vì
        // ID: 963721189 | Phúc Thọ | (21.199278, 105.423484)
        // ════════════════════════════════════════════

        $hubUser = User::firstOrCreate(
            ['email' => 'hub_963721189@example.com'],
            [
                'phone' => '024' . str_pad('963721189', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu điện huyện Ba Vì',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '963721189',
                'hub_latitude' => '21.199278',
                'hub_longitude' => '105.423484',
                'hub_address' => 'Phúc Thọ, Hà Nội',
            ]
        );

        $driverAddresses_963721189 = [
            ['full_address' => 'Gần Bưu điện huyện Ba Vì', 'latitude' => 21.198304, 'longitude' => 105.42251],
            ['full_address' => 'Gần Bưu điện huyện Ba Vì', 'latitude' => 21.200546, 'longitude' => 105.424752],
            ['full_address' => 'Gần Bưu điện huyện Ba Vì', 'latitude' => 21.203722, 'longitude' => 105.427928],
        ];

        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_963721189_{$i}@example.com";
            $phone = "092" . substr(str_pad('963721189', 9, '0', STR_PAD_LEFT), -6) . $i;
            $fullName = "Driver Bưu điện huyện Ba Vì #{$i}";

            $driverUser = User::firstOrCreate(['email' => $email], [
                'phone' => $phone,
                'full_name' => $fullName,
                'role' => 'driver',
                'status' => 'active',
                'password_hash' => Hash::make('123456'),
            ]);

            DriverProfile::updateOrCreate(['user_id' => $driverUser->id], [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'province_code' => 1,
                'post_office_id' => '963721189',
                'post_office_name' => 'Bưu điện huyện Ba Vì',
                'post_office_address' => 'Phúc Thọ, Hà Nội',
                'post_office_lat' => '21.199278',
                'post_office_lng' => '105.423484',
                'post_office_phone' => '0241234567',
                'vehicle_type' => 'Xe máy',
                'license_number' => str_repeat($i, 8),
                'license_image' => 'license_image.png',
                'identity_image' => 'identity_image.png',
                'experience' => rand(1, 5) . ' năm',
                'status' => 'approved',
                'approved_at' => Carbon::now(),
            ]);

            $addr = $driverAddresses_963721189[$i - 1];
            UserInfo::updateOrCreate(['user_id' => $driverUser->id], [
                'national_id' => '963721189' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'tax_code' => 'TX963721189' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_of_birth' => '1995-01-0' . (($i % 9) + 1),
                'full_address' => $addr['full_address'],
                'address_detail' => 'Khu vực giao hàng chính',
                'latitude' => $addr['latitude'],
                'longitude' => $addr['longitude'],
                'province_code' => null,
                'district_code' => null,
                'ward_code' => null,
            ]);
        }

        // ════════════════════════════════════════════
        // HUB: Bưu Điện Thị Xã Sơn Tây
        // ID: 731393695 | Thạch Thất | (21.138348, 105.506722)
        // ════════════════════════════════════════════

        $hubUser = User::firstOrCreate(
            ['email' => 'hub_731393695@example.com'],
            [
                'phone' => '024' . str_pad('731393695', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu Điện Thị Xã Sơn Tây',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '731393695',
                'hub_latitude' => '21.138348',
                'hub_longitude' => '105.506722',
                'hub_address' => 'Thạch Thất, Hà Nội',
            ]
        );

        $driverAddresses_731393695 = [
            ['full_address' => 'Gần Bưu Điện Thị Xã Sơn Tây', 'latitude' => 21.136131, 'longitude' => 105.504505],
            ['full_address' => 'Gần Bưu Điện Thị Xã Sơn Tây', 'latitude' => 21.140782, 'longitude' => 105.509156],
            ['full_address' => 'Gần Bưu Điện Thị Xã Sơn Tây', 'latitude' => 21.137568, 'longitude' => 105.505942],
        ];

        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_731393695_{$i}@example.com";
            $phone = "092" . substr(str_pad('731393695', 9, '0', STR_PAD_LEFT), -6) . $i;
            $fullName = "Driver Bưu Điện Thị Xã Sơn Tây #{$i}";

            $driverUser = User::firstOrCreate(['email' => $email], [
                'phone' => $phone,
                'full_name' => $fullName,
                'role' => 'driver',
                'status' => 'active',
                'password_hash' => Hash::make('123456'),
            ]);

            DriverProfile::updateOrCreate(['user_id' => $driverUser->id], [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'province_code' => 1,
                'post_office_id' => '731393695',
                'post_office_name' => 'Bưu Điện Thị Xã Sơn Tây',
                'post_office_address' => 'Thạch Thất, Hà Nội',
                'post_office_lat' => '21.138348',
                'post_office_lng' => '105.506722',
                'post_office_phone' => '0241234567',
                'vehicle_type' => 'Xe máy',
                'license_number' => str_repeat($i, 8),
                'license_image' => 'license_image.png',
                'identity_image' => 'identity_image.png',
                'experience' => rand(1, 5) . ' năm',
                'status' => 'approved',
                'approved_at' => Carbon::now(),
            ]);

            $addr = $driverAddresses_731393695[$i - 1];
            UserInfo::updateOrCreate(['user_id' => $driverUser->id], [
                'national_id' => '731393695' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'tax_code' => 'TX731393695' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_of_birth' => '1995-01-0' . (($i % 9) + 1),
                'full_address' => $addr['full_address'],
                'address_detail' => 'Khu vực giao hàng chính',
                'latitude' => $addr['latitude'],
                'longitude' => $addr['longitude'],
                'province_code' => null,
                'district_code' => null,
                'ward_code' => null,
            ]);
        }

        // ════════════════════════════════════════════
        // HUB: Bưu cục Thạch Thất
        // ID: 7884264502 | Thạch Thất | (21.021268, 105.550769)
        // ════════════════════════════════════════════

        $hubUser = User::firstOrCreate(
            ['email' => 'hub_7884264502@example.com'],
            [
                'phone' => '024' . str_pad('7884264502', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu cục Thạch Thất',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '7884264502',
                'hub_latitude' => '21.021268',
                'hub_longitude' => '105.550769',
                'hub_address' => 'Thạch Thất, Hà Nội',
            ]
        );

        $driverAddresses_7884264502 = [
            ['full_address' => 'Trường Trung học cơ sở Cần Kiệm', 'latitude' => 21.016499, 'longitude' => 105.581749],
            ['full_address' => 'Trường Trung học cơ sở Thạch Hòa', 'latitude' => 20.983831, 'longitude' => 105.528636],
            ['full_address' => 'Trường Trung học cơ sở Kim Quan', 'latitude' => 21.048211, 'longitude' => 105.57648],
        ];

        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_7884264502_{$i}@example.com";
            $phone = "092" . substr(str_pad('7884264502', 9, '0', STR_PAD_LEFT), -6) . $i;
            $fullName = "Driver Bưu cục Thạch Thất #{$i}";

            $driverUser = User::firstOrCreate(['email' => $email], [
                'phone' => $phone,
                'full_name' => $fullName,
                'role' => 'driver',
                'status' => 'active',
                'password_hash' => Hash::make('123456'),
            ]);

            DriverProfile::updateOrCreate(['user_id' => $driverUser->id], [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'province_code' => 1,
                'post_office_id' => '7884264502',
                'post_office_name' => 'Bưu cục Thạch Thất',
                'post_office_address' => 'Thạch Thất, Hà Nội',
                'post_office_lat' => '21.021268',
                'post_office_lng' => '105.550769',
                'post_office_phone' => '0241234567',
                'vehicle_type' => 'Xe máy',
                'license_number' => str_repeat($i, 8),
                'license_image' => 'license_image.png',
                'identity_image' => 'identity_image.png',
                'experience' => rand(1, 5) . ' năm',
                'status' => 'approved',
                'approved_at' => Carbon::now(),
            ]);

            $addr = $driverAddresses_7884264502[$i - 1];
            UserInfo::updateOrCreate(['user_id' => $driverUser->id], [
                'national_id' => '7884264502' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'tax_code' => 'TX7884264502' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_of_birth' => '1995-01-0' . (($i % 9) + 1),
                'full_address' => $addr['full_address'],
                'address_detail' => 'Khu vực giao hàng chính',
                'latitude' => $addr['latitude'],
                'longitude' => $addr['longitude'],
                'province_code' => null,
                'district_code' => null,
                'ward_code' => null,
            ]);
        }

        // ════════════════════════════════════════════
        // HUB: Bưu Điện Xuân Phương
        // ID: 5296689023 | Quốc Oai | (21.03465, 105.747028)
        // ════════════════════════════════════════════

        $hubUser = User::firstOrCreate(
            ['email' => 'hub_5296689023@example.com'],
            [
                'phone' => '024' . str_pad('5296689023', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu Điện Xuân Phương',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '5296689023',
                'hub_latitude' => '21.03465',
                'hub_longitude' => '105.747028',
                'hub_address' => 'Quốc Oai, Hà Nội',
            ]
        );

        $driverAddresses_5296689023 = [
            ['full_address' => 'Ngõ 74 Hữu Hưng', 'latitude' => 20.997047, 'longitude' => 105.750426],
            ['full_address' => 'Phạm Văn Đồng', 'latitude' => 21.064601, 'longitude' => 105.78261],
            ['full_address' => 'cafe', 'latitude' => 21.03825, 'longitude' => 105.782228],
        ];

        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_5296689023_{$i}@example.com";
            $phone = "092" . substr(str_pad('5296689023', 9, '0', STR_PAD_LEFT), -6) . $i;
            $fullName = "Driver Bưu Điện Xuân Phương #{$i}";

            $driverUser = User::firstOrCreate(['email' => $email], [
                'phone' => $phone,
                'full_name' => $fullName,
                'role' => 'driver',
                'status' => 'active',
                'password_hash' => Hash::make('123456'),
            ]);

            DriverProfile::updateOrCreate(['user_id' => $driverUser->id], [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'province_code' => 1,
                'post_office_id' => '5296689023',
                'post_office_name' => 'Bưu Điện Xuân Phương',
                'post_office_address' => 'Quốc Oai, Hà Nội',
                'post_office_lat' => '21.03465',
                'post_office_lng' => '105.747028',
                'post_office_phone' => '0241234567',
                'vehicle_type' => 'Xe máy',
                'license_number' => str_repeat($i, 8),
                'license_image' => 'license_image.png',
                'identity_image' => 'identity_image.png',
                'experience' => rand(1, 5) . ' năm',
                'status' => 'approved',
                'approved_at' => Carbon::now(),
            ]);

            $addr = $driverAddresses_5296689023[$i - 1];
            UserInfo::updateOrCreate(['user_id' => $driverUser->id], [
                'national_id' => '5296689023' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'tax_code' => 'TX5296689023' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_of_birth' => '1995-01-0' . (($i % 9) + 1),
                'full_address' => $addr['full_address'],
                'address_detail' => 'Khu vực giao hàng chính',
                'latitude' => $addr['latitude'],
                'longitude' => $addr['longitude'],
                'province_code' => null,
                'district_code' => null,
                'ward_code' => null,
            ]);
        }

        // ════════════════════════════════════════════
        // HUB: Bưu Điện Dương Nội
        // ID: 6516239487 | Quốc Oai | (20.979961, 105.748608)
        // ════════════════════════════════════════════

        $hubUser = User::firstOrCreate(
            ['email' => 'hub_6516239487@example.com'],
            [
                'phone' => '024' . str_pad('6516239487', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu Điện Dương Nội',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '6516239487',
                'hub_latitude' => '20.979961',
                'hub_longitude' => '105.748608',
                'hub_address' => 'Quốc Oai, Hà Nội',
            ]
        );

        $driverAddresses_6516239487 = [
            ['full_address' => 'Chiến Thắng', 'latitude' => 20.979978, 'longitude' => 105.795497],
            ['full_address' => 'Số 56BT8 KĐT Văn Quán', 'latitude' => 20.9775, 'longitude' => 105.792222],
            ['full_address' => 'Cà Phê Fimo Sp', 'latitude' => 21.010111, 'longitude' => 105.783736],
        ];

        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_6516239487_{$i}@example.com";
            $phone = "092" . substr(str_pad('6516239487', 9, '0', STR_PAD_LEFT), -6) . $i;
            $fullName = "Driver Bưu Điện Dương Nội #{$i}";

            $driverUser = User::firstOrCreate(['email' => $email], [
                'phone' => $phone,
                'full_name' => $fullName,
                'role' => 'driver',
                'status' => 'active',
                'password_hash' => Hash::make('123456'),
            ]);

            DriverProfile::updateOrCreate(['user_id' => $driverUser->id], [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'province_code' => 1,
                'post_office_id' => '6516239487',
                'post_office_name' => 'Bưu Điện Dương Nội',
                'post_office_address' => 'Quốc Oai, Hà Nội',
                'post_office_lat' => '20.979961',
                'post_office_lng' => '105.748608',
                'post_office_phone' => '0241234567',
                'vehicle_type' => 'Xe máy',
                'license_number' => str_repeat($i, 8),
                'license_image' => 'license_image.png',
                'identity_image' => 'identity_image.png',
                'experience' => rand(1, 5) . ' năm',
                'status' => 'approved',
                'approved_at' => Carbon::now(),
            ]);

            $addr = $driverAddresses_6516239487[$i - 1];
            UserInfo::updateOrCreate(['user_id' => $driverUser->id], [
                'national_id' => '6516239487' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'tax_code' => 'TX6516239487' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_of_birth' => '1995-01-0' . (($i % 9) + 1),
                'full_address' => $addr['full_address'],
                'address_detail' => 'Khu vực giao hàng chính',
                'latitude' => $addr['latitude'],
                'longitude' => $addr['longitude'],
                'province_code' => null,
                'district_code' => null,
                'ward_code' => null,
            ]);
        }

        // ════════════════════════════════════════════
        // HUB: Bưu cục Quốc Oai
        // ID: 224878529 | Quốc Oai | (21.021268, 105.550769)
        // ════════════════════════════════════════════

        $hubUser = User::firstOrCreate(
            ['email' => 'hub_224878529@example.com'],
            [
                'phone' => '024' . str_pad('224878529', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu cục Quốc Oai',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '224878529',
                'hub_latitude' => '21.021268',
                'hub_longitude' => '105.550769',
                'hub_address' => 'Quốc Oai, Hà Nội',
            ]
        );

        $driverAddresses_224878529 = [
            ['full_address' => 'Zing Tea', 'latitude' => 21.017133, 'longitude' => 105.552357],
            ['full_address' => 'Helios', 'latitude' => 21.019387, 'longitude' => 105.550022],
            ['full_address' => 'Wisteria EME', 'latitude' => 20.988359, 'longitude' => 105.551417],
        ];

        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_224878529_{$i}@example.com";
            $phone = "092" . substr(str_pad('224878529', 9, '0', STR_PAD_LEFT), -6) . $i;
            $fullName = "Driver Bưu cục Quốc Oai #{$i}";

            $driverUser = User::firstOrCreate(['email' => $email], [
                'phone' => $phone,
                'full_name' => $fullName,
                'role' => 'driver',
                'status' => 'active',
                'password_hash' => Hash::make('123456'),
            ]);

            DriverProfile::updateOrCreate(['user_id' => $driverUser->id], [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'province_code' => 1,
                'post_office_id' => '224878529',
                'post_office_name' => 'Bưu cục Quốc Oai',
                'post_office_address' => 'Quốc Oai, Hà Nội',
                'post_office_lat' => '21.021268',
                'post_office_lng' => '105.550769',
                'post_office_phone' => '0241234567',
                'vehicle_type' => 'Xe máy',
                'license_number' => str_repeat($i, 8),
                'license_image' => 'license_image.png',
                'identity_image' => 'identity_image.png',
                'experience' => rand(1, 5) . ' năm',
                'status' => 'approved',
                'approved_at' => Carbon::now(),
            ]);

            $addr = $driverAddresses_224878529[$i - 1];
            UserInfo::updateOrCreate(['user_id' => $driverUser->id], [
                'national_id' => '224878529' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'tax_code' => 'TX224878529' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_of_birth' => '1995-01-0' . (($i % 9) + 1),
                'full_address' => $addr['full_address'],
                'address_detail' => 'Khu vực giao hàng chính',
                'latitude' => $addr['latitude'],
                'longitude' => $addr['longitude'],
                'province_code' => null,
                'district_code' => null,
                'ward_code' => null,
            ]);
        }

        // ════════════════════════════════════════════
        // HUB: Bưu Cục Văn Mỗ
        // ID: 444394873 | Chương Mỹ | (20.983502, 105.792247)
        // ════════════════════════════════════════════

        $hubUser = User::firstOrCreate(
            ['email' => 'hub_444394873@example.com'],
            [
                'phone' => '024' . str_pad('444394873', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu Cục Văn Mỗ',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '444394873',
                'hub_latitude' => '20.983502',
                'hub_longitude' => '105.792247',
                'hub_address' => 'Chương Mỹ, Hà Nội',
            ]
        );

        $driverAddresses_444394873 = [
            ['full_address' => 'Đường Nguyễn Khang', 'latitude' => 21.019104, 'longitude' => 105.801508],
            ['full_address' => 'Talky cafe $', 'latitude' => 21.015343, 'longitude' => 105.813105],
            ['full_address' => 'Cafe Artisee', 'latitude' => 21.014615, 'longitude' => 105.775295],
        ];

        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_444394873_{$i}@example.com";
            $phone = "092" . substr(str_pad('444394873', 9, '0', STR_PAD_LEFT), -6) . $i;
            $fullName = "Driver Bưu Cục Văn Mỗ #{$i}";

            $driverUser = User::firstOrCreate(['email' => $email], [
                'phone' => $phone,
                'full_name' => $fullName,
                'role' => 'driver',
                'status' => 'active',
                'password_hash' => Hash::make('123456'),
            ]);

            DriverProfile::updateOrCreate(['user_id' => $driverUser->id], [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'province_code' => 1,
                'post_office_id' => '444394873',
                'post_office_name' => 'Bưu Cục Văn Mỗ',
                'post_office_address' => 'Chương Mỹ, Hà Nội',
                'post_office_lat' => '20.983502',
                'post_office_lng' => '105.792247',
                'post_office_phone' => '0241234567',
                'vehicle_type' => 'Xe máy',
                'license_number' => str_repeat($i, 8),
                'license_image' => 'license_image.png',
                'identity_image' => 'identity_image.png',
                'experience' => rand(1, 5) . ' năm',
                'status' => 'approved',
                'approved_at' => Carbon::now(),
            ]);

            $addr = $driverAddresses_444394873[$i - 1];
            UserInfo::updateOrCreate(['user_id' => $driverUser->id], [
                'national_id' => '444394873' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'tax_code' => 'TX444394873' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_of_birth' => '1995-01-0' . (($i % 9) + 1),
                'full_address' => $addr['full_address'],
                'address_detail' => 'Khu vực giao hàng chính',
                'latitude' => $addr['latitude'],
                'longitude' => $addr['longitude'],
                'province_code' => null,
                'district_code' => null,
                'ward_code' => null,
            ]);
        }

        // ════════════════════════════════════════════
        // HUB: Bưu Điện Dương Nội
        // ID: 873090621 | Chương Mỹ | (20.979961, 105.748608)
        // ════════════════════════════════════════════

        $hubUser = User::firstOrCreate(
            ['email' => 'hub_873090621@example.com'],
            [
                'phone' => '024' . str_pad('873090621', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu Điện Dương Nội',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '873090621',
                'hub_latitude' => '20.979961',
                'hub_longitude' => '105.748608',
                'hub_address' => 'Chương Mỹ, Hà Nội',
            ]
        );

        $driverAddresses_873090621 = [
            ['full_address' => 'Cà Phê Góc Phố', 'latitude' => 20.969327, 'longitude' => 105.754698],
            ['full_address' => 'VPBank NEO Express', 'latitude' => 20.962848, 'longitude' => 105.747626],
            ['full_address' => 'Cơm Sườn', 'latitude' => 20.976295, 'longitude' => 105.788699],
        ];

        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_873090621_{$i}@example.com";
            $phone = "092" . substr(str_pad('873090621', 9, '0', STR_PAD_LEFT), -6) . $i;
            $fullName = "Driver Bưu Điện Dương Nội #{$i}";

            $driverUser = User::firstOrCreate(['email' => $email], [
                'phone' => $phone,
                'full_name' => $fullName,
                'role' => 'driver',
                'status' => 'active',
                'password_hash' => Hash::make('123456'),
            ]);

            DriverProfile::updateOrCreate(['user_id' => $driverUser->id], [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'province_code' => 1,
                'post_office_id' => '873090621',
                'post_office_name' => 'Bưu Điện Dương Nội',
                'post_office_address' => 'Chương Mỹ, Hà Nội',
                'post_office_lat' => '20.979961',
                'post_office_lng' => '105.748608',
                'post_office_phone' => '0241234567',
                'vehicle_type' => 'Xe máy',
                'license_number' => str_repeat($i, 8),
                'license_image' => 'license_image.png',
                'identity_image' => 'identity_image.png',
                'experience' => rand(1, 5) . ' năm',
                'status' => 'approved',
                'approved_at' => Carbon::now(),
            ]);

            $addr = $driverAddresses_873090621[$i - 1];
            UserInfo::updateOrCreate(['user_id' => $driverUser->id], [
                'national_id' => '873090621' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'tax_code' => 'TX873090621' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_of_birth' => '1995-01-0' . (($i % 9) + 1),
                'full_address' => $addr['full_address'],
                'address_detail' => 'Khu vực giao hàng chính',
                'latitude' => $addr['latitude'],
                'longitude' => $addr['longitude'],
                'province_code' => null,
                'district_code' => null,
                'ward_code' => null,
            ]);
        }

        // ════════════════════════════════════════════
        // HUB: Bưu Điện Phụng Châu
        // ID: 336991438 | Chương Mỹ | (20.948898, 105.707044)
        // ════════════════════════════════════════════

        $hubUser = User::firstOrCreate(
            ['email' => 'hub_336991438@example.com'],
            [
                'phone' => '024' . str_pad('336991438', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu Điện Phụng Châu',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '336991438',
                'hub_latitude' => '20.948898',
                'hub_longitude' => '105.707044',
                'hub_address' => 'Phượng Nghĩa',
            ]
        );

        $driverAddresses_336991438 = [
            ['full_address' => 'Trường Tiểu học Chúc Sơn A', 'latitude' => 20.918029, 'longitude' => 105.701139],
            ['full_address' => 'Trường Tiểu học Tiên Phương', 'latitude' => 20.939957, 'longitude' => 105.686008],
            ['full_address' => 'Đường An Thượng', 'latitude' => 20.984604, 'longitude' => 105.70809],
        ];

        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_336991438_{$i}@example.com";
            $phone = "092" . substr(str_pad('336991438', 9, '0', STR_PAD_LEFT), -6) . $i;
            $fullName = "Driver Bưu Điện Phụng Châu #{$i}";

            $driverUser = User::firstOrCreate(['email' => $email], [
                'phone' => $phone,
                'full_name' => $fullName,
                'role' => 'driver',
                'status' => 'active',
                'password_hash' => Hash::make('123456'),
            ]);

            DriverProfile::updateOrCreate(['user_id' => $driverUser->id], [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'province_code' => 1,
                'post_office_id' => '336991438',
                'post_office_name' => 'Bưu Điện Phụng Châu',
                'post_office_address' => 'Phượng Nghĩa',
                'post_office_lat' => '20.948898',
                'post_office_lng' => '105.707044',
                'post_office_phone' => '0241234567',
                'vehicle_type' => 'Xe máy',
                'license_number' => str_repeat($i, 8),
                'license_image' => 'license_image.png',
                'identity_image' => 'identity_image.png',
                'experience' => rand(1, 5) . ' năm',
                'status' => 'approved',
                'approved_at' => Carbon::now(),
            ]);

            $addr = $driverAddresses_336991438[$i - 1];
            UserInfo::updateOrCreate(['user_id' => $driverUser->id], [
                'national_id' => '336991438' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'tax_code' => 'TX336991438' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_of_birth' => '1995-01-0' . (($i % 9) + 1),
                'full_address' => $addr['full_address'],
                'address_detail' => 'Khu vực giao hàng chính',
                'latitude' => $addr['latitude'],
                'longitude' => $addr['longitude'],
                'province_code' => null,
                'district_code' => null,
                'ward_code' => null,
            ]);
        }

        // ════════════════════════════════════════════
        // HUB: Bưu Điện Biên Giang
        // ID: 7893470209 | Chương Mỹ | (20.930468, 105.717365)
        // ════════════════════════════════════════════

        $hubUser = User::firstOrCreate(
            ['email' => 'hub_7893470209@example.com'],
            [
                'phone' => '024' . str_pad('7893470209', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu Điện Biên Giang',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '7893470209',
                'hub_latitude' => '20.930468',
                'hub_longitude' => '105.717365',
                'hub_address' => 'Chương Mỹ, Hà Nội',
            ]
        );

        $driverAddresses_7893470209 = [
            ['full_address' => 'VPBank NEO Express', 'latitude' => 20.962848, 'longitude' => 105.747626],
            ['full_address' => 'Bắc Sơn', 'latitude' => 20.922507, 'longitude' => 105.702385],
            ['full_address' => 'Trường Tiểu học Chúc Sơn A', 'latitude' => 20.918029, 'longitude' => 105.701139],
        ];

        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_7893470209_{$i}@example.com";
            $phone = "092" . substr(str_pad('7893470209', 9, '0', STR_PAD_LEFT), -6) . $i;
            $fullName = "Driver Bưu Điện Biên Giang #{$i}";

            $driverUser = User::firstOrCreate(['email' => $email], [
                'phone' => $phone,
                'full_name' => $fullName,
                'role' => 'driver',
                'status' => 'active',
                'password_hash' => Hash::make('123456'),
            ]);

            DriverProfile::updateOrCreate(['user_id' => $driverUser->id], [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'province_code' => 1,
                'post_office_id' => '7893470209',
                'post_office_name' => 'Bưu Điện Biên Giang',
                'post_office_address' => 'Chương Mỹ, Hà Nội',
                'post_office_lat' => '20.930468',
                'post_office_lng' => '105.717365',
                'post_office_phone' => '0241234567',
                'vehicle_type' => 'Xe máy',
                'license_number' => str_repeat($i, 8),
                'license_image' => 'license_image.png',
                'identity_image' => 'identity_image.png',
                'experience' => rand(1, 5) . ' năm',
                'status' => 'approved',
                'approved_at' => Carbon::now(),
            ]);

            $addr = $driverAddresses_7893470209[$i - 1];
            UserInfo::updateOrCreate(['user_id' => $driverUser->id], [
                'national_id' => '7893470209' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'tax_code' => 'TX7893470209' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_of_birth' => '1995-01-0' . (($i % 9) + 1),
                'full_address' => $addr['full_address'],
                'address_detail' => 'Khu vực giao hàng chính',
                'latitude' => $addr['latitude'],
                'longitude' => $addr['longitude'],
                'province_code' => null,
                'district_code' => null,
                'ward_code' => null,
            ]);
        }

        // ════════════════════════════════════════════
        // HUB: Bưu Điện Vạn Phúc
        // ID: 9042218607 | Chương Mỹ | (20.979893, 105.77479)
        // ════════════════════════════════════════════

        $hubUser = User::firstOrCreate(
            ['email' => 'hub_9042218607@example.com'],
            [
                'phone' => '024' . str_pad('9042218607', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu Điện Vạn Phúc',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '9042218607',
                'hub_latitude' => '20.979893',
                'hub_longitude' => '105.77479',
                'hub_address' => 'Chương Mỹ, Hà Nội',
            ]
        );

        $driverAddresses_9042218607 = [
            ['full_address' => 'Trần Kim Xuyến', 'latitude' => 21.019169, 'longitude' => 105.793509],
            ['full_address' => 'Đường Cầu Bươu', 'latitude' => 20.958673, 'longitude' => 105.805255],
            ['full_address' => 'Phố Trung Hoà', 'latitude' => 21.017158, 'longitude' => 105.799144],
        ];

        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_9042218607_{$i}@example.com";
            $phone = "092" . substr(str_pad('9042218607', 9, '0', STR_PAD_LEFT), -6) . $i;
            $fullName = "Driver Bưu Điện Vạn Phúc #{$i}";

            $driverUser = User::firstOrCreate(['email' => $email], [
                'phone' => $phone,
                'full_name' => $fullName,
                'role' => 'driver',
                'status' => 'active',
                'password_hash' => Hash::make('123456'),
            ]);

            DriverProfile::updateOrCreate(['user_id' => $driverUser->id], [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'province_code' => 1,
                'post_office_id' => '9042218607',
                'post_office_name' => 'Bưu Điện Vạn Phúc',
                'post_office_address' => 'Chương Mỹ, Hà Nội',
                'post_office_lat' => '20.979893',
                'post_office_lng' => '105.77479',
                'post_office_phone' => '0241234567',
                'vehicle_type' => 'Xe máy',
                'license_number' => str_repeat($i, 8),
                'license_image' => 'license_image.png',
                'identity_image' => 'identity_image.png',
                'experience' => rand(1, 5) . ' năm',
                'status' => 'approved',
                'approved_at' => Carbon::now(),
            ]);

            $addr = $driverAddresses_9042218607[$i - 1];
            UserInfo::updateOrCreate(['user_id' => $driverUser->id], [
                'national_id' => '9042218607' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'tax_code' => 'TX9042218607' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_of_birth' => '1995-01-0' . (($i % 9) + 1),
                'full_address' => $addr['full_address'],
                'address_detail' => 'Khu vực giao hàng chính',
                'latitude' => $addr['latitude'],
                'longitude' => $addr['longitude'],
                'province_code' => null,
                'district_code' => null,
                'ward_code' => null,
            ]);
        }

        // ════════════════════════════════════════════
        // HUB: Bưu điện Miếu Môn
        // ID: 763352687 | Chương Mỹ | (20.813549, 105.645371)
        // ════════════════════════════════════════════

        $hubUser = User::firstOrCreate(
            ['email' => 'hub_763352687@example.com'],
            [
                'phone' => '024' . str_pad('763352687', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu điện Miếu Môn',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '763352687',
                'hub_latitude' => '20.813549',
                'hub_longitude' => '105.645371',
                'hub_address' => 'Chương Mỹ, Hà Nội',
            ]
        );

        $driverAddresses_763352687 = [
            ['full_address' => 'Sky Lake', 'latitude' => 20.834401, 'longitude' => 105.619053],
            ['full_address' => 'Nhà Hàng Khánh Phương', 'latitude' => 20.814316, 'longitude' => 105.644973],
            ['full_address' => 'Phương Sự', 'latitude' => 20.814649, 'longitude' => 105.645304],
        ];

        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_763352687_{$i}@example.com";
            $phone = "092" . substr(str_pad('763352687', 9, '0', STR_PAD_LEFT), -6) . $i;
            $fullName = "Driver Bưu điện Miếu Môn #{$i}";

            $driverUser = User::firstOrCreate(['email' => $email], [
                'phone' => $phone,
                'full_name' => $fullName,
                'role' => 'driver',
                'status' => 'active',
                'password_hash' => Hash::make('123456'),
            ]);

            DriverProfile::updateOrCreate(['user_id' => $driverUser->id], [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'province_code' => 1,
                'post_office_id' => '763352687',
                'post_office_name' => 'Bưu điện Miếu Môn',
                'post_office_address' => 'Chương Mỹ, Hà Nội',
                'post_office_lat' => '20.813549',
                'post_office_lng' => '105.645371',
                'post_office_phone' => '0241234567',
                'vehicle_type' => 'Xe máy',
                'license_number' => str_repeat($i, 8),
                'license_image' => 'license_image.png',
                'identity_image' => 'identity_image.png',
                'experience' => rand(1, 5) . ' năm',
                'status' => 'approved',
                'approved_at' => Carbon::now(),
            ]);

            $addr = $driverAddresses_763352687[$i - 1];
            UserInfo::updateOrCreate(['user_id' => $driverUser->id], [
                'national_id' => '763352687' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'tax_code' => 'TX763352687' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_of_birth' => '1995-01-0' . (($i % 9) + 1),
                'full_address' => $addr['full_address'],
                'address_detail' => 'Khu vực giao hàng chính',
                'latitude' => $addr['latitude'],
                'longitude' => $addr['longitude'],
                'province_code' => null,
                'district_code' => null,
                'ward_code' => null,
            ]);
        }

        // ════════════════════════════════════════════
        // HUB: Bưu điện Hà Đông
        // ID: 1236192822 | Chương Mỹ | (20.973409, 105.778351)
        // ════════════════════════════════════════════

        $hubUser = User::firstOrCreate(
            ['email' => 'hub_1236192822@example.com'],
            [
                'phone' => '024' . str_pad('1236192822', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu điện Hà Đông',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '1236192822',
                'hub_latitude' => '20.973409',
                'hub_longitude' => '105.778351',
                'hub_address' => 'Đường Quang Trung',
            ]
        );

        $driverAddresses_1236192822 = [
            ['full_address' => 'Thượng Đình', 'latitude' => 20.997694, 'longitude' => 105.816063],
            ['full_address' => 'Phố Hoàng Đạo Thúy', 'latitude' => 21.004665, 'longitude' => 105.806167],
            ['full_address' => 'Ngõ 460 Khương Đình', 'latitude' => 20.986753, 'longitude' => 105.811091],
        ];

        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_1236192822_{$i}@example.com";
            $phone = "092" . substr(str_pad('1236192822', 9, '0', STR_PAD_LEFT), -6) . $i;
            $fullName = "Driver Bưu điện Hà Đông #{$i}";

            $driverUser = User::firstOrCreate(['email' => $email], [
                'phone' => $phone,
                'full_name' => $fullName,
                'role' => 'driver',
                'status' => 'active',
                'password_hash' => Hash::make('123456'),
            ]);

            DriverProfile::updateOrCreate(['user_id' => $driverUser->id], [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'province_code' => 1,
                'post_office_id' => '1236192822',
                'post_office_name' => 'Bưu điện Hà Đông',
                'post_office_address' => 'Đường Quang Trung',
                'post_office_lat' => '20.973409',
                'post_office_lng' => '105.778351',
                'post_office_phone' => '0241234567',
                'vehicle_type' => 'Xe máy',
                'license_number' => str_repeat($i, 8),
                'license_image' => 'license_image.png',
                'identity_image' => 'identity_image.png',
                'experience' => rand(1, 5) . ' năm',
                'status' => 'approved',
                'approved_at' => Carbon::now(),
            ]);

            $addr = $driverAddresses_1236192822[$i - 1];
            UserInfo::updateOrCreate(['user_id' => $driverUser->id], [
                'national_id' => '1236192822' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'tax_code' => 'TX1236192822' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_of_birth' => '1995-01-0' . (($i % 9) + 1),
                'full_address' => $addr['full_address'],
                'address_detail' => 'Khu vực giao hàng chính',
                'latitude' => $addr['latitude'],
                'longitude' => $addr['longitude'],
                'province_code' => null,
                'district_code' => null,
                'ward_code' => null,
            ]);
        }

        // ════════════════════════════════════════════
        // HUB: Bưu điện huyện Chương Mỹ
        // ID: 1267913616 | Chương Mỹ | (20.92403, 105.705165)
        // ════════════════════════════════════════════

        $hubUser = User::firstOrCreate(
            ['email' => 'hub_1267913616@example.com'],
            [
                'phone' => '024' . str_pad('1267913616', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu điện huyện Chương Mỹ',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '1267913616',
                'hub_latitude' => '20.92403',
                'hub_longitude' => '105.705165',
                'hub_address' => 'Chương Mỹ, Hà Nội',
            ]
        );

        $driverAddresses_1267913616 = [
            ['full_address' => 'Bắc Sơn', 'latitude' => 20.922507, 'longitude' => 105.702385],
            ['full_address' => 'Trường Trung học cơ sở ĐạI Thành', 'latitude' => 20.965247, 'longitude' => 105.708377],
            ['full_address' => 'Phượng Nghĩa', 'latitude' => 20.949577, 'longitude' => 105.70237],
        ];

        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_1267913616_{$i}@example.com";
            $phone = "092" . substr(str_pad('1267913616', 9, '0', STR_PAD_LEFT), -6) . $i;
            $fullName = "Driver Bưu điện huyện Chương Mỹ #{$i}";

            $driverUser = User::firstOrCreate(['email' => $email], [
                'phone' => $phone,
                'full_name' => $fullName,
                'role' => 'driver',
                'status' => 'active',
                'password_hash' => Hash::make('123456'),
            ]);

            DriverProfile::updateOrCreate(['user_id' => $driverUser->id], [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'province_code' => 1,
                'post_office_id' => '1267913616',
                'post_office_name' => 'Bưu điện huyện Chương Mỹ',
                'post_office_address' => 'Chương Mỹ, Hà Nội',
                'post_office_lat' => '20.92403',
                'post_office_lng' => '105.705165',
                'post_office_phone' => '0241234567',
                'vehicle_type' => 'Xe máy',
                'license_number' => str_repeat($i, 8),
                'license_image' => 'license_image.png',
                'identity_image' => 'identity_image.png',
                'experience' => rand(1, 5) . ' năm',
                'status' => 'approved',
                'approved_at' => Carbon::now(),
            ]);

            $addr = $driverAddresses_1267913616[$i - 1];
            UserInfo::updateOrCreate(['user_id' => $driverUser->id], [
                'national_id' => '1267913616' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'tax_code' => 'TX1267913616' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_of_birth' => '1995-01-0' . (($i % 9) + 1),
                'full_address' => $addr['full_address'],
                'address_detail' => 'Khu vực giao hàng chính',
                'latitude' => $addr['latitude'],
                'longitude' => $addr['longitude'],
                'province_code' => null,
                'district_code' => null,
                'ward_code' => null,
            ]);
        }

        // ════════════════════════════════════════════
        // HUB: Bưu điện huyện Mỹ Đức
        // ID: 768696793 | Mỹ Đức | (20.685943, 105.744251)
        // ════════════════════════════════════════════

        $hubUser = User::firstOrCreate(
            ['email' => 'hub_768696793@example.com'],
            [
                'phone' => '024' . str_pad('768696793', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu điện huyện Mỹ Đức',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '768696793',
                'hub_latitude' => '20.685943',
                'hub_longitude' => '105.744251',
                'hub_address' => 'Mỹ Đức, Hà Nội',
            ]
        );

        $driverAddresses_768696793 = [
            ['full_address' => 'Trường Tiểu Học Hòa Xá', 'latitude' => 20.693807, 'longitude' => 105.755459],
            ['full_address' => 'Trường Trung học cơ sở Vạn Thái', 'latitude' => 20.706029, 'longitude' => 105.766189],
            ['full_address' => 'Trường Trung học cơ sở Hòa Nam', 'latitude' => 20.681223, 'longitude' => 105.758913],
        ];

        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_768696793_{$i}@example.com";
            $phone = "092" . substr(str_pad('768696793', 9, '0', STR_PAD_LEFT), -6) . $i;
            $fullName = "Driver Bưu điện huyện Mỹ Đức #{$i}";

            $driverUser = User::firstOrCreate(['email' => $email], [
                'phone' => $phone,
                'full_name' => $fullName,
                'role' => 'driver',
                'status' => 'active',
                'password_hash' => Hash::make('123456'),
            ]);

            DriverProfile::updateOrCreate(['user_id' => $driverUser->id], [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'province_code' => 1,
                'post_office_id' => '768696793',
                'post_office_name' => 'Bưu điện huyện Mỹ Đức',
                'post_office_address' => 'Mỹ Đức, Hà Nội',
                'post_office_lat' => '20.685943',
                'post_office_lng' => '105.744251',
                'post_office_phone' => '0241234567',
                'vehicle_type' => 'Xe máy',
                'license_number' => str_repeat($i, 8),
                'license_image' => 'license_image.png',
                'identity_image' => 'identity_image.png',
                'experience' => rand(1, 5) . ' năm',
                'status' => 'approved',
                'approved_at' => Carbon::now(),
            ]);

            $addr = $driverAddresses_768696793[$i - 1];
            UserInfo::updateOrCreate(['user_id' => $driverUser->id], [
                'national_id' => '768696793' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'tax_code' => 'TX768696793' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_of_birth' => '1995-01-0' . (($i % 9) + 1),
                'full_address' => $addr['full_address'],
                'address_detail' => 'Khu vực giao hàng chính',
                'latitude' => $addr['latitude'],
                'longitude' => $addr['longitude'],
                'province_code' => null,
                'district_code' => null,
                'ward_code' => null,
            ]);
        }

        // ════════════════════════════════════════════
        // HUB: Bưu cục Trương Xá
        // ID: 7858023069 | Ứng Hòa | (20.790745, 106.046908)
        // ════════════════════════════════════════════

        $hubUser = User::firstOrCreate(
            ['email' => 'hub_7858023069@example.com'],
            [
                'phone' => '024' . str_pad('7858023069', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu cục Trương Xá',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '7858023069',
                'hub_latitude' => '20.790745',
                'hub_longitude' => '106.046908',
                'hub_address' => 'Ứng Hòa, Hà Nội',
            ]
        );

        $driverAddresses_7858023069 = [
            ['full_address' => 'Gần Bưu cục Trương Xá', 'latitude' => 20.787432, 'longitude' => 106.043595],
            ['full_address' => 'Gần Bưu cục Trương Xá', 'latitude' => 20.792651, 'longitude' => 106.048814],
            ['full_address' => 'Gần Bưu cục Trương Xá', 'latitude' => 20.790893, 'longitude' => 106.047056],
        ];

        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_7858023069_{$i}@example.com";
            $phone = "092" . substr(str_pad('7858023069', 9, '0', STR_PAD_LEFT), -6) . $i;
            $fullName = "Driver Bưu cục Trương Xá #{$i}";

            $driverUser = User::firstOrCreate(['email' => $email], [
                'phone' => $phone,
                'full_name' => $fullName,
                'role' => 'driver',
                'status' => 'active',
                'password_hash' => Hash::make('123456'),
            ]);

            DriverProfile::updateOrCreate(['user_id' => $driverUser->id], [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'province_code' => 1,
                'post_office_id' => '7858023069',
                'post_office_name' => 'Bưu cục Trương Xá',
                'post_office_address' => 'Ứng Hòa, Hà Nội',
                'post_office_lat' => '20.790745',
                'post_office_lng' => '106.046908',
                'post_office_phone' => '0241234567',
                'vehicle_type' => 'Xe máy',
                'license_number' => str_repeat($i, 8),
                'license_image' => 'license_image.png',
                'identity_image' => 'identity_image.png',
                'experience' => rand(1, 5) . ' năm',
                'status' => 'approved',
                'approved_at' => Carbon::now(),
            ]);

            $addr = $driverAddresses_7858023069[$i - 1];
            UserInfo::updateOrCreate(['user_id' => $driverUser->id], [
                'national_id' => '7858023069' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'tax_code' => 'TX7858023069' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_of_birth' => '1995-01-0' . (($i % 9) + 1),
                'full_address' => $addr['full_address'],
                'address_detail' => 'Khu vực giao hàng chính',
                'latitude' => $addr['latitude'],
                'longitude' => $addr['longitude'],
                'province_code' => null,
                'district_code' => null,
                'ward_code' => null,
            ]);
        }

        // ════════════════════════════════════════════
        // HUB: Bưu cục Thọ Vinh
        // ID: 7858023070 | Ứng Hòa | (20.754132, 105.987888)
        // ════════════════════════════════════════════

        $hubUser = User::firstOrCreate(
            ['email' => 'hub_7858023070@example.com'],
            [
                'phone' => '024' . str_pad('7858023070', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu cục Thọ Vinh',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '7858023070',
                'hub_latitude' => '20.754132',
                'hub_longitude' => '105.987888',
                'hub_address' => 'Ứng Hòa, Hà Nội',
            ]
        );

        $driverAddresses_7858023070 = [
            ['full_address' => 'Trường Trung học cơ sở Hồng Thái', 'latitude' => 20.761937, 'longitude' => 105.955405],
            ['full_address' => 'Trường Trung học cơ sở Khai Thái', 'latitude' => 20.723167, 'longitude' => 105.958395],
            ['full_address' => 'Đường phú khê', 'latitude' => 20.755639, 'longitude' => 105.97306],
        ];

        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_7858023070_{$i}@example.com";
            $phone = "092" . substr(str_pad('7858023070', 9, '0', STR_PAD_LEFT), -6) . $i;
            $fullName = "Driver Bưu cục Thọ Vinh #{$i}";

            $driverUser = User::firstOrCreate(['email' => $email], [
                'phone' => $phone,
                'full_name' => $fullName,
                'role' => 'driver',
                'status' => 'active',
                'password_hash' => Hash::make('123456'),
            ]);

            DriverProfile::updateOrCreate(['user_id' => $driverUser->id], [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'province_code' => 1,
                'post_office_id' => '7858023070',
                'post_office_name' => 'Bưu cục Thọ Vinh',
                'post_office_address' => 'Ứng Hòa, Hà Nội',
                'post_office_lat' => '20.754132',
                'post_office_lng' => '105.987888',
                'post_office_phone' => '0241234567',
                'vehicle_type' => 'Xe máy',
                'license_number' => str_repeat($i, 8),
                'license_image' => 'license_image.png',
                'identity_image' => 'identity_image.png',
                'experience' => rand(1, 5) . ' năm',
                'status' => 'approved',
                'approved_at' => Carbon::now(),
            ]);

            $addr = $driverAddresses_7858023070[$i - 1];
            UserInfo::updateOrCreate(['user_id' => $driverUser->id], [
                'national_id' => '7858023070' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'tax_code' => 'TX7858023070' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_of_birth' => '1995-01-0' . (($i % 9) + 1),
                'full_address' => $addr['full_address'],
                'address_detail' => 'Khu vực giao hàng chính',
                'latitude' => $addr['latitude'],
                'longitude' => $addr['longitude'],
                'province_code' => null,
                'district_code' => null,
                'ward_code' => null,
            ]);
        }

        // ════════════════════════════════════════════
        // HUB: Bưu cục Đức Hợp
        // ID: 512905870 | Ứng Hòa | (20.725727, 105.993557)
        // ════════════════════════════════════════════

        $hubUser = User::firstOrCreate(
            ['email' => 'hub_512905870@example.com'],
            [
                'phone' => '024' . str_pad('512905870', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu cục Đức Hợp',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '512905870',
                'hub_latitude' => '20.725727',
                'hub_longitude' => '105.993557',
                'hub_address' => 'Ứng Hòa, Hà Nội',
            ]
        );

        $driverAddresses_512905870 = [
            ['full_address' => 'Đường phú khê', 'latitude' => 20.755639, 'longitude' => 105.97306],
            ['full_address' => 'Đường phú khê', 'latitude' => 20.756232, 'longitude' => 105.973009],
            ['full_address' => 'Trường Trung học cơ sở Khai Thái', 'latitude' => 20.723167, 'longitude' => 105.958395],
        ];

        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_512905870_{$i}@example.com";
            $phone = "092" . substr(str_pad('512905870', 9, '0', STR_PAD_LEFT), -6) . $i;
            $fullName = "Driver Bưu cục Đức Hợp #{$i}";

            $driverUser = User::firstOrCreate(['email' => $email], [
                'phone' => $phone,
                'full_name' => $fullName,
                'role' => 'driver',
                'status' => 'active',
                'password_hash' => Hash::make('123456'),
            ]);

            DriverProfile::updateOrCreate(['user_id' => $driverUser->id], [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'province_code' => 1,
                'post_office_id' => '512905870',
                'post_office_name' => 'Bưu cục Đức Hợp',
                'post_office_address' => 'Ứng Hòa, Hà Nội',
                'post_office_lat' => '20.725727',
                'post_office_lng' => '105.993557',
                'post_office_phone' => '0241234567',
                'vehicle_type' => 'Xe máy',
                'license_number' => str_repeat($i, 8),
                'license_image' => 'license_image.png',
                'identity_image' => 'identity_image.png',
                'experience' => rand(1, 5) . ' năm',
                'status' => 'approved',
                'approved_at' => Carbon::now(),
            ]);

            $addr = $driverAddresses_512905870[$i - 1];
            UserInfo::updateOrCreate(['user_id' => $driverUser->id], [
                'national_id' => '512905870' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'tax_code' => 'TX512905870' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_of_birth' => '1995-01-0' . (($i % 9) + 1),
                'full_address' => $addr['full_address'],
                'address_detail' => 'Khu vực giao hàng chính',
                'latitude' => $addr['latitude'],
                'longitude' => $addr['longitude'],
                'province_code' => null,
                'district_code' => null,
                'ward_code' => null,
            ]);
        }

        // ════════════════════════════════════════════
        // HUB: Điểm BĐVHX Phú Cường
        // ID: 496826423 | Ứng Hòa | (20.701411, 106.02884)
        // ════════════════════════════════════════════

        $hubUser = User::firstOrCreate(
            ['email' => 'hub_496826423@example.com'],
            [
                'phone' => '024' . str_pad('496826423', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Điểm BĐVHX Phú Cường',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '496826423',
                'hub_latitude' => '20.701411',
                'hub_longitude' => '106.02884',
                'hub_address' => 'Ứng Hòa, Hà Nội',
            ]
        );

        $driverAddresses_496826423 = [
            ['full_address' => 'Gần Điểm BĐVHX Phú Cường', 'latitude' => 20.705405, 'longitude' => 106.032834],
            ['full_address' => 'Gần Điểm BĐVHX Phú Cường', 'latitude' => 20.70121, 'longitude' => 106.028639],
            ['full_address' => 'Gần Điểm BĐVHX Phú Cường', 'latitude' => 20.702823, 'longitude' => 106.030252],
        ];

        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_496826423_{$i}@example.com";
            $phone = "092" . substr(str_pad('496826423', 9, '0', STR_PAD_LEFT), -6) . $i;
            $fullName = "Driver Điểm BĐVHX Phú Cường #{$i}";

            $driverUser = User::firstOrCreate(['email' => $email], [
                'phone' => $phone,
                'full_name' => $fullName,
                'role' => 'driver',
                'status' => 'active',
                'password_hash' => Hash::make('123456'),
            ]);

            DriverProfile::updateOrCreate(['user_id' => $driverUser->id], [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'province_code' => 1,
                'post_office_id' => '496826423',
                'post_office_name' => 'Điểm BĐVHX Phú Cường',
                'post_office_address' => 'Ứng Hòa, Hà Nội',
                'post_office_lat' => '20.701411',
                'post_office_lng' => '106.02884',
                'post_office_phone' => '0241234567',
                'vehicle_type' => 'Xe máy',
                'license_number' => str_repeat($i, 8),
                'license_image' => 'license_image.png',
                'identity_image' => 'identity_image.png',
                'experience' => rand(1, 5) . ' năm',
                'status' => 'approved',
                'approved_at' => Carbon::now(),
            ]);

            $addr = $driverAddresses_496826423[$i - 1];
            UserInfo::updateOrCreate(['user_id' => $driverUser->id], [
                'national_id' => '496826423' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'tax_code' => 'TX496826423' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_of_birth' => '1995-01-0' . (($i % 9) + 1),
                'full_address' => $addr['full_address'],
                'address_detail' => 'Khu vực giao hàng chính',
                'latitude' => $addr['latitude'],
                'longitude' => $addr['longitude'],
                'province_code' => null,
                'district_code' => null,
                'ward_code' => null,
            ]);
        }

        // ════════════════════════════════════════════
        // HUB: Bưu Điện Cầu Giấy
        // ID: 714524067 | Hoài Đức | (21.032534, 105.798427)
        // ════════════════════════════════════════════

        $hubUser = User::firstOrCreate(
            ['email' => 'hub_714524067@example.com'],
            [
                'phone' => '024' . str_pad('714524067', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu Điện Cầu Giấy',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '714524067',
                'hub_latitude' => '21.032534',
                'hub_longitude' => '105.798427',
                'hub_address' => 'Đường Cầu Giấy',
            ]
        );

        $driverAddresses_714524067 = [
            ['full_address' => 'Bánh cuốn Bà Khê', 'latitude' => 21.040504, 'longitude' => 105.784364],
            ['full_address' => 'Oceanbank', 'latitude' => 21.060339, 'longitude' => 105.833096],
            ['full_address' => 'AU', 'latitude' => 21.031298, 'longitude' => 105.840646],
        ];

        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_714524067_{$i}@example.com";
            $phone = "092" . substr(str_pad('714524067', 9, '0', STR_PAD_LEFT), -6) . $i;
            $fullName = "Driver Bưu Điện Cầu Giấy #{$i}";

            $driverUser = User::firstOrCreate(['email' => $email], [
                'phone' => $phone,
                'full_name' => $fullName,
                'role' => 'driver',
                'status' => 'active',
                'password_hash' => Hash::make('123456'),
            ]);

            DriverProfile::updateOrCreate(['user_id' => $driverUser->id], [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'province_code' => 1,
                'post_office_id' => '714524067',
                'post_office_name' => 'Bưu Điện Cầu Giấy',
                'post_office_address' => 'Đường Cầu Giấy',
                'post_office_lat' => '21.032534',
                'post_office_lng' => '105.798427',
                'post_office_phone' => '0241234567',
                'vehicle_type' => 'Xe máy',
                'license_number' => str_repeat($i, 8),
                'license_image' => 'license_image.png',
                'identity_image' => 'identity_image.png',
                'experience' => rand(1, 5) . ' năm',
                'status' => 'approved',
                'approved_at' => Carbon::now(),
            ]);

            $addr = $driverAddresses_714524067[$i - 1];
            UserInfo::updateOrCreate(['user_id' => $driverUser->id], [
                'national_id' => '714524067' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'tax_code' => 'TX714524067' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_of_birth' => '1995-01-0' . (($i % 9) + 1),
                'full_address' => $addr['full_address'],
                'address_detail' => 'Khu vực giao hàng chính',
                'latitude' => $addr['latitude'],
                'longitude' => $addr['longitude'],
                'province_code' => null,
                'district_code' => null,
                'ward_code' => null,
            ]);
        }

        // ════════════════════════════════════════════
        // HUB: VIETTEL POST
        // ID: 958844583 | Hoài Đức | (21.038059, 105.801133)
        // ════════════════════════════════════════════

        $hubUser = User::firstOrCreate(
            ['email' => 'hub_958844583@example.com'],
            [
                'phone' => '024' . str_pad('958844583', 7, '0', STR_PAD_LEFT),
                'full_name' => 'VIETTEL POST',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '958844583',
                'hub_latitude' => '21.038059',
                'hub_longitude' => '105.801133',
                'hub_address' => 'Ngõ 118 Nguyễn Khánh Toàn',
            ]
        );

        $driverAddresses_958844583 = [
            ['full_address' => 'Phố Trung Hoà', 'latitude' => 21.017158, 'longitude' => 105.799144],
            ['full_address' => 'Phố Phan Đình Phùng', 'latitude' => 21.040108, 'longitude' => 105.844702],
            ['full_address' => 'Phõ Láng Hạ', 'latitude' => 21.011725, 'longitude' => 105.811917],
        ];

        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_958844583_{$i}@example.com";
            $phone = "092" . substr(str_pad('958844583', 9, '0', STR_PAD_LEFT), -6) . $i;
            $fullName = "Driver VIETTEL POST #{$i}";

            $driverUser = User::firstOrCreate(['email' => $email], [
                'phone' => $phone,
                'full_name' => $fullName,
                'role' => 'driver',
                'status' => 'active',
                'password_hash' => Hash::make('123456'),
            ]);

            DriverProfile::updateOrCreate(['user_id' => $driverUser->id], [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'province_code' => 1,
                'post_office_id' => '958844583',
                'post_office_name' => 'VIETTEL POST',
                'post_office_address' => 'Ngõ 118 Nguyễn Khánh Toàn',
                'post_office_lat' => '21.038059',
                'post_office_lng' => '105.801133',
                'post_office_phone' => '0241234567',
                'vehicle_type' => 'Xe máy',
                'license_number' => str_repeat($i, 8),
                'license_image' => 'license_image.png',
                'identity_image' => 'identity_image.png',
                'experience' => rand(1, 5) . ' năm',
                'status' => 'approved',
                'approved_at' => Carbon::now(),
            ]);

            $addr = $driverAddresses_958844583[$i - 1];
            UserInfo::updateOrCreate(['user_id' => $driverUser->id], [
                'national_id' => '958844583' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'tax_code' => 'TX958844583' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_of_birth' => '1995-01-0' . (($i % 9) + 1),
                'full_address' => $addr['full_address'],
                'address_detail' => 'Khu vực giao hàng chính',
                'latitude' => $addr['latitude'],
                'longitude' => $addr['longitude'],
                'province_code' => null,
                'district_code' => null,
                'ward_code' => null,
            ]);
        }

        // ════════════════════════════════════════════
        // HUB: Bưu Cục Hàng Cót
        // ID: 945767987 | Hoài Đức | (21.037995, 105.846254)
        // ════════════════════════════════════════════

        $hubUser = User::firstOrCreate(
            ['email' => 'hub_945767987@example.com'],
            [
                'phone' => '024' . str_pad('945767987', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu Cục Hàng Cót',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '945767987',
                'hub_latitude' => '21.037995',
                'hub_longitude' => '105.846254',
                'hub_address' => 'Phố Phùng Hưng',
            ]
        );

        $driverAddresses_945767987 = [
            ['full_address' => 'Nhà Hàng & Cà Phê Joc', 'latitude' => 21.024598, 'longitude' => 105.858548],
            ['full_address' => 'Phố Phan Đình Phùng', 'latitude' => 21.04014, 'longitude' => 105.84436],
            ['full_address' => 'Talky cafe $', 'latitude' => 21.015343, 'longitude' => 105.813105],
        ];

        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_945767987_{$i}@example.com";
            $phone = "092" . substr(str_pad('945767987', 9, '0', STR_PAD_LEFT), -6) . $i;
            $fullName = "Driver Bưu Cục Hàng Cót #{$i}";

            $driverUser = User::firstOrCreate(['email' => $email], [
                'phone' => $phone,
                'full_name' => $fullName,
                'role' => 'driver',
                'status' => 'active',
                'password_hash' => Hash::make('123456'),
            ]);

            DriverProfile::updateOrCreate(['user_id' => $driverUser->id], [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'province_code' => 1,
                'post_office_id' => '945767987',
                'post_office_name' => 'Bưu Cục Hàng Cót',
                'post_office_address' => 'Phố Phùng Hưng',
                'post_office_lat' => '21.037995',
                'post_office_lng' => '105.846254',
                'post_office_phone' => '0241234567',
                'vehicle_type' => 'Xe máy',
                'license_number' => str_repeat($i, 8),
                'license_image' => 'license_image.png',
                'identity_image' => 'identity_image.png',
                'experience' => rand(1, 5) . ' năm',
                'status' => 'approved',
                'approved_at' => Carbon::now(),
            ]);

            $addr = $driverAddresses_945767987[$i - 1];
            UserInfo::updateOrCreate(['user_id' => $driverUser->id], [
                'national_id' => '945767987' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'tax_code' => 'TX945767987' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_of_birth' => '1995-01-0' . (($i % 9) + 1),
                'full_address' => $addr['full_address'],
                'address_detail' => 'Khu vực giao hàng chính',
                'latitude' => $addr['latitude'],
                'longitude' => $addr['longitude'],
                'province_code' => null,
                'district_code' => null,
                'ward_code' => null,
            ]);
        }

        // ════════════════════════════════════════════
        // HUB: Bưu cục Hoài Đức
        // ID: 5281622724 | Hoài Đức | (21.045069, 105.843277)
        // ════════════════════════════════════════════

        $hubUser = User::firstOrCreate(
            ['email' => 'hub_5281622724@example.com'],
            [
                'phone' => '024' . str_pad('5281622724', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu cục Hoài Đức',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '5281622724',
                'hub_latitude' => '21.045069',
                'hub_longitude' => '105.843277',
                'hub_address' => 'Hoài Đức, Hà Nội',
            ]
        );

        $driverAddresses_5281622724 = [
            ['full_address' => 'Phố Bùi Thị Xuân', 'latitude' => 21.013997, 'longitude' => 105.849991],
            ['full_address' => 'Phố Phủ Doãn', 'latitude' => 21.030291, 'longitude' => 105.847648],
            ['full_address' => 'Nhà Hàng Nét Huế', 'latitude' => 21.032707, 'longitude' => 105.854384],
        ];

        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_5281622724_{$i}@example.com";
            $phone = "092" . substr(str_pad('5281622724', 9, '0', STR_PAD_LEFT), -6) . $i;
            $fullName = "Driver Bưu cục Hoài Đức #{$i}";

            $driverUser = User::firstOrCreate(['email' => $email], [
                'phone' => $phone,
                'full_name' => $fullName,
                'role' => 'driver',
                'status' => 'active',
                'password_hash' => Hash::make('123456'),
            ]);

            DriverProfile::updateOrCreate(['user_id' => $driverUser->id], [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'province_code' => 1,
                'post_office_id' => '5281622724',
                'post_office_name' => 'Bưu cục Hoài Đức',
                'post_office_address' => 'Hoài Đức, Hà Nội',
                'post_office_lat' => '21.045069',
                'post_office_lng' => '105.843277',
                'post_office_phone' => '0241234567',
                'vehicle_type' => 'Xe máy',
                'license_number' => str_repeat($i, 8),
                'license_image' => 'license_image.png',
                'identity_image' => 'identity_image.png',
                'experience' => rand(1, 5) . ' năm',
                'status' => 'approved',
                'approved_at' => Carbon::now(),
            ]);

            $addr = $driverAddresses_5281622724[$i - 1];
            UserInfo::updateOrCreate(['user_id' => $driverUser->id], [
                'national_id' => '5281622724' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'tax_code' => 'TX5281622724' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_of_birth' => '1995-01-0' . (($i % 9) + 1),
                'full_address' => $addr['full_address'],
                'address_detail' => 'Khu vực giao hàng chính',
                'latitude' => $addr['latitude'],
                'longitude' => $addr['longitude'],
                'province_code' => null,
                'district_code' => null,
                'ward_code' => null,
            ]);
        }

        // ════════════════════════════════════════════
        // HUB: Bưu Điện Xuân Phương
        // ID: 204212696 | Hoài Đức | (21.03465, 105.747028)
        // ════════════════════════════════════════════

        $hubUser = User::firstOrCreate(
            ['email' => 'hub_204212696@example.com'],
            [
                'phone' => '024' . str_pad('204212696', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu Điện Xuân Phương',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '204212696',
                'hub_latitude' => '21.03465',
                'hub_longitude' => '105.747028',
                'hub_address' => 'Hoài Đức, Hà Nội',
            ]
        );

        $driverAddresses_204212696 = [
            ['full_address' => 'Phố Trần Thái Tông', 'latitude' => 21.034271, 'longitude' => 105.78921],
            ['full_address' => 'Đường Hồ Tùng Mậu', 'latitude' => 21.036895, 'longitude' => 105.77763],
            ['full_address' => 'Duck Coffee', 'latitude' => 21.06601, 'longitude' => 105.781415],
        ];

        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_204212696_{$i}@example.com";
            $phone = "092" . substr(str_pad('204212696', 9, '0', STR_PAD_LEFT), -6) . $i;
            $fullName = "Driver Bưu Điện Xuân Phương #{$i}";

            $driverUser = User::firstOrCreate(['email' => $email], [
                'phone' => $phone,
                'full_name' => $fullName,
                'role' => 'driver',
                'status' => 'active',
                'password_hash' => Hash::make('123456'),
            ]);

            DriverProfile::updateOrCreate(['user_id' => $driverUser->id], [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'province_code' => 1,
                'post_office_id' => '204212696',
                'post_office_name' => 'Bưu Điện Xuân Phương',
                'post_office_address' => 'Hoài Đức, Hà Nội',
                'post_office_lat' => '21.03465',
                'post_office_lng' => '105.747028',
                'post_office_phone' => '0241234567',
                'vehicle_type' => 'Xe máy',
                'license_number' => str_repeat($i, 8),
                'license_image' => 'license_image.png',
                'identity_image' => 'identity_image.png',
                'experience' => rand(1, 5) . ' năm',
                'status' => 'approved',
                'approved_at' => Carbon::now(),
            ]);

            $addr = $driverAddresses_204212696[$i - 1];
            UserInfo::updateOrCreate(['user_id' => $driverUser->id], [
                'national_id' => '204212696' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'tax_code' => 'TX204212696' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_of_birth' => '1995-01-0' . (($i % 9) + 1),
                'full_address' => $addr['full_address'],
                'address_detail' => 'Khu vực giao hàng chính',
                'latitude' => $addr['latitude'],
                'longitude' => $addr['longitude'],
                'province_code' => null,
                'district_code' => null,
                'ward_code' => null,
            ]);
        }

        // ════════════════════════════════════════════
        // HUB: Bưu Cục Giảng Võ
        // ID: 073350432 | Hoài Đức | (21.026417, 105.822812)
        // ════════════════════════════════════════════

        $hubUser = User::firstOrCreate(
            ['email' => 'hub_073350432@example.com'],
            [
                'phone' => '024' . str_pad('073350432', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu Cục Giảng Võ',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '073350432',
                'hub_latitude' => '21.026417',
                'hub_longitude' => '105.822812',
                'hub_address' => 'Giảng Võ',
            ]
        );

        $driverAddresses_073350432 = [
            ['full_address' => 'Phùng Chí Kiên', 'latitude' => 21.044636, 'longitude' => 105.802737],
            ['full_address' => '76 Train Street', 'latitude' => 21.030866, 'longitude' => 105.844779],
            ['full_address' => 'Phố Phan Đình Phùng', 'latitude' => 21.040692, 'longitude' => 105.842514],
        ];

        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_073350432_{$i}@example.com";
            $phone = "092" . substr(str_pad('073350432', 9, '0', STR_PAD_LEFT), -6) . $i;
            $fullName = "Driver Bưu Cục Giảng Võ #{$i}";

            $driverUser = User::firstOrCreate(['email' => $email], [
                'phone' => $phone,
                'full_name' => $fullName,
                'role' => 'driver',
                'status' => 'active',
                'password_hash' => Hash::make('123456'),
            ]);

            DriverProfile::updateOrCreate(['user_id' => $driverUser->id], [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'province_code' => 1,
                'post_office_id' => '073350432',
                'post_office_name' => 'Bưu Cục Giảng Võ',
                'post_office_address' => 'Giảng Võ',
                'post_office_lat' => '21.026417',
                'post_office_lng' => '105.822812',
                'post_office_phone' => '0241234567',
                'vehicle_type' => 'Xe máy',
                'license_number' => str_repeat($i, 8),
                'license_image' => 'license_image.png',
                'identity_image' => 'identity_image.png',
                'experience' => rand(1, 5) . ' năm',
                'status' => 'approved',
                'approved_at' => Carbon::now(),
            ]);

            $addr = $driverAddresses_073350432[$i - 1];
            UserInfo::updateOrCreate(['user_id' => $driverUser->id], [
                'national_id' => '073350432' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'tax_code' => 'TX073350432' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_of_birth' => '1995-01-0' . (($i % 9) + 1),
                'full_address' => $addr['full_address'],
                'address_detail' => 'Khu vực giao hàng chính',
                'latitude' => $addr['latitude'],
                'longitude' => $addr['longitude'],
                'province_code' => null,
                'district_code' => null,
                'ward_code' => null,
            ]);
        }

        // ════════════════════════════════════════════
        // HUB: Bưu Điện Trung Tâm 4
        // ID: 691757115 | Hoài Đức | (21.034851, 105.826192)
        // ════════════════════════════════════════════

        $hubUser = User::firstOrCreate(
            ['email' => 'hub_691757115@example.com'],
            [
                'phone' => '024' . str_pad('691757115', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu Điện Trung Tâm 4',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '691757115',
                'hub_latitude' => '21.034851',
                'hub_longitude' => '105.826192',
                'hub_address' => 'Đội Cấn',
            ]
        );

        $driverAddresses_691757115 = [
            ['full_address' => 'Maritime Bank', 'latitude' => 21.034796, 'longitude' => 105.795158],
            ['full_address' => 'Phố Dịch Vọng Hậu', 'latitude' => 21.030589, 'longitude' => 105.787492],
            ['full_address' => 'Phố Lý Đạo Thành', 'latitude' => 21.025205, 'longitude' => 105.857372],
        ];

        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_691757115_{$i}@example.com";
            $phone = "092" . substr(str_pad('691757115', 9, '0', STR_PAD_LEFT), -6) . $i;
            $fullName = "Driver Bưu Điện Trung Tâm 4 #{$i}";

            $driverUser = User::firstOrCreate(['email' => $email], [
                'phone' => $phone,
                'full_name' => $fullName,
                'role' => 'driver',
                'status' => 'active',
                'password_hash' => Hash::make('123456'),
            ]);

            DriverProfile::updateOrCreate(['user_id' => $driverUser->id], [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'province_code' => 1,
                'post_office_id' => '691757115',
                'post_office_name' => 'Bưu Điện Trung Tâm 4',
                'post_office_address' => 'Đội Cấn',
                'post_office_lat' => '21.034851',
                'post_office_lng' => '105.826192',
                'post_office_phone' => '0241234567',
                'vehicle_type' => 'Xe máy',
                'license_number' => str_repeat($i, 8),
                'license_image' => 'license_image.png',
                'identity_image' => 'identity_image.png',
                'experience' => rand(1, 5) . ' năm',
                'status' => 'approved',
                'approved_at' => Carbon::now(),
            ]);

            $addr = $driverAddresses_691757115[$i - 1];
            UserInfo::updateOrCreate(['user_id' => $driverUser->id], [
                'national_id' => '691757115' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'tax_code' => 'TX691757115' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_of_birth' => '1995-01-0' . (($i % 9) + 1),
                'full_address' => $addr['full_address'],
                'address_detail' => 'Khu vực giao hàng chính',
                'latitude' => $addr['latitude'],
                'longitude' => $addr['longitude'],
                'province_code' => null,
                'district_code' => null,
                'ward_code' => null,
            ]);
        }

        // ════════════════════════════════════════════
        // HUB: Bưu điện Đồng Xuân
        // ID: 470418302 | Hoài Đức | (21.03799, 105.848714)
        // ════════════════════════════════════════════

        $hubUser = User::firstOrCreate(
            ['email' => 'hub_470418302@example.com'],
            [
                'phone' => '024' . str_pad('470418302', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu điện Đồng Xuân',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '470418302',
                'hub_latitude' => '21.03799',
                'hub_longitude' => '105.848714',
                'hub_address' => 'Phố Đồng Xuân',
            ]
        );

        $driverAddresses_470418302 = [
            ['full_address' => 'Học Viện Không Quân', 'latitude' => 21.000667, 'longitude' => 105.827072],
            ['full_address' => 'Phố Hàng Quạt', 'latitude' => 21.032311, 'longitude' => 105.849141],
            ['full_address' => 'Phố Ngọc Khánh', 'latitude' => 21.026961, 'longitude' => 105.817806],
        ];

        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_470418302_{$i}@example.com";
            $phone = "092" . substr(str_pad('470418302', 9, '0', STR_PAD_LEFT), -6) . $i;
            $fullName = "Driver Bưu điện Đồng Xuân #{$i}";

            $driverUser = User::firstOrCreate(['email' => $email], [
                'phone' => $phone,
                'full_name' => $fullName,
                'role' => 'driver',
                'status' => 'active',
                'password_hash' => Hash::make('123456'),
            ]);

            DriverProfile::updateOrCreate(['user_id' => $driverUser->id], [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'province_code' => 1,
                'post_office_id' => '470418302',
                'post_office_name' => 'Bưu điện Đồng Xuân',
                'post_office_address' => 'Phố Đồng Xuân',
                'post_office_lat' => '21.03799',
                'post_office_lng' => '105.848714',
                'post_office_phone' => '0241234567',
                'vehicle_type' => 'Xe máy',
                'license_number' => str_repeat($i, 8),
                'license_image' => 'license_image.png',
                'identity_image' => 'identity_image.png',
                'experience' => rand(1, 5) . ' năm',
                'status' => 'approved',
                'approved_at' => Carbon::now(),
            ]);

            $addr = $driverAddresses_470418302[$i - 1];
            UserInfo::updateOrCreate(['user_id' => $driverUser->id], [
                'national_id' => '470418302' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'tax_code' => 'TX470418302' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_of_birth' => '1995-01-0' . (($i % 9) + 1),
                'full_address' => $addr['full_address'],
                'address_detail' => 'Khu vực giao hàng chính',
                'latitude' => $addr['latitude'],
                'longitude' => $addr['longitude'],
                'province_code' => null,
                'district_code' => null,
                'ward_code' => null,
            ]);
        }

        // ════════════════════════════════════════════
        // HUB: Bưu điện Kim Liên
        // ID: 902970942 | Hoài Đức | (21.006522, 105.835382)
        // ════════════════════════════════════════════

        $hubUser = User::firstOrCreate(
            ['email' => 'hub_902970942@example.com'],
            [
                'phone' => '024' . str_pad('902970942', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu điện Kim Liên',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '902970942',
                'hub_latitude' => '21.006522',
                'hub_longitude' => '105.835382',
                'hub_address' => 'Phố Lương Định Của',
            ]
        );

        $driverAddresses_902970942 = [
            ['full_address' => 'Phố Lý Thường Kiệt', 'latitude' => 21.025534, 'longitude' => 105.842446],
            ['full_address' => 'cafe', 'latitude' => 21.034426, 'longitude' => 105.853688],
            ['full_address' => 'Phố Giang Văn Minh', 'latitude' => 21.034247, 'longitude' => 105.827219],
        ];

        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_902970942_{$i}@example.com";
            $phone = "092" . substr(str_pad('902970942', 9, '0', STR_PAD_LEFT), -6) . $i;
            $fullName = "Driver Bưu điện Kim Liên #{$i}";

            $driverUser = User::firstOrCreate(['email' => $email], [
                'phone' => $phone,
                'full_name' => $fullName,
                'role' => 'driver',
                'status' => 'active',
                'password_hash' => Hash::make('123456'),
            ]);

            DriverProfile::updateOrCreate(['user_id' => $driverUser->id], [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'province_code' => 1,
                'post_office_id' => '902970942',
                'post_office_name' => 'Bưu điện Kim Liên',
                'post_office_address' => 'Phố Lương Định Của',
                'post_office_lat' => '21.006522',
                'post_office_lng' => '105.835382',
                'post_office_phone' => '0241234567',
                'vehicle_type' => 'Xe máy',
                'license_number' => str_repeat($i, 8),
                'license_image' => 'license_image.png',
                'identity_image' => 'identity_image.png',
                'experience' => rand(1, 5) . ' năm',
                'status' => 'approved',
                'approved_at' => Carbon::now(),
            ]);

            $addr = $driverAddresses_902970942[$i - 1];
            UserInfo::updateOrCreate(['user_id' => $driverUser->id], [
                'national_id' => '902970942' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'tax_code' => 'TX902970942' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_of_birth' => '1995-01-0' . (($i % 9) + 1),
                'full_address' => $addr['full_address'],
                'address_detail' => 'Khu vực giao hàng chính',
                'latitude' => $addr['latitude'],
                'longitude' => $addr['longitude'],
                'province_code' => null,
                'district_code' => null,
                'ward_code' => null,
            ]);
        }

        // ════════════════════════════════════════════
        // HUB: Bưu điện Cửa Nam
        // ID: 035229916 | Hoài Đức | (21.02789, 105.842421)
        // ════════════════════════════════════════════

        $hubUser = User::firstOrCreate(
            ['email' => 'hub_035229916@example.com'],
            [
                'phone' => '024' . str_pad('035229916', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu điện Cửa Nam',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '035229916',
                'hub_latitude' => '21.02789',
                'hub_longitude' => '105.842421',
                'hub_address' => 'Phố Cửa Nam',
            ]
        );

        $driverAddresses_035229916 = [
            ['full_address' => 'Phố Huế', 'latitude' => 21.015894, 'longitude' => 105.851316],
            ['full_address' => 'Nhà Hàng New Sake 1', 'latitude' => 21.030786, 'longitude' => 105.819421],
            ['full_address' => 'Phở 24', 'latitude' => 21.017854, 'longitude' => 105.79609],
        ];

        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_035229916_{$i}@example.com";
            $phone = "092" . substr(str_pad('035229916', 9, '0', STR_PAD_LEFT), -6) . $i;
            $fullName = "Driver Bưu điện Cửa Nam #{$i}";

            $driverUser = User::firstOrCreate(['email' => $email], [
                'phone' => $phone,
                'full_name' => $fullName,
                'role' => 'driver',
                'status' => 'active',
                'password_hash' => Hash::make('123456'),
            ]);

            DriverProfile::updateOrCreate(['user_id' => $driverUser->id], [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'province_code' => 1,
                'post_office_id' => '035229916',
                'post_office_name' => 'Bưu điện Cửa Nam',
                'post_office_address' => 'Phố Cửa Nam',
                'post_office_lat' => '21.02789',
                'post_office_lng' => '105.842421',
                'post_office_phone' => '0241234567',
                'vehicle_type' => 'Xe máy',
                'license_number' => str_repeat($i, 8),
                'license_image' => 'license_image.png',
                'identity_image' => 'identity_image.png',
                'experience' => rand(1, 5) . ' năm',
                'status' => 'approved',
                'approved_at' => Carbon::now(),
            ]);

            $addr = $driverAddresses_035229916[$i - 1];
            UserInfo::updateOrCreate(['user_id' => $driverUser->id], [
                'national_id' => '035229916' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'tax_code' => 'TX035229916' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_of_birth' => '1995-01-0' . (($i % 9) + 1),
                'full_address' => $addr['full_address'],
                'address_detail' => 'Khu vực giao hàng chính',
                'latitude' => $addr['latitude'],
                'longitude' => $addr['longitude'],
                'province_code' => null,
                'district_code' => null,
                'ward_code' => null,
            ]);
        }

        // ════════════════════════════════════════════
        // HUB: Bưu Cục Vân Hồ
        // ID: 535806772 | Hoài Đức | (21.010339, 105.846454)
        // ════════════════════════════════════════════

        $hubUser = User::firstOrCreate(
            ['email' => 'hub_535806772@example.com'],
            [
                'phone' => '024' . str_pad('535806772', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu Cục Vân Hồ',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '535806772',
                'hub_latitude' => '21.010339',
                'hub_longitude' => '105.846454',
                'hub_address' => 'Phố Vân Hồ 3',
            ]
        );

        $driverAddresses_535806772 = [
            ['full_address' => 'Đường Nguyễn Văn Cừ', 'latitude' => 21.044858, 'longitude' => 105.874375],
            ['full_address' => 'Highlands Coffee', 'latitude' => 21.023301, 'longitude' => 105.809692],
            ['full_address' => 'Phố Hàng Chĩnh', 'latitude' => 21.035498, 'longitude' => 105.853708],
        ];

        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_535806772_{$i}@example.com";
            $phone = "092" . substr(str_pad('535806772', 9, '0', STR_PAD_LEFT), -6) . $i;
            $fullName = "Driver Bưu Cục Vân Hồ #{$i}";

            $driverUser = User::firstOrCreate(['email' => $email], [
                'phone' => $phone,
                'full_name' => $fullName,
                'role' => 'driver',
                'status' => 'active',
                'password_hash' => Hash::make('123456'),
            ]);

            DriverProfile::updateOrCreate(['user_id' => $driverUser->id], [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'province_code' => 1,
                'post_office_id' => '535806772',
                'post_office_name' => 'Bưu Cục Vân Hồ',
                'post_office_address' => 'Phố Vân Hồ 3',
                'post_office_lat' => '21.010339',
                'post_office_lng' => '105.846454',
                'post_office_phone' => '0241234567',
                'vehicle_type' => 'Xe máy',
                'license_number' => str_repeat($i, 8),
                'license_image' => 'license_image.png',
                'identity_image' => 'identity_image.png',
                'experience' => rand(1, 5) . ' năm',
                'status' => 'approved',
                'approved_at' => Carbon::now(),
            ]);

            $addr = $driverAddresses_535806772[$i - 1];
            UserInfo::updateOrCreate(['user_id' => $driverUser->id], [
                'national_id' => '535806772' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'tax_code' => 'TX535806772' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_of_birth' => '1995-01-0' . (($i % 9) + 1),
                'full_address' => $addr['full_address'],
                'address_detail' => 'Khu vực giao hàng chính',
                'latitude' => $addr['latitude'],
                'longitude' => $addr['longitude'],
                'province_code' => null,
                'district_code' => null,
                'ward_code' => null,
            ]);
        }

        // ════════════════════════════════════════════
        // HUB: Bưu cục Bách Khoa
        // ID: 119329539 | Hoài Đức | (21.003708, 105.847806)
        // ════════════════════════════════════════════

        $hubUser = User::firstOrCreate(
            ['email' => 'hub_119329539@example.com'],
            [
                'phone' => '024' . str_pad('119329539', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu cục Bách Khoa',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '119329539',
                'hub_latitude' => '21.003708',
                'hub_longitude' => '105.847806',
                'hub_address' => 'Phố Tạ Quang Bửu',
            ]
        );

        $driverAddresses_119329539 = [
            ['full_address' => 'Phố Lý Nam Đế', 'latitude' => 21.036383, 'longitude' => 105.845194],
            ['full_address' => 'Phố Nguyễn Hữu Thọ', 'latitude' => 20.968182, 'longitude' => 105.827168],
            ['full_address' => 'Phố Lý Nam Đế', 'latitude' => 21.032112, 'longitude' => 105.844302],
        ];

        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_119329539_{$i}@example.com";
            $phone = "092" . substr(str_pad('119329539', 9, '0', STR_PAD_LEFT), -6) . $i;
            $fullName = "Driver Bưu cục Bách Khoa #{$i}";

            $driverUser = User::firstOrCreate(['email' => $email], [
                'phone' => $phone,
                'full_name' => $fullName,
                'role' => 'driver',
                'status' => 'active',
                'password_hash' => Hash::make('123456'),
            ]);

            DriverProfile::updateOrCreate(['user_id' => $driverUser->id], [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'province_code' => 1,
                'post_office_id' => '119329539',
                'post_office_name' => 'Bưu cục Bách Khoa',
                'post_office_address' => 'Phố Tạ Quang Bửu',
                'post_office_lat' => '21.003708',
                'post_office_lng' => '105.847806',
                'post_office_phone' => '0241234567',
                'vehicle_type' => 'Xe máy',
                'license_number' => str_repeat($i, 8),
                'license_image' => 'license_image.png',
                'identity_image' => 'identity_image.png',
                'experience' => rand(1, 5) . ' năm',
                'status' => 'approved',
                'approved_at' => Carbon::now(),
            ]);

            $addr = $driverAddresses_119329539[$i - 1];
            UserInfo::updateOrCreate(['user_id' => $driverUser->id], [
                'national_id' => '119329539' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'tax_code' => 'TX119329539' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_of_birth' => '1995-01-0' . (($i % 9) + 1),
                'full_address' => $addr['full_address'],
                'address_detail' => 'Khu vực giao hàng chính',
                'latitude' => $addr['latitude'],
                'longitude' => $addr['longitude'],
                'province_code' => null,
                'district_code' => null,
                'ward_code' => null,
            ]);
        }

        // ════════════════════════════════════════════
        // HUB: Bưu điện Đống Đa
        // ID: 232400947 | Hoài Đức | (21.007116, 105.821162)
        // ════════════════════════════════════════════

        $hubUser = User::firstOrCreate(
            ['email' => 'hub_232400947@example.com'],
            [
                'phone' => '024' . str_pad('232400947', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu điện Đống Đa',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '232400947',
                'hub_latitude' => '21.007116',
                'hub_longitude' => '105.821162',
                'hub_address' => 'Phố Thái Thịnh',
            ]
        );

        $driverAddresses_232400947 = [
            ['full_address' => 'Đào Duy Từ', 'latitude' => 21.035883, 'longitude' => 105.853219],
            ['full_address' => 'Đường Trương Định', 'latitude' => 20.994106, 'longitude' => 105.84963],
            ['full_address' => 'Cà Phê & Bánh Mì Mạnh', 'latitude' => 21.031501, 'longitude' => 105.848422],
        ];

        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_232400947_{$i}@example.com";
            $phone = "092" . substr(str_pad('232400947', 9, '0', STR_PAD_LEFT), -6) . $i;
            $fullName = "Driver Bưu điện Đống Đa #{$i}";

            $driverUser = User::firstOrCreate(['email' => $email], [
                'phone' => $phone,
                'full_name' => $fullName,
                'role' => 'driver',
                'status' => 'active',
                'password_hash' => Hash::make('123456'),
            ]);

            DriverProfile::updateOrCreate(['user_id' => $driverUser->id], [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'province_code' => 1,
                'post_office_id' => '232400947',
                'post_office_name' => 'Bưu điện Đống Đa',
                'post_office_address' => 'Phố Thái Thịnh',
                'post_office_lat' => '21.007116',
                'post_office_lng' => '105.821162',
                'post_office_phone' => '0241234567',
                'vehicle_type' => 'Xe máy',
                'license_number' => str_repeat($i, 8),
                'license_image' => 'license_image.png',
                'identity_image' => 'identity_image.png',
                'experience' => rand(1, 5) . ' năm',
                'status' => 'approved',
                'approved_at' => Carbon::now(),
            ]);

            $addr = $driverAddresses_232400947[$i - 1];
            UserInfo::updateOrCreate(['user_id' => $driverUser->id], [
                'national_id' => '232400947' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'tax_code' => 'TX232400947' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_of_birth' => '1995-01-0' . (($i % 9) + 1),
                'full_address' => $addr['full_address'],
                'address_detail' => 'Khu vực giao hàng chính',
                'latitude' => $addr['latitude'],
                'longitude' => $addr['longitude'],
                'province_code' => null,
                'district_code' => null,
                'ward_code' => null,
            ]);
        }

        // ════════════════════════════════════════════
        // HUB: Bưu Cục EMS Cát Linh
        // ID: 549491928 | Hoài Đức | (21.028465, 105.83197)
        // ════════════════════════════════════════════

        $hubUser = User::firstOrCreate(
            ['email' => 'hub_549491928@example.com'],
            [
                'phone' => '024' . str_pad('549491928', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu Cục EMS Cát Linh',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '549491928',
                'hub_latitude' => '21.028465',
                'hub_longitude' => '105.83197',
                'hub_address' => 'Phố Cát Linh',
            ]
        );

        $driverAddresses_549491928 = [
           ['full_address' => 'Nhà Hàng Al Fresco\'s', 'latitude' => 21.009214, 'longitude' => 105.802142],
            ['full_address' => 'restaurant', 'latitude' => 21.030079, 'longitude' => 105.844487],
            ['full_address' => 'Phõ Láng Hạ', 'latitude' => 21.012417, 'longitude' => 105.812469],
        ];

        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_549491928_{$i}@example.com";
            $phone = "092" . substr(str_pad('549491928', 9, '0', STR_PAD_LEFT), -6) . $i;
            $fullName = "Driver Bưu Cục EMS Cát Linh #{$i}";

            $driverUser = User::firstOrCreate(['email' => $email], [
                'phone' => $phone,
                'full_name' => $fullName,
                'role' => 'driver',
                'status' => 'active',
                'password_hash' => Hash::make('123456'),
            ]);

            DriverProfile::updateOrCreate(['user_id' => $driverUser->id], [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'province_code' => 1,
                'post_office_id' => '549491928',
                'post_office_name' => 'Bưu Cục EMS Cát Linh',
                'post_office_address' => 'Phố Cát Linh',
                'post_office_lat' => '21.028465',
                'post_office_lng' => '105.83197',
                'post_office_phone' => '0241234567',
                'vehicle_type' => 'Xe máy',
                'license_number' => str_repeat($i, 8),
                'license_image' => 'license_image.png',
                'identity_image' => 'identity_image.png',
                'experience' => rand(1, 5) . ' năm',
                'status' => 'approved',
                'approved_at' => Carbon::now(),
            ]);

            $addr = $driverAddresses_549491928[$i - 1];
            UserInfo::updateOrCreate(['user_id' => $driverUser->id], [
                'national_id' => '549491928' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'tax_code' => 'TX549491928' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_of_birth' => '1995-01-0' . (($i % 9) + 1),
                'full_address' => $addr['full_address'],
                'address_detail' => 'Khu vực giao hàng chính',
                'latitude' => $addr['latitude'],
                'longitude' => $addr['longitude'],
                'province_code' => null,
                'district_code' => null,
                'ward_code' => null,
            ]);
        }

        // ════════════════════════════════════════════
        // HUB: Viettel Post
        // ID: 8361003166 | Hoài Đức | (21.088888, 105.785467)
        // ════════════════════════════════════════════

        $hubUser = User::firstOrCreate(
            ['email' => 'hub_8361003166@example.com'],
            [
                'phone' => '024' . str_pad('8361003166', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Viettel Post',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '8361003166',
                'hub_latitude' => '21.088888',
                'hub_longitude' => '105.785467',
                'hub_address' => 'Hoài Đức, Hà Nội',
            ]
        );

        $driverAddresses_8361003166 = [
            ['full_address' => 'Phố Đặng Thùy Trâm', 'latitude' => 21.044259, 'longitude' => 105.785198],
            ['full_address' => 'Ngõ 445 Lạc Long Quân', 'latitude' => 21.058708, 'longitude' => 105.807793],
            ['full_address' => 'trà sữa HOUJICHA', 'latitude' => 21.074803, 'longitude' => 105.773087],
        ];

        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_8361003166_{$i}@example.com";
            $phone = "092" . substr(str_pad('8361003166', 9, '0', STR_PAD_LEFT), -6) . $i;
            $fullName = "Driver Viettel Post #{$i}";

            $driverUser = User::firstOrCreate(['email' => $email], [
                'phone' => $phone,
                'full_name' => $fullName,
                'role' => 'driver',
                'status' => 'active',
                'password_hash' => Hash::make('123456'),
            ]);

            DriverProfile::updateOrCreate(['user_id' => $driverUser->id], [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'province_code' => 1,
                'post_office_id' => '8361003166',
                'post_office_name' => 'Viettel Post',
                'post_office_address' => 'Hoài Đức, Hà Nội',
                'post_office_lat' => '21.088888',
                'post_office_lng' => '105.785467',
                'post_office_phone' => '0241234567',
                'vehicle_type' => 'Xe máy',
                'license_number' => str_repeat($i, 8),
                'license_image' => 'license_image.png',
                'identity_image' => 'identity_image.png',
                'experience' => rand(1, 5) . ' năm',
                'status' => 'approved',
                'approved_at' => Carbon::now(),
            ]);

            $addr = $driverAddresses_8361003166[$i - 1];
            UserInfo::updateOrCreate(['user_id' => $driverUser->id], [
                'national_id' => '8361003166' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'tax_code' => 'TX8361003166' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_of_birth' => '1995-01-0' . (($i % 9) + 1),
                'full_address' => $addr['full_address'],
                'address_detail' => 'Khu vực giao hàng chính',
                'latitude' => $addr['latitude'],
                'longitude' => $addr['longitude'],
                'province_code' => null,
                'district_code' => null,
                'ward_code' => null,
            ]);
        }

        // ════════════════════════════════════════════
        // HUB: Bưu cục Hoài Đức
        // ID: 8363303883 | Hoài Đức | (21.069615, 105.777946)
        // ════════════════════════════════════════════

        $hubUser = User::firstOrCreate(
            ['email' => 'hub_8363303883@example.com'],
            [
                'phone' => '024' . str_pad('8363303883', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu cục Hoài Đức',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '8363303883',
                'hub_latitude' => '21.069615',
                'hub_longitude' => '105.777946',
                'hub_address' => 'Đường Cổ Nhuế',
            ]
        );

        $driverAddresses_8363303883 = [
            ['full_address' => 'restaurant', 'latitude' => 21.040387, 'longitude' => 105.782065],
            ['full_address' => 'Phố Trần Thái Tông', 'latitude' => 21.035081, 'longitude' => 105.789419],
            ['full_address' => 'Bánh cuốn Bà Khê', 'latitude' => 21.040504, 'longitude' => 105.784364],
        ];

        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_8363303883_{$i}@example.com";
            $phone = "092" . substr(str_pad('8363303883', 9, '0', STR_PAD_LEFT), -6) . $i;
            $fullName = "Driver Bưu cục Hoài Đức #{$i}";

            $driverUser = User::firstOrCreate(['email' => $email], [
                'phone' => $phone,
                'full_name' => $fullName,
                'role' => 'driver',
                'status' => 'active',
                'password_hash' => Hash::make('123456'),
            ]);

            DriverProfile::updateOrCreate(['user_id' => $driverUser->id], [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'province_code' => 1,
                'post_office_id' => '8363303883',
                'post_office_name' => 'Bưu cục Hoài Đức',
                'post_office_address' => 'Đường Cổ Nhuế',
                'post_office_lat' => '21.069615',
                'post_office_lng' => '105.777946',
                'post_office_phone' => '0241234567',
                'vehicle_type' => 'Xe máy',
                'license_number' => str_repeat($i, 8),
                'license_image' => 'license_image.png',
                'identity_image' => 'identity_image.png',
                'experience' => rand(1, 5) . ' năm',
                'status' => 'approved',
                'approved_at' => Carbon::now(),
            ]);

            $addr = $driverAddresses_8363303883[$i - 1];
            UserInfo::updateOrCreate(['user_id' => $driverUser->id], [
                'national_id' => '8363303883' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'tax_code' => 'TX8363303883' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_of_birth' => '1995-01-0' . (($i % 9) + 1),
                'full_address' => $addr['full_address'],
                'address_detail' => 'Khu vực giao hàng chính',
                'latitude' => $addr['latitude'],
                'longitude' => $addr['longitude'],
                'province_code' => null,
                'district_code' => null,
                'ward_code' => null,
            ]);
        }

        // ════════════════════════════════════════════
        // HUB: Bưu điện Láng Trung
        // ID: 733813231 | Hoài Đức | (21.019007, 105.808138)
        // ════════════════════════════════════════════

        $hubUser = User::firstOrCreate(
            ['email' => 'hub_733813231@example.com'],
            [
                'phone' => '024' . str_pad('733813231', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu điện Láng Trung',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '733813231',
                'hub_latitude' => '21.019007',
                'hub_longitude' => '105.808138',
                'hub_address' => 'Đường Nguyễn Chí Thanh',
            ]
        );

        $driverAddresses_733813231 = [
            ['full_address' => 'Phố Yên Phụ', 'latitude' => 21.052781, 'longitude' => 105.837248],
            ['full_address' => 'Nguyễn Phong Sắc', 'latitude' => 21.038585, 'longitude' => 105.790647],
            ['full_address' => 'Phố Bà Triệu', 'latitude' => 21.023641, 'longitude' => 105.850856],
        ];

        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_733813231_{$i}@example.com";
            $phone = "092" . substr(str_pad('733813231', 9, '0', STR_PAD_LEFT), -6) . $i;
            $fullName = "Driver Bưu điện Láng Trung #{$i}";

            $driverUser = User::firstOrCreate(['email' => $email], [
                'phone' => $phone,
                'full_name' => $fullName,
                'role' => 'driver',
                'status' => 'active',
                'password_hash' => Hash::make('123456'),
            ]);

            DriverProfile::updateOrCreate(['user_id' => $driverUser->id], [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'province_code' => 1,
                'post_office_id' => '733813231',
                'post_office_name' => 'Bưu điện Láng Trung',
                'post_office_address' => 'Đường Nguyễn Chí Thanh',
                'post_office_lat' => '21.019007',
                'post_office_lng' => '105.808138',
                'post_office_phone' => '0241234567',
                'vehicle_type' => 'Xe máy',
                'license_number' => str_repeat($i, 8),
                'license_image' => 'license_image.png',
                'identity_image' => 'identity_image.png',
                'experience' => rand(1, 5) . ' năm',
                'status' => 'approved',
                'approved_at' => Carbon::now(),
            ]);

            $addr = $driverAddresses_733813231[$i - 1];
            UserInfo::updateOrCreate(['user_id' => $driverUser->id], [
                'national_id' => '733813231' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'tax_code' => 'TX733813231' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_of_birth' => '1995-01-0' . (($i % 9) + 1),
                'full_address' => $addr['full_address'],
                'address_detail' => 'Khu vực giao hàng chính',
                'latitude' => $addr['latitude'],
                'longitude' => $addr['longitude'],
                'province_code' => null,
                'district_code' => null,
                'ward_code' => null,
            ]);
        }

        // ════════════════════════════════════════════
        // HUB: Bưu Điện Cống Vị
        // ID: 428018813 | Hoài Đức | (21.035236, 105.820054)
        // ════════════════════════════════════════════

        $hubUser = User::firstOrCreate(
            ['email' => 'hub_428018813@example.com'],
            [
                'phone' => '024' . str_pad('428018813', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu Điện Cống Vị',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '428018813',
                'hub_latitude' => '21.035236',
                'hub_longitude' => '105.820054',
                'hub_address' => 'Phố Đội Cấn',
            ]
        );

        $driverAddresses_428018813 = [
            ['full_address' => 'Phố Xã Đàn', 'latitude' => 21.011744, 'longitude' => 105.836894],
            ['full_address' => 'Phố Vũ Phạm Hàm', 'latitude' => 21.017713, 'longitude' => 105.798038],
            ['full_address' => 'Phô Vū Miên', 'latitude' => 21.05385, 'longitude' => 105.833941],
        ];

        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_428018813_{$i}@example.com";
            $phone = "092" . substr(str_pad('428018813', 9, '0', STR_PAD_LEFT), -6) . $i;
            $fullName = "Driver Bưu Điện Cống Vị #{$i}";

            $driverUser = User::firstOrCreate(['email' => $email], [
                'phone' => $phone,
                'full_name' => $fullName,
                'role' => 'driver',
                'status' => 'active',
                'password_hash' => Hash::make('123456'),
            ]);

            DriverProfile::updateOrCreate(['user_id' => $driverUser->id], [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'province_code' => 1,
                'post_office_id' => '428018813',
                'post_office_name' => 'Bưu Điện Cống Vị',
                'post_office_address' => 'Phố Đội Cấn',
                'post_office_lat' => '21.035236',
                'post_office_lng' => '105.820054',
                'post_office_phone' => '0241234567',
                'vehicle_type' => 'Xe máy',
                'license_number' => str_repeat($i, 8),
                'license_image' => 'license_image.png',
                'identity_image' => 'identity_image.png',
                'experience' => rand(1, 5) . ' năm',
                'status' => 'approved',
                'approved_at' => Carbon::now(),
            ]);

            $addr = $driverAddresses_428018813[$i - 1];
            UserInfo::updateOrCreate(['user_id' => $driverUser->id], [
                'national_id' => '428018813' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'tax_code' => 'TX428018813' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_of_birth' => '1995-01-0' . (($i % 9) + 1),
                'full_address' => $addr['full_address'],
                'address_detail' => 'Khu vực giao hàng chính',
                'latitude' => $addr['latitude'],
                'longitude' => $addr['longitude'],
                'province_code' => null,
                'district_code' => null,
                'ward_code' => null,
            ]);
        }

        // ════════════════════════════════════════════
        // HUB: Bưu Điện Đốc Ngữ
        // ID: 9420364736 | Hoài Đức | (21.037755, 105.810609)
        // ════════════════════════════════════════════

        $hubUser = User::firstOrCreate(
            ['email' => 'hub_9420364736@example.com'],
            [
                'phone' => '024' . str_pad('9420364736', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu Điện Đốc Ngữ',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '9420364736',
                'hub_latitude' => '21.037755',
                'hub_longitude' => '105.810609',
                'hub_address' => 'Hoài Đức, Hà Nội',
            ]
        );

        $driverAddresses_9420364736 = [
            ['full_address' => 'Phố Nguyễn Thị Định', 'latitude' => 21.01317, 'longitude' => 105.804341],
            ['full_address' => 'Đường Âu Cơ', 'latitude' => 21.079464, 'longitude' => 105.819008],
            ['full_address' => 'Trà sữa Lofi', 'latitude' => 21.070395, 'longitude' => 105.777649],
        ];

        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_9420364736_{$i}@example.com";
            $phone = "092" . substr(str_pad('9420364736', 9, '0', STR_PAD_LEFT), -6) . $i;
            $fullName = "Driver Bưu Điện Đốc Ngữ #{$i}";

            $driverUser = User::firstOrCreate(['email' => $email], [
                'phone' => $phone,
                'full_name' => $fullName,
                'role' => 'driver',
                'status' => 'active',
                'password_hash' => Hash::make('123456'),
            ]);

            DriverProfile::updateOrCreate(['user_id' => $driverUser->id], [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'province_code' => 1,
                'post_office_id' => '9420364736',
                'post_office_name' => 'Bưu Điện Đốc Ngữ',
                'post_office_address' => 'Hoài Đức, Hà Nội',
                'post_office_lat' => '21.037755',
                'post_office_lng' => '105.810609',
                'post_office_phone' => '0241234567',
                'vehicle_type' => 'Xe máy',
                'license_number' => str_repeat($i, 8),
                'license_image' => 'license_image.png',
                'identity_image' => 'identity_image.png',
                'experience' => rand(1, 5) . ' năm',
                'status' => 'approved',
                'approved_at' => Carbon::now(),
            ]);

            $addr = $driverAddresses_9420364736[$i - 1];
            UserInfo::updateOrCreate(['user_id' => $driverUser->id], [
                'national_id' => '9420364736' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'tax_code' => 'TX9420364736' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_of_birth' => '1995-01-0' . (($i % 9) + 1),
                'full_address' => $addr['full_address'],
                'address_detail' => 'Khu vực giao hàng chính',
                'latitude' => $addr['latitude'],
                'longitude' => $addr['longitude'],
                'province_code' => null,
                'district_code' => null,
                'ward_code' => null,
            ]);
        }

        // ════════════════════════════════════════════
        // HUB: Bưu cục Đặng Tiến Đông
        // ID: 179384911 | Hoài Đức | (21.011343, 105.824441)
        // ════════════════════════════════════════════

        $hubUser = User::firstOrCreate(
            ['email' => 'hub_179384911@example.com'],
            [
                'phone' => '024' . str_pad('179384911', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu cục Đặng Tiến Đông',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '179384911',
                'hub_latitude' => '21.011343',
                'hub_longitude' => '105.824441',
                'hub_address' => 'Phố Đặng Tiến Đông',
            ]
        );

        $driverAddresses_179384911 = [
            ['full_address' => 'Cộng Cà Phê', 'latitude' => 21.004321, 'longitude' => 105.81848],
            ['full_address' => 'Dang Thai Mai', 'latitude' => 21.055179, 'longitude' => 105.82136],
            ['full_address' => 'Quan Ngon nha Ham', 'latitude' => 21.025736, 'longitude' => 105.85219],
        ];

        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_179384911_{$i}@example.com";
            $phone = "092" . substr(str_pad('179384911', 9, '0', STR_PAD_LEFT), -6) . $i;
            $fullName = "Driver Bưu cục Đặng Tiến Đông #{$i}";

            $driverUser = User::firstOrCreate(['email' => $email], [
                'phone' => $phone,
                'full_name' => $fullName,
                'role' => 'driver',
                'status' => 'active',
                'password_hash' => Hash::make('123456'),
            ]);

            DriverProfile::updateOrCreate(['user_id' => $driverUser->id], [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'province_code' => 1,
                'post_office_id' => '179384911',
                'post_office_name' => 'Bưu cục Đặng Tiến Đông',
                'post_office_address' => 'Phố Đặng Tiến Đông',
                'post_office_lat' => '21.011343',
                'post_office_lng' => '105.824441',
                'post_office_phone' => '0241234567',
                'vehicle_type' => 'Xe máy',
                'license_number' => str_repeat($i, 8),
                'license_image' => 'license_image.png',
                'identity_image' => 'identity_image.png',
                'experience' => rand(1, 5) . ' năm',
                'status' => 'approved',
                'approved_at' => Carbon::now(),
            ]);

            $addr = $driverAddresses_179384911[$i - 1];
            UserInfo::updateOrCreate(['user_id' => $driverUser->id], [
                'national_id' => '179384911' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'tax_code' => 'TX179384911' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_of_birth' => '1995-01-0' . (($i % 9) + 1),
                'full_address' => $addr['full_address'],
                'address_detail' => 'Khu vực giao hàng chính',
                'latitude' => $addr['latitude'],
                'longitude' => $addr['longitude'],
                'province_code' => null,
                'district_code' => null,
                'ward_code' => null,
            ]);
        }

        // ════════════════════════════════════════════
        // HUB: Bưu Điện Hoàng Quốc Việt
        // ID: 309893181 | Hoài Đức | (21.04587, 105.79117)
        // ════════════════════════════════════════════

        $hubUser = User::firstOrCreate(
            ['email' => 'hub_309893181@example.com'],
            [
                'phone' => '024' . str_pad('309893181', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu Điện Hoàng Quốc Việt',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '309893181',
                'hub_latitude' => '21.04587',
                'hub_longitude' => '105.79117',
                'hub_address' => 'Đường Hoàng Quốc Việt',
            ]
        );

        $driverAddresses_309893181 = [
            ['full_address' => 'Mộc trà quán', 'latitude' => 21.07155, 'longitude' => 105.772116],
            ['full_address' => 'Hanoi Metro Cafe & Canteen', 'latitude' => 21.027754, 'longitude' => 105.827323],
            ['full_address' => 'Duy Tân', 'latitude' => 21.030511, 'longitude' => 105.784811],
        ];

        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_309893181_{$i}@example.com";
            $phone = "092" . substr(str_pad('309893181', 9, '0', STR_PAD_LEFT), -6) . $i;
            $fullName = "Driver Bưu Điện Hoàng Quốc Việt #{$i}";

            $driverUser = User::firstOrCreate(['email' => $email], [
                'phone' => $phone,
                'full_name' => $fullName,
                'role' => 'driver',
                'status' => 'active',
                'password_hash' => Hash::make('123456'),
            ]);

            DriverProfile::updateOrCreate(['user_id' => $driverUser->id], [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'province_code' => 1,
                'post_office_id' => '309893181',
                'post_office_name' => 'Bưu Điện Hoàng Quốc Việt',
                'post_office_address' => 'Đường Hoàng Quốc Việt',
                'post_office_lat' => '21.04587',
                'post_office_lng' => '105.79117',
                'post_office_phone' => '0241234567',
                'vehicle_type' => 'Xe máy',
                'license_number' => str_repeat($i, 8),
                'license_image' => 'license_image.png',
                'identity_image' => 'identity_image.png',
                'experience' => rand(1, 5) . ' năm',
                'status' => 'approved',
                'approved_at' => Carbon::now(),
            ]);

            $addr = $driverAddresses_309893181[$i - 1];
            UserInfo::updateOrCreate(['user_id' => $driverUser->id], [
                'national_id' => '309893181' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'tax_code' => 'TX309893181' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_of_birth' => '1995-01-0' . (($i % 9) + 1),
                'full_address' => $addr['full_address'],
                'address_detail' => 'Khu vực giao hàng chính',
                'latitude' => $addr['latitude'],
                'longitude' => $addr['longitude'],
                'province_code' => null,
                'district_code' => null,
                'ward_code' => null,
            ]);
        }

        // ════════════════════════════════════════════
        // HUB: Bưu điện Nghĩa Tân
        // ID: 9420492210 | Hoài Đức | (21.044245, 105.79452)
        // ════════════════════════════════════════════

        $hubUser = User::firstOrCreate(
            ['email' => 'hub_9420492210@example.com'],
            [
                'phone' => '024' . str_pad('9420492210', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu điện Nghĩa Tân',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '9420492210',
                'hub_latitude' => '21.044245',
                'hub_longitude' => '105.79452',
                'hub_address' => 'Hoài Đức, Hà Nội',
            ]
        );

        $driverAddresses_9420492210 = [
            ['full_address' => 'Phố Trích Sài', 'latitude' => 21.055595, 'longitude' => 105.810714],
            ['full_address' => 'AHA Cafe', 'latitude' => 21.034792, 'longitude' => 105.811193],
            ['full_address' => 'cafe', 'latitude' => 21.05704, 'longitude' => 105.78411],
        ];

        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_9420492210_{$i}@example.com";
            $phone = "092" . substr(str_pad('9420492210', 9, '0', STR_PAD_LEFT), -6) . $i;
            $fullName = "Driver Bưu điện Nghĩa Tân #{$i}";

            $driverUser = User::firstOrCreate(['email' => $email], [
                'phone' => $phone,
                'full_name' => $fullName,
                'role' => 'driver',
                'status' => 'active',
                'password_hash' => Hash::make('123456'),
            ]);

            DriverProfile::updateOrCreate(['user_id' => $driverUser->id], [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'province_code' => 1,
                'post_office_id' => '9420492210',
                'post_office_name' => 'Bưu điện Nghĩa Tân',
                'post_office_address' => 'Hoài Đức, Hà Nội',
                'post_office_lat' => '21.044245',
                'post_office_lng' => '105.79452',
                'post_office_phone' => '0241234567',
                'vehicle_type' => 'Xe máy',
                'license_number' => str_repeat($i, 8),
                'license_image' => 'license_image.png',
                'identity_image' => 'identity_image.png',
                'experience' => rand(1, 5) . ' năm',
                'status' => 'approved',
                'approved_at' => Carbon::now(),
            ]);

            $addr = $driverAddresses_9420492210[$i - 1];
            UserInfo::updateOrCreate(['user_id' => $driverUser->id], [
                'national_id' => '9420492210' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'tax_code' => 'TX9420492210' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_of_birth' => '1995-01-0' . (($i % 9) + 1),
                'full_address' => $addr['full_address'],
                'address_detail' => 'Khu vực giao hàng chính',
                'latitude' => $addr['latitude'],
                'longitude' => $addr['longitude'],
                'province_code' => null,
                'district_code' => null,
                'ward_code' => null,
            ]);
        }

        // ════════════════════════════════════════════
        // HUB: Bưu cục Hoài Đức
        // ID: 9420492211 | Hoài Đức | (21.045596, 105.79928)
        // ════════════════════════════════════════════

        $hubUser = User::firstOrCreate(
            ['email' => 'hub_9420492211@example.com'],
            [
                'phone' => '024' . str_pad('9420492211', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu cục Hoài Đức',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '9420492211',
                'hub_latitude' => '21.045596',
                'hub_longitude' => '105.79928',
                'hub_address' => 'Phố Hoàng Sâm',
            ]
        );

        $driverAddresses_9420492211 = [
            ['full_address' => 'Đường Hoàng Hoa Thám', 'latitude' => 21.041002, 'longitude' => 105.824162],
            ['full_address' => 'Cửa Hàng Bánh Tôm Hồ Tây', 'latitude' => 21.047321, 'longitude' => 105.837626],
            ['full_address' => 'Wrap and Roll', 'latitude' => 21.026401, 'longitude' => 105.822724],
        ];

        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_9420492211_{$i}@example.com";
            $phone = "092" . substr(str_pad('9420492211', 9, '0', STR_PAD_LEFT), -6) . $i;
            $fullName = "Driver Bưu cục Hoài Đức #{$i}";

            $driverUser = User::firstOrCreate(['email' => $email], [
                'phone' => $phone,
                'full_name' => $fullName,
                'role' => 'driver',
                'status' => 'active',
                'password_hash' => Hash::make('123456'),
            ]);

            DriverProfile::updateOrCreate(['user_id' => $driverUser->id], [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'province_code' => 1,
                'post_office_id' => '9420492211',
                'post_office_name' => 'Bưu cục Hoài Đức',
                'post_office_address' => 'Phố Hoàng Sâm',
                'post_office_lat' => '21.045596',
                'post_office_lng' => '105.79928',
                'post_office_phone' => '0241234567',
                'vehicle_type' => 'Xe máy',
                'license_number' => str_repeat($i, 8),
                'license_image' => 'license_image.png',
                'identity_image' => 'identity_image.png',
                'experience' => rand(1, 5) . ' năm',
                'status' => 'approved',
                'approved_at' => Carbon::now(),
            ]);

            $addr = $driverAddresses_9420492211[$i - 1];
            UserInfo::updateOrCreate(['user_id' => $driverUser->id], [
                'national_id' => '9420492211' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'tax_code' => 'TX9420492211' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_of_birth' => '1995-01-0' . (($i % 9) + 1),
                'full_address' => $addr['full_address'],
                'address_detail' => 'Khu vực giao hàng chính',
                'latitude' => $addr['latitude'],
                'longitude' => $addr['longitude'],
                'province_code' => null,
                'district_code' => null,
                'ward_code' => null,
            ]);
        }

        // ════════════════════════════════════════════
        // HUB: Bưu Điện Yên Phụ
        // ID: 807188085 | Hoài Đức | (21.051152, 105.838953)
        // ════════════════════════════════════════════

        $hubUser = User::firstOrCreate(
            ['email' => 'hub_807188085@example.com'],
            [
                'phone' => '024' . str_pad('807188085', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu Điện Yên Phụ',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '807188085',
                'hub_latitude' => '21.051152',
                'hub_longitude' => '105.838953',
                'hub_address' => 'Phố Yên Phụ',
            ]
        );

        $driverAddresses_807188085 = [
            ['full_address' => 'Hội Kiến Trúc Sư Việt Nam', 'latitude' => 21.01617, 'longitude' => 105.858451],
            ['full_address' => 'PVcomBank', 'latitude' => 21.028273, 'longitude' => 105.842953],
            ['full_address' => 'Đường Lạc Long Quân', 'latitude' => 21.07366, 'longitude' => 105.812744],
        ];

        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_807188085_{$i}@example.com";
            $phone = "092" . substr(str_pad('807188085', 9, '0', STR_PAD_LEFT), -6) . $i;
            $fullName = "Driver Bưu Điện Yên Phụ #{$i}";

            $driverUser = User::firstOrCreate(['email' => $email], [
                'phone' => $phone,
                'full_name' => $fullName,
                'role' => 'driver',
                'status' => 'active',
                'password_hash' => Hash::make('123456'),
            ]);

            DriverProfile::updateOrCreate(['user_id' => $driverUser->id], [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'province_code' => 1,
                'post_office_id' => '807188085',
                'post_office_name' => 'Bưu Điện Yên Phụ',
                'post_office_address' => 'Phố Yên Phụ',
                'post_office_lat' => '21.051152',
                'post_office_lng' => '105.838953',
                'post_office_phone' => '0241234567',
                'vehicle_type' => 'Xe máy',
                'license_number' => str_repeat($i, 8),
                'license_image' => 'license_image.png',
                'identity_image' => 'identity_image.png',
                'experience' => rand(1, 5) . ' năm',
                'status' => 'approved',
                'approved_at' => Carbon::now(),
            ]);

            $addr = $driverAddresses_807188085[$i - 1];
            UserInfo::updateOrCreate(['user_id' => $driverUser->id], [
                'national_id' => '807188085' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'tax_code' => 'TX807188085' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_of_birth' => '1995-01-0' . (($i % 9) + 1),
                'full_address' => $addr['full_address'],
                'address_detail' => 'Khu vực giao hàng chính',
                'latitude' => $addr['latitude'],
                'longitude' => $addr['longitude'],
                'province_code' => null,
                'district_code' => null,
                'ward_code' => null,
            ]);
        }

        // ════════════════════════════════════════════
        // HUB: Bưu cục Hoài Đức
        // ID: 104953748 | Hoài Đức | (21.037167, 105.847985)
        // ════════════════════════════════════════════

        $hubUser = User::firstOrCreate(
            ['email' => 'hub_104953748@example.com'],
            [
                'phone' => '024' . str_pad('104953748', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu cục Hoài Đức',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '104953748',
                'hub_latitude' => '21.037167',
                'hub_longitude' => '105.847985',
                'hub_address' => 'Hoài Đức, Hà Nội',
            ]
        );

        $driverAddresses_104953748 = [
            ['full_address' => 'Đường Trường Chinh', 'latitude' => 20.999133, 'longitude' => 105.836934],
            ['full_address' => 'Phố Kim Mã', 'latitude' => 21.029117, 'longitude' => 105.806983],
            ['full_address' => 'Nhà ăn sinh viên quốc tế', 'latitude' => 21.005433, 'longitude' => 105.846409],
        ];

        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_104953748_{$i}@example.com";
            $phone = "092" . substr(str_pad('104953748', 9, '0', STR_PAD_LEFT), -6) . $i;
            $fullName = "Driver Bưu cục Hoài Đức #{$i}";

            $driverUser = User::firstOrCreate(['email' => $email], [
                'phone' => $phone,
                'full_name' => $fullName,
                'role' => 'driver',
                'status' => 'active',
                'password_hash' => Hash::make('123456'),
            ]);

            DriverProfile::updateOrCreate(['user_id' => $driverUser->id], [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'province_code' => 1,
                'post_office_id' => '104953748',
                'post_office_name' => 'Bưu cục Hoài Đức',
                'post_office_address' => 'Hoài Đức, Hà Nội',
                'post_office_lat' => '21.037167',
                'post_office_lng' => '105.847985',
                'post_office_phone' => '0241234567',
                'vehicle_type' => 'Xe máy',
                'license_number' => str_repeat($i, 8),
                'license_image' => 'license_image.png',
                'identity_image' => 'identity_image.png',
                'experience' => rand(1, 5) . ' năm',
                'status' => 'approved',
                'approved_at' => Carbon::now(),
            ]);

            $addr = $driverAddresses_104953748[$i - 1];
            UserInfo::updateOrCreate(['user_id' => $driverUser->id], [
                'national_id' => '104953748' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'tax_code' => 'TX104953748' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_of_birth' => '1995-01-0' . (($i % 9) + 1),
                'full_address' => $addr['full_address'],
                'address_detail' => 'Khu vực giao hàng chính',
                'latitude' => $addr['latitude'],
                'longitude' => $addr['longitude'],
                'province_code' => null,
                'district_code' => null,
                'ward_code' => null,
            ]);
        }

        // ════════════════════════════════════════════
        // HUB: AAL Express
        // ID: 624858082 | Hoài Đức | (21.041674, 105.782155)
        // ════════════════════════════════════════════

        $hubUser = User::firstOrCreate(
            ['email' => 'hub_624858082@example.com'],
            [
                'phone' => '024' . str_pad('624858082', 7, '0', STR_PAD_LEFT),
                'full_name' => 'AAL Express',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '624858082',
                'hub_latitude' => '21.041674',
                'hub_longitude' => '105.782155',
                'hub_address' => 'Phố Trần Quốc Hoàn',
            ]
        );

        $driverAddresses_624858082 = [
            ['full_address' => 'Phố Trần Thái Tông', 'latitude' => 21.033382, 'longitude' => 105.788673],
            ['full_address' => 'Duy Tân', 'latitude' => 21.030864, 'longitude' => 105.785242],
            ['full_address' => 'cafe', 'latitude' => 21.035208, 'longitude' => 105.820742],
        ];

        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_624858082_{$i}@example.com";
            $phone = "092" . substr(str_pad('624858082', 9, '0', STR_PAD_LEFT), -6) . $i;
            $fullName = "Driver AAL Express #{$i}";

            $driverUser = User::firstOrCreate(['email' => $email], [
                'phone' => $phone,
                'full_name' => $fullName,
                'role' => 'driver',
                'status' => 'active',
                'password_hash' => Hash::make('123456'),
            ]);

            DriverProfile::updateOrCreate(['user_id' => $driverUser->id], [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'province_code' => 1,
                'post_office_id' => '624858082',
                'post_office_name' => 'AAL Express',
                'post_office_address' => 'Phố Trần Quốc Hoàn',
                'post_office_lat' => '21.041674',
                'post_office_lng' => '105.782155',
                'post_office_phone' => '0241234567',
                'vehicle_type' => 'Xe máy',
                'license_number' => str_repeat($i, 8),
                'license_image' => 'license_image.png',
                'identity_image' => 'identity_image.png',
                'experience' => rand(1, 5) . ' năm',
                'status' => 'approved',
                'approved_at' => Carbon::now(),
            ]);

            $addr = $driverAddresses_624858082[$i - 1];
            UserInfo::updateOrCreate(['user_id' => $driverUser->id], [
                'national_id' => '624858082' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'tax_code' => 'TX624858082' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_of_birth' => '1995-01-0' . (($i % 9) + 1),
                'full_address' => $addr['full_address'],
                'address_detail' => 'Khu vực giao hàng chính',
                'latitude' => $addr['latitude'],
                'longitude' => $addr['longitude'],
                'province_code' => null,
                'district_code' => null,
                'ward_code' => null,
            ]);
        }

        // ════════════════════════════════════════════
        // HUB: Bưu Điện Yên Thái
        // ID: 022794521 | Hoài Đức | (21.048481, 105.807964)
        // ════════════════════════════════════════════

        $hubUser = User::firstOrCreate(
            ['email' => 'hub_022794521@example.com'],
            [
                'phone' => '024' . str_pad('022794521', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu Điện Yên Thái',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '022794521',
                'hub_latitude' => '21.048481',
                'hub_longitude' => '105.807964',
                'hub_address' => 'Đường Thụy Khuê',
            ]
        );

        $driverAddresses_022794521 = [
            ['full_address' => 'coffe chill', 'latitude' => 21.070554, 'longitude' => 105.777496],
            ['full_address' => 'Lương Văn Can', 'latitude' => 21.033102, 'longitude' => 105.850181],
            ['full_address' => 'Phố Văn Miếu', 'latitude' => 21.029946, 'longitude' => 105.837063],
        ];

        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_022794521_{$i}@example.com";
            $phone = "092" . substr(str_pad('022794521', 9, '0', STR_PAD_LEFT), -6) . $i;
            $fullName = "Driver Bưu Điện Yên Thái #{$i}";

            $driverUser = User::firstOrCreate(['email' => $email], [
                'phone' => $phone,
                'full_name' => $fullName,
                'role' => 'driver',
                'status' => 'active',
                'password_hash' => Hash::make('123456'),
            ]);

            DriverProfile::updateOrCreate(['user_id' => $driverUser->id], [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'province_code' => 1,
                'post_office_id' => '022794521',
                'post_office_name' => 'Bưu Điện Yên Thái',
                'post_office_address' => 'Đường Thụy Khuê',
                'post_office_lat' => '21.048481',
                'post_office_lng' => '105.807964',
                'post_office_phone' => '0241234567',
                'vehicle_type' => 'Xe máy',
                'license_number' => str_repeat($i, 8),
                'license_image' => 'license_image.png',
                'identity_image' => 'identity_image.png',
                'experience' => rand(1, 5) . ' năm',
                'status' => 'approved',
                'approved_at' => Carbon::now(),
            ]);

            $addr = $driverAddresses_022794521[$i - 1];
            UserInfo::updateOrCreate(['user_id' => $driverUser->id], [
                'national_id' => '022794521' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'tax_code' => 'TX022794521' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_of_birth' => '1995-01-0' . (($i % 9) + 1),
                'full_address' => $addr['full_address'],
                'address_detail' => 'Khu vực giao hàng chính',
                'latitude' => $addr['latitude'],
                'longitude' => $addr['longitude'],
                'province_code' => null,
                'district_code' => null,
                'ward_code' => null,
            ]);
        }

        // ════════════════════════════════════════════
        // HUB: Giao hàng Tiết kiệm
        // ID: 568384383 | Hoài Đức | (21.048209, 105.809551)
        // ════════════════════════════════════════════

        $hubUser = User::firstOrCreate(
            ['email' => 'hub_568384383@example.com'],
            [
                'phone' => '024' . str_pad('568384383', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Giao hàng Tiết kiệm',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '568384383',
                'hub_latitude' => '21.048209',
                'hub_longitude' => '105.809551',
                'hub_address' => 'Hoài Đức, Hà Nội',
            ]
        );

        $driverAddresses_568384383 = [
            ['full_address' => "Luna d'automno", 'latitude' => 21.027021, 'longitude' => 105.842833],
            ['full_address' => 'Cầu Giấy', 'latitude' => 21.034908, 'longitude' => 105.793689],
            ['full_address' => 'restaurant', 'latitude' => 21.03441, 'longitude' => 105.854418],
        ];

        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_568384383_{$i}@example.com";
            $phone = "092" . substr(str_pad('568384383', 9, '0', STR_PAD_LEFT), -6) . $i;
            $fullName = "Driver Giao hàng Tiết kiệm #{$i}";

            $driverUser = User::firstOrCreate(['email' => $email], [
                'phone' => $phone,
                'full_name' => $fullName,
                'role' => 'driver',
                'status' => 'active',
                'password_hash' => Hash::make('123456'),
            ]);

            DriverProfile::updateOrCreate(['user_id' => $driverUser->id], [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'province_code' => 1,
                'post_office_id' => '568384383',
                'post_office_name' => 'Giao hàng Tiết kiệm',
                'post_office_address' => 'Hoài Đức, Hà Nội',
                'post_office_lat' => '21.048209',
                'post_office_lng' => '105.809551',
                'post_office_phone' => '0241234567',
                'vehicle_type' => 'Xe máy',
                'license_number' => str_repeat($i, 8),
                'license_image' => 'license_image.png',
                'identity_image' => 'identity_image.png',
                'experience' => rand(1, 5) . ' năm',
                'status' => 'approved',
                'approved_at' => Carbon::now(),
            ]);

            $addr = $driverAddresses_568384383[$i - 1];
            UserInfo::updateOrCreate(['user_id' => $driverUser->id], [
                'national_id' => '568384383' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'tax_code' => 'TX568384383' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_of_birth' => '1995-01-0' . (($i % 9) + 1),
                'full_address' => $addr['full_address'],
                'address_detail' => 'Khu vực giao hàng chính',
                'latitude' => $addr['latitude'],
                'longitude' => $addr['longitude'],
                'province_code' => null,
                'district_code' => null,
                'ward_code' => null,
            ]);
        }

        // ════════════════════════════════════════════
        // HUB: DHL
        // ID: 674770005 | Hoài Đức | (21.00955, 105.835515)
        // ════════════════════════════════════════════

        $hubUser = User::firstOrCreate(
            ['email' => 'hub_674770005@example.com'],
            [
                'phone' => '024' . str_pad('674770005', 7, '0', STR_PAD_LEFT),
                'full_name' => 'DHL',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '674770005',
                'hub_latitude' => '21.00955',
                'hub_longitude' => '105.835515',
                'hub_address' => 'Phố Phạm Ngọc Thạch',
            ]
        );

        $driverAddresses_674770005 = [
            ['full_address' => '79C Lý Nam Đế', 'latitude' => 21.033366, 'longitude' => 105.844756],
            ['full_address' => 'Phố Hàng Buồm', 'latitude' => 21.036038, 'longitude' => 105.852097],
            ['full_address' => 'Tuên an', 'latitude' => 21.030202, 'longitude' => 105.850564],
        ];

        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_674770005_{$i}@example.com";
            $phone = "092" . substr(str_pad('674770005', 9, '0', STR_PAD_LEFT), -6) . $i;
            $fullName = "Driver DHL #{$i}";

            $driverUser = User::firstOrCreate(['email' => $email], [
                'phone' => $phone,
                'full_name' => $fullName,
                'role' => 'driver',
                'status' => 'active',
                'password_hash' => Hash::make('123456'),
            ]);

            DriverProfile::updateOrCreate(['user_id' => $driverUser->id], [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'province_code' => 1,
                'post_office_id' => '674770005',
                'post_office_name' => 'DHL',
                'post_office_address' => 'Phố Phạm Ngọc Thạch',
                'post_office_lat' => '21.00955',
                'post_office_lng' => '105.835515',
                'post_office_phone' => '+84 24 3775 6937',
                'vehicle_type' => 'Xe máy',
                'license_number' => str_repeat($i, 8),
                'license_image' => 'license_image.png',
                'identity_image' => 'identity_image.png',
                'experience' => rand(1, 5) . ' năm',
                'status' => 'approved',
                'approved_at' => Carbon::now(),
            ]);

            $addr = $driverAddresses_674770005[$i - 1];
            UserInfo::updateOrCreate(['user_id' => $driverUser->id], [
                'national_id' => '674770005' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'tax_code' => 'TX674770005' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_of_birth' => '1995-01-0' . (($i % 9) + 1),
                'full_address' => $addr['full_address'],
                'address_detail' => 'Khu vực giao hàng chính',
                'latitude' => $addr['latitude'],
                'longitude' => $addr['longitude'],
                'province_code' => null,
                'district_code' => null,
                'ward_code' => null,
            ]);
        }

        // ════════════════════════════════════════════
        // HUB: Chuyển phát nhanh Văn Minh
        // ID: 403091591 | Hoài Đức | (21.015283, 105.832786)
        // ════════════════════════════════════════════

        $hubUser = User::firstOrCreate(
            ['email' => 'hub_403091591@example.com'],
            [
                'phone' => '024' . str_pad('403091591', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Chuyển phát nhanh Văn Minh',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '403091591',
                'hub_latitude' => '21.015283',
                'hub_longitude' => '105.832786',
                'hub_address' => 'Phố Xã Đàn',
            ]
        );

        $driverAddresses_403091591 = [
            ['full_address' => 'Phố Ngô Quyền', 'latitude' => 21.02456, 'longitude' => 105.854909],
            ['full_address' => 'Nhà Hàng Lẩu Hơi Cosmos', 'latitude' => 21.025739, 'longitude' => 105.822048],
            ['full_address' => 'Bun Ca sam Cay Si', 'latitude' => 21.033383, 'longitude' => 105.852671],
        ];

        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_403091591_{$i}@example.com";
            $phone = "092" . substr(str_pad('403091591', 9, '0', STR_PAD_LEFT), -6) . $i;
            $fullName = "Driver Chuyển phát nhanh Văn Minh #{$i}";

            $driverUser = User::firstOrCreate(['email' => $email], [
                'phone' => $phone,
                'full_name' => $fullName,
                'role' => 'driver',
                'status' => 'active',
                'password_hash' => Hash::make('123456'),
            ]);

            DriverProfile::updateOrCreate(['user_id' => $driverUser->id], [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'province_code' => 1,
                'post_office_id' => '403091591',
                'post_office_name' => 'Chuyển phát nhanh Văn Minh',
                'post_office_address' => 'Phố Xã Đàn',
                'post_office_lat' => '21.015283',
                'post_office_lng' => '105.832786',
                'post_office_phone' => '0241234567',
                'vehicle_type' => 'Xe máy',
                'license_number' => str_repeat($i, 8),
                'license_image' => 'license_image.png',
                'identity_image' => 'identity_image.png',
                'experience' => rand(1, 5) . ' năm',
                'status' => 'approved',
                'approved_at' => Carbon::now(),
            ]);

            $addr = $driverAddresses_403091591[$i - 1];
            UserInfo::updateOrCreate(['user_id' => $driverUser->id], [
                'national_id' => '403091591' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'tax_code' => 'TX403091591' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_of_birth' => '1995-01-0' . (($i % 9) + 1),
                'full_address' => $addr['full_address'],
                'address_detail' => 'Khu vực giao hàng chính',
                'latitude' => $addr['latitude'],
                'longitude' => $addr['longitude'],
                'province_code' => null,
                'district_code' => null,
                'ward_code' => null,
            ]);
        }

        // ════════════════════════════════════════════
        // HUB: 247Express
        // ID: 545461890 | Hoài Đức | (21.043931, 105.786054)
        // ════════════════════════════════════════════

        $hubUser = User::firstOrCreate(
            ['email' => 'hub_545461890@example.com'],
            [
                'phone' => '024' . str_pad('545461890', 7, '0', STR_PAD_LEFT),
                'full_name' => '247Express',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '545461890',
                'hub_latitude' => '21.043931',
                'hub_longitude' => '105.786054',
                'hub_address' => 'Đường Đặng Thùy Trâm',
            ]
        );

        $driverAddresses_545461890 = [
            ['full_address' => 'Nhà hàng Bornga - Tân Mỹ', 'latitude' => 21.025741, 'longitude' => 105.758916],
            ['full_address' => 'Trường Song Ngữ Quốc Tế Hanoi Academy', 'latitude' => 21.073668, 'longitude' => 105.802922],
            ['full_address' => 'Phố Trung Hoà', 'latitude' => 21.015273, 'longitude' => 105.801192],
        ];

        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_545461890_{$i}@example.com";
            $phone = "092" . substr(str_pad('545461890', 9, '0', STR_PAD_LEFT), -6) . $i;
            $fullName = "Driver 247Express #{$i}";

            $driverUser = User::firstOrCreate(['email' => $email], [
                'phone' => $phone,
                'full_name' => $fullName,
                'role' => 'driver',
                'status' => 'active',
                'password_hash' => Hash::make('123456'),
            ]);

            DriverProfile::updateOrCreate(['user_id' => $driverUser->id], [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'province_code' => 1,
                'post_office_id' => '545461890',
                'post_office_name' => '247Express',
                'post_office_address' => 'Đường Đặng Thùy Trâm',
                'post_office_lat' => '21.043931',
                'post_office_lng' => '105.786054',
                'post_office_phone' => '0241234567',
                'vehicle_type' => 'Xe máy',
                'license_number' => str_repeat($i, 8),
                'license_image' => 'license_image.png',
                'identity_image' => 'identity_image.png',
                'experience' => rand(1, 5) . ' năm',
                'status' => 'approved',
                'approved_at' => Carbon::now(),
            ]);

            $addr = $driverAddresses_545461890[$i - 1];
            UserInfo::updateOrCreate(['user_id' => $driverUser->id], [
                'national_id' => '545461890' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'tax_code' => 'TX545461890' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_of_birth' => '1995-01-0' . (($i % 9) + 1),
                'full_address' => $addr['full_address'],
                'address_detail' => 'Khu vực giao hàng chính',
                'latitude' => $addr['latitude'],
                'longitude' => $addr['longitude'],
                'province_code' => null,
                'district_code' => null,
                'ward_code' => null,
            ]);
        }

        // ════════════════════════════════════════════
        // HUB: Viettel Post
        // ID: 748594351 | Hoài Đức | (21.043851, 105.785706)
        // ════════════════════════════════════════════

        $hubUser = User::firstOrCreate(
            ['email' => 'hub_748594351@example.com'],
            [
                'phone' => '024' . str_pad('748594351', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Viettel Post',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '748594351',
                'hub_latitude' => '21.043851',
                'hub_longitude' => '105.785706',
                'hub_address' => 'Phố Đặng Thùy Trâm',
            ]
        );

        $driverAddresses_748594351 = [
            ['full_address' => 'Highlands Coffee', 'latitude' => 21.013514, 'longitude' => 105.803246],
            ['full_address' => 'Phố Hoàng Ngân', 'latitude' => 21.00903, 'longitude' => 105.801961],
            ['full_address' => 'cafe', 'latitude' => 21.074694, 'longitude' => 105.772098],
        ];

        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_748594351_{$i}@example.com";
            $phone = "092" . substr(str_pad('748594351', 9, '0', STR_PAD_LEFT), -6) . $i;
            $fullName = "Driver Viettel Post #{$i}";

            $driverUser = User::firstOrCreate(['email' => $email], [
                'phone' => $phone,
                'full_name' => $fullName,
                'role' => 'driver',
                'status' => 'active',
                'password_hash' => Hash::make('123456'),
            ]);

            DriverProfile::updateOrCreate(['user_id' => $driverUser->id], [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'province_code' => 1,
                'post_office_id' => '748594351',
                'post_office_name' => 'Viettel Post',
                'post_office_address' => 'Phố Đặng Thùy Trâm',
                'post_office_lat' => '21.043851',
                'post_office_lng' => '105.785706',
                'post_office_phone' => '0241234567',
                'vehicle_type' => 'Xe máy',
                'license_number' => str_repeat($i, 8),
                'license_image' => 'license_image.png',
                'identity_image' => 'identity_image.png',
                'experience' => rand(1, 5) . ' năm',
                'status' => 'approved',
                'approved_at' => Carbon::now(),
            ]);

            $addr = $driverAddresses_748594351[$i - 1];
            UserInfo::updateOrCreate(['user_id' => $driverUser->id], [
                'national_id' => '748594351' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'tax_code' => 'TX748594351' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_of_birth' => '1995-01-0' . (($i % 9) + 1),
                'full_address' => $addr['full_address'],
                'address_detail' => 'Khu vực giao hàng chính',
                'latitude' => $addr['latitude'],
                'longitude' => $addr['longitude'],
                'province_code' => null,
                'district_code' => null,
                'ward_code' => null,
            ]);
        }

        // ════════════════════════════════════════════
        // HUB: J&T Express
        // ID: 309792664 | Hoài Đức | (21.043881, 105.784743)
        // ════════════════════════════════════════════

        $hubUser = User::firstOrCreate(
            ['email' => 'hub_309792664@example.com'],
            [
                'phone' => '024' . str_pad('309792664', 7, '0', STR_PAD_LEFT),
                'full_name' => 'J&T Express',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '309792664',
                'hub_latitude' => '21.043881',
                'hub_longitude' => '105.784743',
                'hub_address' => 'Ngõ 3 Phạm Tuấn Tài',
            ]
        );

        $driverAddresses_309792664 = [
            ['full_address' => 'Phố Trung Kính', 'latitude' => 21.020053, 'longitude' => 105.792954],
            ['full_address' => 'Bún chả - Phở Gà Ta', 'latitude' => 21.004472, 'longitude' => 105.778968],
            ['full_address' => 'Đường Lê Văn Lương', 'latitude' => 21.006366, 'longitude' => 105.806499],
        ];

        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_309792664_{$i}@example.com";
            $phone = "092" . substr(str_pad('309792664', 9, '0', STR_PAD_LEFT), -6) . $i;
            $fullName = "Driver J&T Express #{$i}";

            $driverUser = User::firstOrCreate(['email' => $email], [
                'phone' => $phone,
                'full_name' => $fullName,
                'role' => 'driver',
                'status' => 'active',
                'password_hash' => Hash::make('123456'),
            ]);

            DriverProfile::updateOrCreate(['user_id' => $driverUser->id], [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'province_code' => 1,
                'post_office_id' => '309792664',
                'post_office_name' => 'J&T Express',
                'post_office_address' => 'Ngõ 3 Phạm Tuấn Tài',
                'post_office_lat' => '21.043881',
                'post_office_lng' => '105.784743',
                'post_office_phone' => '0241234567',
                'vehicle_type' => 'Xe máy',
                'license_number' => str_repeat($i, 8),
                'license_image' => 'license_image.png',
                'identity_image' => 'identity_image.png',
                'experience' => rand(1, 5) . ' năm',
                'status' => 'approved',
                'approved_at' => Carbon::now(),
            ]);

            $addr = $driverAddresses_309792664[$i - 1];
            UserInfo::updateOrCreate(['user_id' => $driverUser->id], [
                'national_id' => '309792664' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'tax_code' => 'TX309792664' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_of_birth' => '1995-01-0' . (($i % 9) + 1),
                'full_address' => $addr['full_address'],
                'address_detail' => 'Khu vực giao hàng chính',
                'latitude' => $addr['latitude'],
                'longitude' => $addr['longitude'],
                'province_code' => null,
                'district_code' => null,
                'ward_code' => null,
            ]);
        }

        // ════════════════════════════════════════════
        // HUB: Bưu điện CP16
        // ID: 304662290 | Hoài Đức | (21.033627, 105.833826)
        // ════════════════════════════════════════════

        $hubUser = User::firstOrCreate(
            ['email' => 'hub_304662290@example.com'],
            [
                'phone' => '024' . str_pad('304662290', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu điện CP16',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '304662290',
                'hub_latitude' => '21.033627',
                'hub_longitude' => '105.833826',
                'hub_address' => 'Phố Ông Ích Khiêm',
            ]
        );

        $driverAddresses_304662290 = [
            ['full_address' => 'Phố Thái Hà', 'latitude' => 21.016021, 'longitude' => 105.815518],
            ['full_address' => 'Lê Văn Lương', 'latitude' => 21.002665, 'longitude' => 105.801294],
            ['full_address' => 'restaurant', 'latitude' => 21.0274, 'longitude' => 105.84243],
        ];

        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_304662290_{$i}@example.com";
            $phone = "092" . substr(str_pad('304662290', 9, '0', STR_PAD_LEFT), -6) . $i;
            $fullName = "Driver Bưu điện CP16 #{$i}";

            $driverUser = User::firstOrCreate(['email' => $email], [
                'phone' => $phone,
                'full_name' => $fullName,
                'role' => 'driver',
                'status' => 'active',
                'password_hash' => Hash::make('123456'),
            ]);

            DriverProfile::updateOrCreate(['user_id' => $driverUser->id], [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'province_code' => 1,
                'post_office_id' => '304662290',
                'post_office_name' => 'Bưu điện CP16',
                'post_office_address' => 'Phố Ông Ích Khiêm',
                'post_office_lat' => '21.033627',
                'post_office_lng' => '105.833826',
                'post_office_phone' => '0241234567',
                'vehicle_type' => 'Xe máy',
                'license_number' => str_repeat($i, 8),
                'license_image' => 'license_image.png',
                'identity_image' => 'identity_image.png',
                'experience' => rand(1, 5) . ' năm',
                'status' => 'approved',
                'approved_at' => Carbon::now(),
            ]);

            $addr = $driverAddresses_304662290[$i - 1];
            UserInfo::updateOrCreate(['user_id' => $driverUser->id], [
                'national_id' => '304662290' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'tax_code' => 'TX304662290' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_of_birth' => '1995-01-0' . (($i % 9) + 1),
                'full_address' => $addr['full_address'],
                'address_detail' => 'Khu vực giao hàng chính',
                'latitude' => $addr['latitude'],
                'longitude' => $addr['longitude'],
                'province_code' => null,
                'district_code' => null,
                'ward_code' => null,
            ]);
        }

        // ════════════════════════════════════════════
        // HUB: Bưu cục Hoài Đức
        // ID: 527710969 | Hoài Đức | (21.066678, 105.803449)
        // ════════════════════════════════════════════

        $hubUser = User::firstOrCreate(
            ['email' => 'hub_527710969@example.com'],
            [
                'phone' => '024' . str_pad('527710969', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu cục Hoài Đức',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '527710969',
                'hub_latitude' => '21.066678',
                'hub_longitude' => '105.803449',
                'hub_address' => 'Hoài Đức, Hà Nội',
            ]
        );

        $driverAddresses_527710969 = [
            ['full_address' => 'Nhà Hàng A La Folie', 'latitude' => 21.049201, 'longitude' => 105.839471],
            ['full_address' => 'Đường Đức Thắng', 'latitude' => 21.080379, 'longitude' => 105.777767],
            ['full_address' => 'Cơm Ông Bình', 'latitude' => 21.067591, 'longitude' => 105.76208],
        ];

        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_527710969_{$i}@example.com";
            $phone = "092" . substr(str_pad('527710969', 9, '0', STR_PAD_LEFT), -6) . $i;
            $fullName = "Driver Bưu cục Hoài Đức #{$i}";

            $driverUser = User::firstOrCreate(['email' => $email], [
                'phone' => $phone,
                'full_name' => $fullName,
                'role' => 'driver',
                'status' => 'active',
                'password_hash' => Hash::make('123456'),
            ]);

            DriverProfile::updateOrCreate(['user_id' => $driverUser->id], [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'province_code' => 1,
                'post_office_id' => '527710969',
                'post_office_name' => 'Bưu cục Hoài Đức',
                'post_office_address' => 'Hoài Đức, Hà Nội',
                'post_office_lat' => '21.066678',
                'post_office_lng' => '105.803449',
                'post_office_phone' => '0241234567',
                'vehicle_type' => 'Xe máy',
                'license_number' => str_repeat($i, 8),
                'license_image' => 'license_image.png',
                'identity_image' => 'identity_image.png',
                'experience' => rand(1, 5) . ' năm',
                'status' => 'approved',
                'approved_at' => Carbon::now(),
            ]);

            $addr = $driverAddresses_527710969[$i - 1];
            UserInfo::updateOrCreate(['user_id' => $driverUser->id], [
                'national_id' => '527710969' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'tax_code' => 'TX527710969' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_of_birth' => '1995-01-0' . (($i % 9) + 1),
                'full_address' => $addr['full_address'],
                'address_detail' => 'Khu vực giao hàng chính',
                'latitude' => $addr['latitude'],
                'longitude' => $addr['longitude'],
                'province_code' => null,
                'district_code' => null,
                'ward_code' => null,
            ]);
        }

        // ════════════════════════════════════════════
        // HUB: Bưu điện Nam Từ Liêm
        // ID: 874998873 | Hoài Đức | (21.039879, 105.764885)
        // ════════════════════════════════════════════

        $hubUser = User::firstOrCreate(
            ['email' => 'hub_874998873@example.com'],
            [
                'phone' => '024' . str_pad('874998873', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu điện Nam Từ Liêm',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '874998873',
                'hub_latitude' => '21.039879',
                'hub_longitude' => '105.764885',
                'hub_address' => 'Hoài Đức, Hà Nội',
            ]
        );

        $driverAddresses_874998873 = [
            ['full_address' => 'restaurant', 'latitude' => 21.043738, 'longitude' => 105.78967],
            ['full_address' => 'Cơm Ngon Hà Nội', 'latitude' => 21.07875, 'longitude' => 105.7747],
            ['full_address' => 'Vape store', 'latitude' => 21.016931, 'longitude' => 105.802481],
        ];

        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_874998873_{$i}@example.com";
            $phone = "092" . substr(str_pad('874998873', 9, '0', STR_PAD_LEFT), -6) . $i;
            $fullName = "Driver Bưu điện Nam Từ Liêm #{$i}";

            $driverUser = User::firstOrCreate(['email' => $email], [
                'phone' => $phone,
                'full_name' => $fullName,
                'role' => 'driver',
                'status' => 'active',
                'password_hash' => Hash::make('123456'),
            ]);

            DriverProfile::updateOrCreate(['user_id' => $driverUser->id], [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'province_code' => 1,
                'post_office_id' => '874998873',
                'post_office_name' => 'Bưu điện Nam Từ Liêm',
                'post_office_address' => 'Hoài Đức, Hà Nội',
                'post_office_lat' => '21.039879',
                'post_office_lng' => '105.764885',
                'post_office_phone' => '0241234567',
                'vehicle_type' => 'Xe máy',
                'license_number' => str_repeat($i, 8),
                'license_image' => 'license_image.png',
                'identity_image' => 'identity_image.png',
                'experience' => rand(1, 5) . ' năm',
                'status' => 'approved',
                'approved_at' => Carbon::now(),
            ]);

            $addr = $driverAddresses_874998873[$i - 1];
            UserInfo::updateOrCreate(['user_id' => $driverUser->id], [
                'national_id' => '874998873' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'tax_code' => 'TX874998873' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_of_birth' => '1995-01-0' . (($i % 9) + 1),
                'full_address' => $addr['full_address'],
                'address_detail' => 'Khu vực giao hàng chính',
                'latitude' => $addr['latitude'],
                'longitude' => $addr['longitude'],
                'province_code' => null,
                'district_code' => null,
                'ward_code' => null,
            ]);
        }

        // ════════════════════════════════════════════
        // HUB: Bưu điện Trung tâm Cầu Giấy
        // ID: 965945333 | Hoài Đức | (21.014913, 105.799547)
        // ════════════════════════════════════════════

        $hubUser = User::firstOrCreate(
            ['email' => 'hub_965945333@example.com'],
            [
                'phone' => '024' . str_pad('965945333', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu điện Trung tâm Cầu Giấy',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '965945333',
                'hub_latitude' => '21.014913',
                'hub_longitude' => '105.799547',
                'hub_address' => 'Phố Lưu Quang Vũ',
            ]
        );

        $driverAddresses_965945333 = [
            ['full_address' => 'Phố Chùa Bộc', 'latitude' => 21.006366, 'longitude' => 105.830526],
            ['full_address' => 'cafe', 'latitude' => 21.030123, 'longitude' => 105.824],
            ['full_address' => 'Phố Phạm Ngọc Thạch', 'latitude' => 21.008143, 'longitude' => 105.833325],
        ];

        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_965945333_{$i}@example.com";
            $phone = "092" . substr(str_pad('965945333', 9, '0', STR_PAD_LEFT), -6) . $i;
            $fullName = "Driver Bưu điện Trung tâm Cầu Giấy #{$i}";

            $driverUser = User::firstOrCreate(['email' => $email], [
                'phone' => $phone,
                'full_name' => $fullName,
                'role' => 'driver',
                'status' => 'active',
                'password_hash' => Hash::make('123456'),
            ]);

            DriverProfile::updateOrCreate(['user_id' => $driverUser->id], [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'province_code' => 1,
                'post_office_id' => '965945333',
                'post_office_name' => 'Bưu điện Trung tâm Cầu Giấy',
                'post_office_address' => 'Phố Lưu Quang Vũ',
                'post_office_lat' => '21.014913',
                'post_office_lng' => '105.799547',
                'post_office_phone' => '0241234567',
                'vehicle_type' => 'Xe máy',
                'license_number' => str_repeat($i, 8),
                'license_image' => 'license_image.png',
                'identity_image' => 'identity_image.png',
                'experience' => rand(1, 5) . ' năm',
                'status' => 'approved',
                'approved_at' => Carbon::now(),
            ]);

            $addr = $driverAddresses_965945333[$i - 1];
            UserInfo::updateOrCreate(['user_id' => $driverUser->id], [
                'national_id' => '965945333' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'tax_code' => 'TX965945333' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_of_birth' => '1995-01-0' . (($i % 9) + 1),
                'full_address' => $addr['full_address'],
                'address_detail' => 'Khu vực giao hàng chính',
                'latitude' => $addr['latitude'],
                'longitude' => $addr['longitude'],
                'province_code' => null,
                'district_code' => null,
                'ward_code' => null,
            ]);
        }

        // ════════════════════════════════════════════
        // HUB: Bưu điện Ciputra
        // ID: 974063116 | Hoài Đức | (21.07303, 105.804575)
        // ════════════════════════════════════════════

        $hubUser = User::firstOrCreate(
            ['email' => 'hub_974063116@example.com'],
            [
                'phone' => '024' . str_pad('974063116', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu điện Ciputra',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '974063116',
                'hub_latitude' => '21.07303',
                'hub_longitude' => '105.804575',
                'hub_address' => 'Plutus Street',
            ]
        );

        $driverAddresses_974063116 = [
            ['full_address' => 'Phố Văn Hội', 'latitude' => 21.079, 'longitude' => 105.773695],
            ['full_address' => 'らーめん喰龍', 'latitude' => 21.035967, 'longitude' => 105.810481],
            ['full_address' => 'Nướng mỏ', 'latitude' => 21.071556, 'longitude' => 105.771901],
        ];

        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_974063116_{$i}@example.com";
            $phone = "092" . substr(str_pad('974063116', 9, '0', STR_PAD_LEFT), -6) . $i;
            $fullName = "Driver Bưu điện Ciputra #{$i}";

            $driverUser = User::firstOrCreate(['email' => $email], [
                'phone' => $phone,
                'full_name' => $fullName,
                'role' => 'driver',
                'status' => 'active',
                'password_hash' => Hash::make('123456'),
            ]);

            DriverProfile::updateOrCreate(['user_id' => $driverUser->id], [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'province_code' => 1,
                'post_office_id' => '974063116',
                'post_office_name' => 'Bưu điện Ciputra',
                'post_office_address' => 'Plutus Street',
                'post_office_lat' => '21.07303',
                'post_office_lng' => '105.804575',
                'post_office_phone' => '0241234567',
                'vehicle_type' => 'Xe máy',
                'license_number' => str_repeat($i, 8),
                'license_image' => 'license_image.png',
                'identity_image' => 'identity_image.png',
                'experience' => rand(1, 5) . ' năm',
                'status' => 'approved',
                'approved_at' => Carbon::now(),
            ]);

            $addr = $driverAddresses_974063116[$i - 1];
            UserInfo::updateOrCreate(['user_id' => $driverUser->id], [
                'national_id' => '974063116' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'tax_code' => 'TX974063116' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_of_birth' => '1995-01-0' . (($i % 9) + 1),
                'full_address' => $addr['full_address'],
                'address_detail' => 'Khu vực giao hàng chính',
                'latitude' => $addr['latitude'],
                'longitude' => $addr['longitude'],
                'province_code' => null,
                'district_code' => null,
                'ward_code' => null,
            ]);
        }

        // ════════════════════════════════════════════
        // HUB: Bưu điện Nguyễn Thái Học
        // ID: 1021370764 | Hoài Đức | (21.028845, 105.840758)
        // ════════════════════════════════════════════

        $hubUser = User::firstOrCreate(
            ['email' => 'hub_1021370764@example.com'],
            [
                'phone' => '024' . str_pad('1021370764', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu điện Nguyễn Thái Học',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '1021370764',
                'hub_latitude' => '21.028845',
                'hub_longitude' => '105.840758',
                'hub_address' => 'Phố Nguyễn Thái Học',
            ]
        );

        $driverAddresses_1021370764 = [
            ['full_address' => 'Phố Lý Quốc Sư', 'latitude' => 21.030472, 'longitude' => 105.848847],
            ['full_address' => 'Chất Coffee & Drinks', 'latitude' => 21.031159, 'longitude' => 105.84871],
            ['full_address' => 'restaurant', 'latitude' => 21.029964, 'longitude' => 105.847823],
        ];

        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_1021370764_{$i}@example.com";
            $phone = "092" . substr(str_pad('1021370764', 9, '0', STR_PAD_LEFT), -6) . $i;
            $fullName = "Driver Bưu điện Nguyễn Thái Học #{$i}";

            $driverUser = User::firstOrCreate(['email' => $email], [
                'phone' => $phone,
                'full_name' => $fullName,
                'role' => 'driver',
                'status' => 'active',
                'password_hash' => Hash::make('123456'),
            ]);

            DriverProfile::updateOrCreate(['user_id' => $driverUser->id], [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'province_code' => 1,
                'post_office_id' => '1021370764',
                'post_office_name' => 'Bưu điện Nguyễn Thái Học',
                'post_office_address' => 'Phố Nguyễn Thái Học',
                'post_office_lat' => '21.028845',
                'post_office_lng' => '105.840758',
                'post_office_phone' => '+84 24 3831 3366',
                'vehicle_type' => 'Xe máy',
                'license_number' => str_repeat($i, 8),
                'license_image' => 'license_image.png',
                'identity_image' => 'identity_image.png',
                'experience' => rand(1, 5) . ' năm',
                'status' => 'approved',
                'approved_at' => Carbon::now(),
            ]);

            $addr = $driverAddresses_1021370764[$i - 1];
            UserInfo::updateOrCreate(['user_id' => $driverUser->id], [
                'national_id' => '1021370764' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'tax_code' => 'TX1021370764' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_of_birth' => '1995-01-0' . (($i % 9) + 1),
                'full_address' => $addr['full_address'],
                'address_detail' => 'Khu vực giao hàng chính',
                'latitude' => $addr['latitude'],
                'longitude' => $addr['longitude'],
                'province_code' => null,
                'district_code' => null,
                'ward_code' => null,
            ]);
        }

        // ════════════════════════════════════════════
        // HUB: Bưu điện Thanh Niên
        // ID: 1021404967 | Hoài Đức | (21.04404, 105.836612)
        // ════════════════════════════════════════════

        $hubUser = User::firstOrCreate(
            ['email' => 'hub_1021404967@example.com'],
            [
                'phone' => '024' . str_pad('1021404967', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu điện Thanh Niên',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '1021404967',
                'hub_latitude' => '21.04404',
                'hub_longitude' => '105.836612',
                'hub_address' => 'Phố Trấn Vũ',
            ]
        );

        $driverAddresses_1021404967 = [
            ['full_address' => 'Reng Reng Café', 'latitude' => 21.038478, 'longitude' => 105.844979],
            ['full_address' => 'Kim Mã', 'latitude' => 21.031198, 'longitude' => 105.822061],
            ['full_address' => 'Phố Hàng Gai', 'latitude' => 21.032236, 'longitude' => 105.851068],
        ];

        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_1021404967_{$i}@example.com";
            $phone = "092" . substr(str_pad('1021404967', 9, '0', STR_PAD_LEFT), -6) . $i;
            $fullName = "Driver Bưu điện Thanh Niên #{$i}";

            $driverUser = User::firstOrCreate(['email' => $email], [
                'phone' => $phone,
                'full_name' => $fullName,
                'role' => 'driver',
                'status' => 'active',
                'password_hash' => Hash::make('123456'),
            ]);

            DriverProfile::updateOrCreate(['user_id' => $driverUser->id], [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'province_code' => 1,
                'post_office_id' => '1021404967',
                'post_office_name' => 'Bưu điện Thanh Niên',
                'post_office_address' => 'Phố Trấn Vũ',
                'post_office_lat' => '21.04404',
                'post_office_lng' => '105.836612',
                'post_office_phone' => '0241234567',
                'vehicle_type' => 'Xe máy',
                'license_number' => str_repeat($i, 8),
                'license_image' => 'license_image.png',
                'identity_image' => 'identity_image.png',
                'experience' => rand(1, 5) . ' năm',
                'status' => 'approved',
                'approved_at' => Carbon::now(),
            ]);

            $addr = $driverAddresses_1021404967[$i - 1];
            UserInfo::updateOrCreate(['user_id' => $driverUser->id], [
                'national_id' => '1021404967' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'tax_code' => 'TX1021404967' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_of_birth' => '1995-01-0' . (($i % 9) + 1),
                'full_address' => $addr['full_address'],
                'address_detail' => 'Khu vực giao hàng chính',
                'latitude' => $addr['latitude'],
                'longitude' => $addr['longitude'],
                'province_code' => null,
                'district_code' => null,
                'ward_code' => null,
            ]);
        }

        // ════════════════════════════════════════════
        // HUB: Bưu điện Mỹ Đình 2
        // ID: 1231794774 | Hoài Đức | (21.037412, 105.766555)
        // ════════════════════════════════════════════

        $hubUser = User::firstOrCreate(
            ['email' => 'hub_1231794774@example.com'],
            [
                'phone' => '024' . str_pad('1231794774', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu điện Mỹ Đình 2',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '1231794774',
                'hub_latitude' => '21.037412',
                'hub_longitude' => '105.766555',
                'hub_address' => 'Phố Nguyễn Cơ Thạch',
            ]
        );

        $driverAddresses_1231794774 = [
            ['full_address' => 'Đường Phạm Văn Đồng', 'latitude' => 21.051524, 'longitude' => 105.782106],
            ['full_address' => 'Toán Trí Tuệ Superbrain Ba đình', 'latitude' => 21.037154, 'longitude' => 105.810715],
            ['full_address' => 'Khu Ngoại Giao Đoàn', 'latitude' => 21.065497, 'longitude' => 105.801564],
        ];

        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_1231794774_{$i}@example.com";
            $phone = "092" . substr(str_pad('1231794774', 9, '0', STR_PAD_LEFT), -6) . $i;
            $fullName = "Driver Bưu điện Mỹ Đình 2 #{$i}";

            $driverUser = User::firstOrCreate(['email' => $email], [
                'phone' => $phone,
                'full_name' => $fullName,
                'role' => 'driver',
                'status' => 'active',
                'password_hash' => Hash::make('123456'),
            ]);

            DriverProfile::updateOrCreate(['user_id' => $driverUser->id], [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'province_code' => 1,
                'post_office_id' => '1231794774',
                'post_office_name' => 'Bưu điện Mỹ Đình 2',
                'post_office_address' => 'Phố Nguyễn Cơ Thạch',
                'post_office_lat' => '21.037412',
                'post_office_lng' => '105.766555',
                'post_office_phone' => '0241234567',
                'vehicle_type' => 'Xe máy',
                'license_number' => str_repeat($i, 8),
                'license_image' => 'license_image.png',
                'identity_image' => 'identity_image.png',
                'experience' => rand(1, 5) . ' năm',
                'status' => 'approved',
                'approved_at' => Carbon::now(),
            ]);

            $addr = $driverAddresses_1231794774[$i - 1];
            UserInfo::updateOrCreate(['user_id' => $driverUser->id], [
                'national_id' => '1231794774' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'tax_code' => 'TX1231794774' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_of_birth' => '1995-01-0' . (($i % 9) + 1),
                'full_address' => $addr['full_address'],
                'address_detail' => 'Khu vực giao hàng chính',
                'latitude' => $addr['latitude'],
                'longitude' => $addr['longitude'],
                'province_code' => null,
                'district_code' => null,
                'ward_code' => null,
            ]);
        }

        // ════════════════════════════════════════════
        // HUB: Bưu điện Quán Thánh
        // ID: 1235777136 | Hoài Đức | (21.040436, 105.847041)
        // ════════════════════════════════════════════

        $hubUser = User::firstOrCreate(
            ['email' => 'hub_1235777136@example.com'],
            [
                'phone' => '024' . str_pad('1235777136', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu điện Quán Thánh',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '1235777136',
                'hub_latitude' => '21.040436',
                'hub_longitude' => '105.847041',
                'hub_address' => 'Phố Quán Thánh',
            ]
        );

        $driverAddresses_1235777136 = [
            ['full_address' => 'Phố Quán Thánh', 'latitude' => 21.042042, 'longitude' => 105.843829],
            ['full_address' => 'restaurant', 'latitude' => 20.999404, 'longitude' => 105.841523],
            ['full_address' => 'Chè Hai Lúa', 'latitude' => 21.008857, 'longitude' => 105.864626],
        ];

        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_1235777136_{$i}@example.com";
            $phone = "092" . substr(str_pad('1235777136', 9, '0', STR_PAD_LEFT), -6) . $i;
            $fullName = "Driver Bưu điện Quán Thánh #{$i}";

            $driverUser = User::firstOrCreate(['email' => $email], [
                'phone' => $phone,
                'full_name' => $fullName,
                'role' => 'driver',
                'status' => 'active',
                'password_hash' => Hash::make('123456'),
            ]);

            DriverProfile::updateOrCreate(['user_id' => $driverUser->id], [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'province_code' => 1,
                'post_office_id' => '1235777136',
                'post_office_name' => 'Bưu điện Quán Thánh',
                'post_office_address' => 'Phố Quán Thánh',
                'post_office_lat' => '21.040436',
                'post_office_lng' => '105.847041',
                'post_office_phone' => '0241234567',
                'vehicle_type' => 'Xe máy',
                'license_number' => str_repeat($i, 8),
                'license_image' => 'license_image.png',
                'identity_image' => 'identity_image.png',
                'experience' => rand(1, 5) . ' năm',
                'status' => 'approved',
                'approved_at' => Carbon::now(),
            ]);

            $addr = $driverAddresses_1235777136[$i - 1];
            UserInfo::updateOrCreate(['user_id' => $driverUser->id], [
                'national_id' => '1235777136' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'tax_code' => 'TX1235777136' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_of_birth' => '1995-01-0' . (($i % 9) + 1),
                'full_address' => $addr['full_address'],
                'address_detail' => 'Khu vực giao hàng chính',
                'latitude' => $addr['latitude'],
                'longitude' => $addr['longitude'],
                'province_code' => null,
                'district_code' => null,
                'ward_code' => null,
            ]);
        }

        // ════════════════════════════════════════════
        // HUB: Bưu điện Xã Đàn
        // ID: 1236491877 | Hoài Đức | (21.015186, 105.834948)
        // ════════════════════════════════════════════

        $hubUser = User::firstOrCreate(
            ['email' => 'hub_1236491877@example.com'],
            [
                'phone' => '024' . str_pad('1236491877', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu điện Xã Đàn',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '1236491877',
                'hub_latitude' => '21.015186',
                'hub_longitude' => '105.834948',
                'hub_address' => 'Phố Trung Phụng',
            ]
        );

        $driverAddresses_1236491877 = [
            ['full_address' => 'Phố Trung Hoà', 'latitude' => 21.015356, 'longitude' => 105.80111],
            ['full_address' => 'Phố Phan Huy Ích', 'latitude' => 21.041391, 'longitude' => 105.845743],
            ['full_address' => 'Phố Nguyễn Thái Học', 'latitude' => 21.028891, 'longitude' => 105.841435],
        ];

        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_1236491877_{$i}@example.com";
            $phone = "092" . substr(str_pad('1236491877', 9, '0', STR_PAD_LEFT), -6) . $i;
            $fullName = "Driver Bưu điện Xã Đàn #{$i}";

            $driverUser = User::firstOrCreate(['email' => $email], [
                'phone' => $phone,
                'full_name' => $fullName,
                'role' => 'driver',
                'status' => 'active',
                'password_hash' => Hash::make('123456'),
            ]);

            DriverProfile::updateOrCreate(['user_id' => $driverUser->id], [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'province_code' => 1,
                'post_office_id' => '1236491877',
                'post_office_name' => 'Bưu điện Xã Đàn',
                'post_office_address' => 'Phố Trung Phụng',
                'post_office_lat' => '21.015186',
                'post_office_lng' => '105.834948',
                'post_office_phone' => '0241234567',
                'vehicle_type' => 'Xe máy',
                'license_number' => str_repeat($i, 8),
                'license_image' => 'license_image.png',
                'identity_image' => 'identity_image.png',
                'experience' => rand(1, 5) . ' năm',
                'status' => 'approved',
                'approved_at' => Carbon::now(),
            ]);

            $addr = $driverAddresses_1236491877[$i - 1];
            UserInfo::updateOrCreate(['user_id' => $driverUser->id], [
                'national_id' => '1236491877' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'tax_code' => 'TX1236491877' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_of_birth' => '1995-01-0' . (($i % 9) + 1),
                'full_address' => $addr['full_address'],
                'address_detail' => 'Khu vực giao hàng chính',
                'latitude' => $addr['latitude'],
                'longitude' => $addr['longitude'],
                'province_code' => null,
                'district_code' => null,
                'ward_code' => null,
            ]);
        }

        // ════════════════════════════════════════════
        // HUB: Bưu cục Đông Anh
        // ID: 283807305 | Hoài Đức | (21.141394, 105.846251)
        // ════════════════════════════════════════════

        $hubUser = User::firstOrCreate(
            ['email' => 'hub_283807305@example.com'],
            [
                'phone' => '024' . str_pad('283807305', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu cục Đông Anh',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '283807305',
                'hub_latitude' => '21.141394',
                'hub_longitude' => '105.846251',
                'hub_address' => 'Đường Cao Lỗ',
            ]
        );

        $driverAddresses_283807305 = [
            ['full_address' => 'Agribank', 'latitude' => 21.111167, 'longitude' => 105.876815],
            ['full_address' => 'Trường Trung cấp Y - Dược Cộng đồng Hà Nội', 'latitude' => 21.111901, 'longitude' => 105.832821],
            ['full_address' => 'Trường cao đẳng nghề Việt Nam-Hàn Quốc Thành phố Hà Nội', 'latitude' => 21.175135, 'longitude' => 105.856856],
        ];

        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_283807305_{$i}@example.com";
            $phone = "092" . substr(str_pad('283807305', 9, '0', STR_PAD_LEFT), -6) . $i;
            $fullName = "Driver Bưu cục Đông Anh #{$i}";

            $driverUser = User::firstOrCreate(['email' => $email], [
                'phone' => $phone,
                'full_name' => $fullName,
                'role' => 'driver',
                'status' => 'active',
                'password_hash' => Hash::make('123456'),
            ]);

            DriverProfile::updateOrCreate(['user_id' => $driverUser->id], [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'province_code' => 1,
                'post_office_id' => '283807305',
                'post_office_name' => 'Bưu cục Đông Anh',
                'post_office_address' => 'Đường Cao Lỗ',
                'post_office_lat' => '21.141394',
                'post_office_lng' => '105.846251',
                'post_office_phone' => '0241234567',
                'vehicle_type' => 'Xe máy',
                'license_number' => str_repeat($i, 8),
                'license_image' => 'license_image.png',
                'identity_image' => 'identity_image.png',
                'experience' => rand(1, 5) . ' năm',
                'status' => 'approved',
                'approved_at' => Carbon::now(),
            ]);

            $addr = $driverAddresses_283807305[$i - 1];
            UserInfo::updateOrCreate(['user_id' => $driverUser->id], [
                'national_id' => '283807305' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'tax_code' => 'TX283807305' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_of_birth' => '1995-01-0' . (($i % 9) + 1),
                'full_address' => $addr['full_address'],
                'address_detail' => 'Khu vực giao hàng chính',
                'latitude' => $addr['latitude'],
                'longitude' => $addr['longitude'],
                'province_code' => null,
                'district_code' => null,
                'ward_code' => null,
            ]);
        }

        // ════════════════════════════════════════════
        // HUB: Bưu điện Mỹ Đình 2
        // ID: 1318588365 | Hoài Đức | (21.031929, 105.765388)
        // ════════════════════════════════════════════

        $hubUser = User::firstOrCreate(
            ['email' => 'hub_1318588365@example.com'],
            [
                'phone' => '024' . str_pad('1318588365', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu điện Mỹ Đình 2',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '1318588365',
                'hub_latitude' => '21.031929',
                'hub_longitude' => '105.765388',
                'hub_address' => 'Phố Nguyễn Cơ Thạch',
            ]
        );

        $driverAddresses_1318588365 = [
            ['full_address' => 'Phố Đặng Thùy Trâm', 'latitude' => 21.045431, 'longitude' => 105.784229],
            ['full_address' => 'Bún chả', 'latitude' => 21.003806, 'longitude' => 105.779408],
            ['full_address' => 'Bún cá Việt Nam', 'latitude' => 21.047237, 'longitude' => 105.78429],
        ];

        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_1318588365_{$i}@example.com";
            $phone = "092" . substr(str_pad('1318588365', 9, '0', STR_PAD_LEFT), -6) . $i;
            $fullName = "Driver Bưu điện Mỹ Đình 2 #{$i}";

            $driverUser = User::firstOrCreate(['email' => $email], [
                'phone' => $phone,
                'full_name' => $fullName,
                'role' => 'driver',
                'status' => 'active',
                'password_hash' => Hash::make('123456'),
            ]);

            DriverProfile::updateOrCreate(['user_id' => $driverUser->id], [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'province_code' => 1,
                'post_office_id' => '1318588365',
                'post_office_name' => 'Bưu điện Mỹ Đình 2',
                'post_office_address' => 'Phố Nguyễn Cơ Thạch',
                'post_office_lat' => '21.031929',
                'post_office_lng' => '105.765388',
                'post_office_phone' => '0241234567',
                'vehicle_type' => 'Xe máy',
                'license_number' => str_repeat($i, 8),
                'license_image' => 'license_image.png',
                'identity_image' => 'identity_image.png',
                'experience' => rand(1, 5) . ' năm',
                'status' => 'approved',
                'approved_at' => Carbon::now(),
            ]);

            $addr = $driverAddresses_1318588365[$i - 1];
            UserInfo::updateOrCreate(['user_id' => $driverUser->id], [
                'national_id' => '1318588365' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'tax_code' => 'TX1318588365' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_of_birth' => '1995-01-0' . (($i % 9) + 1),
                'full_address' => $addr['full_address'],
                'address_detail' => 'Khu vực giao hàng chính',
                'latitude' => $addr['latitude'],
                'longitude' => $addr['longitude'],
                'province_code' => null,
                'district_code' => null,
                'ward_code' => null,
            ]);
        }

        // ════════════════════════════════════════════
        // HUB: Bưu cục Đan Phượng
        // ID: 1269068039 | Đan Phượng | (21.226202, 105.762662)
        // ════════════════════════════════════════════

        $hubUser = User::firstOrCreate(
            ['email' => 'hub_1269068039@example.com'],
            [
                'phone' => '024' . str_pad('1269068039', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu cục Đan Phượng',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '1269068039',
                'hub_latitude' => '21.226202',
                'hub_longitude' => '105.762662',
                'hub_address' => 'Đan Phượng, Hà Nội',
            ]
        );

        $driverAddresses_1269068039 = [
            ['full_address' => 'restaurant', 'latitude' => 21.186531, 'longitude' => 105.774914],
            ['full_address' => 'El Domo restaurant and bar', 'latitude' => 21.21812, 'longitude' => 105.79273],
            ['full_address' => 'Trường Trung học cơ sở Tân Dân', 'latitude' => 21.24321, 'longitude' => 105.741295],
        ];

        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_1269068039_{$i}@example.com";
            $phone = "092" . substr(str_pad('1269068039', 9, '0', STR_PAD_LEFT), -6) . $i;
            $fullName = "Driver Bưu cục Đan Phượng #{$i}";

            $driverUser = User::firstOrCreate(['email' => $email], [
                'phone' => $phone,
                'full_name' => $fullName,
                'role' => 'driver',
                'status' => 'active',
                'password_hash' => Hash::make('123456'),
            ]);

            DriverProfile::updateOrCreate(['user_id' => $driverUser->id], [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'province_code' => 1,
                'post_office_id' => '1269068039',
                'post_office_name' => 'Bưu cục Đan Phượng',
                'post_office_address' => 'Đan Phượng, Hà Nội',
                'post_office_lat' => '21.226202',
                'post_office_lng' => '105.762662',
                'post_office_phone' => '0241234567',
                'vehicle_type' => 'Xe máy',
                'license_number' => str_repeat($i, 8),
                'license_image' => 'license_image.png',
                'identity_image' => 'identity_image.png',
                'experience' => rand(1, 5) . ' năm',
                'status' => 'approved',
                'approved_at' => Carbon::now(),
            ]);

            $addr = $driverAddresses_1269068039[$i - 1];
            UserInfo::updateOrCreate(['user_id' => $driverUser->id], [
                'national_id' => '1269068039' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'tax_code' => 'TX1269068039' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_of_birth' => '1995-01-0' . (($i % 9) + 1),
                'full_address' => $addr['full_address'],
                'address_detail' => 'Khu vực giao hàng chính',
                'latitude' => $addr['latitude'],
                'longitude' => $addr['longitude'],
                'province_code' => null,
                'district_code' => null,
                'ward_code' => null,
            ]);
        }

        // ════════════════════════════════════════════
        // HUB: Bưu diện Trung tâm Mê Linh
        // ID: 1271977348 | Đan Phượng | (21.186242, 105.721405)
        // ════════════════════════════════════════════

        $hubUser = User::firstOrCreate(
            ['email' => 'hub_1271977348@example.com'],
            [
                'phone' => '024' . str_pad('1271977348', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu diện Trung tâm Mê Linh',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '1271977348',
                'hub_latitude' => '21.186242',
                'hub_longitude' => '105.721405',
                'hub_address' => 'Đan Phượng, Hà Nội',
            ]
        );

        $driverAddresses_1271977348 = [
            ['full_address' => 'Trường Trung học cơ sở Trưng Vương', 'latitude' => 21.213502, 'longitude' => 105.718386],
            ['full_address' => 'Trường Tiểu Học Mê Linh', 'latitude' => 21.164167, 'longitude' => 105.730772],
            ['full_address' => 'Trường Trung học cơ sở Trưng Vương - Mê Linh', 'latitude' => 21.177804, 'longitude' => 105.726895],
        ];

        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_1271977348_{$i}@example.com";
            $phone = "092" . substr(str_pad('1271977348', 9, '0', STR_PAD_LEFT), -6) . $i;
            $fullName = "Driver Bưu diện Trung tâm Mê Linh #{$i}";

            $driverUser = User::firstOrCreate(['email' => $email], [
                'phone' => $phone,
                'full_name' => $fullName,
                'role' => 'driver',
                'status' => 'active',
                'password_hash' => Hash::make('123456'),
            ]);

            DriverProfile::updateOrCreate(['user_id' => $driverUser->id], [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'province_code' => 1,
                'post_office_id' => '1271977348',
                'post_office_name' => 'Bưu diện Trung tâm Mê Linh',
                'post_office_address' => 'Đan Phượng, Hà Nội',
                'post_office_lat' => '21.186242',
                'post_office_lng' => '105.721405',
                'post_office_phone' => '0241234567',
                'vehicle_type' => 'Xe máy',
                'license_number' => str_repeat($i, 8),
                'license_image' => 'license_image.png',
                'identity_image' => 'identity_image.png',
                'experience' => rand(1, 5) . ' năm',
                'status' => 'approved',
                'approved_at' => Carbon::now(),
            ]);

            $addr = $driverAddresses_1271977348[$i - 1];
            UserInfo::updateOrCreate(['user_id' => $driverUser->id], [
                'national_id' => '1271977348' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'tax_code' => 'TX1271977348' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_of_birth' => '1995-01-0' . (($i % 9) + 1),
                'full_address' => $addr['full_address'],
                'address_detail' => 'Khu vực giao hàng chính',
                'latitude' => $addr['latitude'],
                'longitude' => $addr['longitude'],
                'province_code' => null,
                'district_code' => null,
                'ward_code' => null,
            ]);
        }

        // ════════════════════════════════════════════
        // HUB: Bưu cục Đông Anh
        // ID: 434619265 | Đan Phượng | (21.141394, 105.846251)
        // ════════════════════════════════════════════

        $hubUser = User::firstOrCreate(
            ['email' => 'hub_434619265@example.com'],
            [
                'phone' => '024' . str_pad('434619265', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu cục Đông Anh',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '434619265',
                'hub_latitude' => '21.141394',
                'hub_longitude' => '105.846251',
                'hub_address' => 'Đường Cao Lỗ',
            ]
        );

        $driverAddresses_434619265 = [
            ['full_address' => 'Trường Trung cấp Y - Dược Cộng đồng Hà Nội', 'latitude' => 21.111901, 'longitude' => 105.832821],
            ['full_address' => 'Agribank', 'latitude' => 21.111167, 'longitude' => 105.876815],
            ['full_address' => 'Trường cao đẳng nghề Việt Nam-Hàn Quốc Thành phố Hà Nội', 'latitude' => 21.175135, 'longitude' => 105.856856],
        ];

        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_434619265_{$i}@example.com";
            $phone = "092" . substr(str_pad('434619265', 9, '0', STR_PAD_LEFT), -6) . $i;
            $fullName = "Driver Bưu cục Đông Anh #{$i}";

            $driverUser = User::firstOrCreate(['email' => $email], [
                'phone' => $phone,
                'full_name' => $fullName,
                'role' => 'driver',
                'status' => 'active',
                'password_hash' => Hash::make('123456'),
            ]);

            DriverProfile::updateOrCreate(['user_id' => $driverUser->id], [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'province_code' => 1,
                'post_office_id' => '434619265',
                'post_office_name' => 'Bưu cục Đông Anh',
                'post_office_address' => 'Đường Cao Lỗ',
                'post_office_lat' => '21.141394',
                'post_office_lng' => '105.846251',
                'post_office_phone' => '0241234567',
                'vehicle_type' => 'Xe máy',
                'license_number' => str_repeat($i, 8),
                'license_image' => 'license_image.png',
                'identity_image' => 'identity_image.png',
                'experience' => rand(1, 5) . ' năm',
                'status' => 'approved',
                'approved_at' => Carbon::now(),
            ]);

            $addr = $driverAddresses_434619265[$i - 1];
            UserInfo::updateOrCreate(['user_id' => $driverUser->id], [
                'national_id' => '434619265' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'tax_code' => 'TX434619265' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_of_birth' => '1995-01-0' . (($i % 9) + 1),
                'full_address' => $addr['full_address'],
                'address_detail' => 'Khu vực giao hàng chính',
                'latitude' => $addr['latitude'],
                'longitude' => $addr['longitude'],
                'province_code' => null,
                'district_code' => null,
                'ward_code' => null,
            ]);
        }

        // ════════════════════════════════════════════
        // HUB: Bưu Điện Thị Xã Sơn Tây
        // ID: 740984893 | Sơn Tây | (21.138348, 105.506722)
        // ════════════════════════════════════════════

        $hubUser = User::firstOrCreate(
            ['email' => 'hub_740984893@example.com'],
            [
                'phone' => '024' . str_pad('740984893', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu Điện Thị Xã Sơn Tây',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '740984893',
                'hub_latitude' => '21.138348',
                'hub_longitude' => '105.506722',
                'hub_address' => 'Sơn Tây, Hà Nội',
            ]
        );

        $driverAddresses_740984893 = [
            ['full_address' => 'Đường Phú Hà', 'latitude' => 21.143591, 'longitude' => 105.502483],
            ['full_address' => 'Quán Lẩu Bò', 'latitude' => 21.143176, 'longitude' => 105.531852],
            ['full_address' => 'Phố Đinh Tiên Hoàng', 'latitude' => 21.142984, 'longitude' => 105.503281],
        ];

        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_740984893_{$i}@example.com";
            $phone = "092" . substr(str_pad('740984893', 9, '0', STR_PAD_LEFT), -6) . $i;
            $fullName = "Driver Bưu Điện Thị Xã Sơn Tây #{$i}";

            $driverUser = User::firstOrCreate(['email' => $email], [
                'phone' => $phone,
                'full_name' => $fullName,
                'role' => 'driver',
                'status' => 'active',
                'password_hash' => Hash::make('123456'),
            ]);

            DriverProfile::updateOrCreate(['user_id' => $driverUser->id], [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'province_code' => 1,
                'post_office_id' => '740984893',
                'post_office_name' => 'Bưu Điện Thị Xã Sơn Tây',
                'post_office_address' => 'Sơn Tây, Hà Nội',
                'post_office_lat' => '21.138348',
                'post_office_lng' => '105.506722',
                'post_office_phone' => '0241234567',
                'vehicle_type' => 'Xe máy',
                'license_number' => str_repeat($i, 8),
                'license_image' => 'license_image.png',
                'identity_image' => 'identity_image.png',
                'experience' => rand(1, 5) . ' năm',
                'status' => 'approved',
                'approved_at' => Carbon::now(),
            ]);

            $addr = $driverAddresses_740984893[$i - 1];
            UserInfo::updateOrCreate(['user_id' => $driverUser->id], [
                'national_id' => '740984893' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'tax_code' => 'TX740984893' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_of_birth' => '1995-01-0' . (($i % 9) + 1),
                'full_address' => $addr['full_address'],
                'address_detail' => 'Khu vực giao hàng chính',
                'latitude' => $addr['latitude'],
                'longitude' => $addr['longitude'],
                'province_code' => null,
                'district_code' => null,
                'ward_code' => null,
            ]);
        }

        // ════════════════════════════════════════════
        // HUB: Bưu Điện Viettel Tây Đằng
        // ID: 567436844 | Sơn Tây | (21.187022, 105.438665)
        // ════════════════════════════════════════════

        $hubUser = User::firstOrCreate(
            ['email' => 'hub_567436844@example.com'],
            [
                'phone' => '024' . str_pad('567436844', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu Điện Viettel Tây Đằng',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '567436844',
                'hub_latitude' => '21.187022',
                'hub_longitude' => '105.438665',
                'hub_address' => 'Sơn Tây, Hà Nội',
            ]
        );

        $driverAddresses_567436844 = [
            ['full_address' => 'Hương Quỳnh - Trâu Ngon 36 Món', 'latitude' => 21.21493, 'longitude' => 105.403835],
            ['full_address' => 'restaurant', 'latitude' => 21.20779, 'longitude' => 105.41518],
            ['full_address' => 'Gần Bưu Điện Viettel Tây Đằng', 'latitude' => 21.190304, 'longitude' => 105.441947],
        ];

        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_567436844_{$i}@example.com";
            $phone = "092" . substr(str_pad('567436844', 9, '0', STR_PAD_LEFT), -6) . $i;
            $fullName = "Driver Bưu Điện Viettel Tây Đằng #{$i}";

            $driverUser = User::firstOrCreate(['email' => $email], [
                'phone' => $phone,
                'full_name' => $fullName,
                'role' => 'driver',
                'status' => 'active',
                'password_hash' => Hash::make('123456'),
            ]);

            DriverProfile::updateOrCreate(['user_id' => $driverUser->id], [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'province_code' => 1,
                'post_office_id' => '567436844',
                'post_office_name' => 'Bưu Điện Viettel Tây Đằng',
                'post_office_address' => 'Sơn Tây, Hà Nội',
                'post_office_lat' => '21.187022',
                'post_office_lng' => '105.438665',
                'post_office_phone' => '0241234567',
                'vehicle_type' => 'Xe máy',
                'license_number' => str_repeat($i, 8),
                'license_image' => 'license_image.png',
                'identity_image' => 'identity_image.png',
                'experience' => rand(1, 5) . ' năm',
                'status' => 'approved',
                'approved_at' => Carbon::now(),
            ]);

            $addr = $driverAddresses_567436844[$i - 1];
            UserInfo::updateOrCreate(['user_id' => $driverUser->id], [
                'national_id' => '567436844' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'tax_code' => 'TX567436844' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_of_birth' => '1995-01-0' . (($i % 9) + 1),
                'full_address' => $addr['full_address'],
                'address_detail' => 'Khu vực giao hàng chính',
                'latitude' => $addr['latitude'],
                'longitude' => $addr['longitude'],
                'province_code' => null,
                'district_code' => null,
                'ward_code' => null,
            ]);
        }

        // ════════════════════════════════════════════
        // HUB: Bưu điện huyện Ba Vì
        // ID: 446332801 | Sơn Tây | (21.199278, 105.423484)
        // ════════════════════════════════════════════

        $hubUser = User::firstOrCreate(
            ['email' => 'hub_446332801@example.com'],
            [
                'phone' => '024' . str_pad('446332801', 7, '0', STR_PAD_LEFT),
                'full_name' => 'Bưu điện huyện Ba Vì',
                'role' => 'hub',
                'status' => 1,
                'password_hash' => Hash::make('123456'),
            ]
        );

        DB::table('hubs')->updateOrInsert(
            ['user_id' => $hubUser->id],
            [
                'post_office_id' => '446332801',
                'hub_latitude' => '21.199278',
                'hub_longitude' => '105.423484',
                'hub_address' => 'Sơn Tây, Hà Nội',
            ]
        );

        $driverAddresses_446332801 = [
            ['full_address' => 'Hương Quỳnh - Trâu Ngon 36 Món', 'latitude' => 21.21493, 'longitude' => 105.403835],
            ['full_address' => 'restaurant', 'latitude' => 21.20779, 'longitude' => 105.41518],
            ['full_address' => 'Gần Bưu điện huyện Ba Vì', 'latitude' => 21.198447, 'longitude' => 105.422653],
        ];

        for ($i = 1; $i <= 3; $i++) {
            $email = "driver_hub_446332801_{$i}@example.com";
            $phone = "092" . substr(str_pad('446332801', 9, '0', STR_PAD_LEFT), -6) . $i;
            $fullName = "Driver Bưu điện huyện Ba Vì #{$i}";

            $driverUser = User::firstOrCreate(['email' => $email], [
                'phone' => $phone,
                'full_name' => $fullName,
                'role' => 'driver',
                'status' => 'active',
                'password_hash' => Hash::make('123456'),
            ]);

            DriverProfile::updateOrCreate(['user_id' => $driverUser->id], [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'province_code' => 1,
                'post_office_id' => '446332801',
                'post_office_name' => 'Bưu điện huyện Ba Vì',
                'post_office_address' => 'Sơn Tây, Hà Nội',
                'post_office_lat' => '21.199278',
                'post_office_lng' => '105.423484',
                'post_office_phone' => '0241234567',
                'vehicle_type' => 'Xe máy',
                'license_number' => str_repeat($i, 8),
                'license_image' => 'license_image.png',
                'identity_image' => 'identity_image.png',
                'experience' => rand(1, 5) . ' năm',
                'status' => 'approved',
                'approved_at' => Carbon::now(),
            ]);

            $addr = $driverAddresses_446332801[$i - 1];
            UserInfo::updateOrCreate(['user_id' => $driverUser->id], [
                'national_id' => '446332801' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'tax_code' => 'TX446332801' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'date_of_birth' => '1995-01-0' . (($i % 9) + 1),
                'full_address' => $addr['full_address'],
                'address_detail' => 'Khu vực giao hàng chính',
                'latitude' => $addr['latitude'],
                'longitude' => $addr['longitude'],
                'province_code' => null,
                'district_code' => null,
                'ward_code' => null,
            ]);
        }

    }
}

// Lệnh chạy nè: php artisan db:seed --class=NewPostOfficeSeeder
