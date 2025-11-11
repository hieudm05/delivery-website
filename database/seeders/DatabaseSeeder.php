<?php

namespace Database\Seeders;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;
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

        for ($i = 1; $i <= 10; $i++) {
            $email = "hub{$i}@example.com";
            $phone = "091000000{$i}";

            // Kiểm tra user đã tồn tại chưa (theo email hoặc phone)
            $user = User::where('email', $email)->orWhere('phone', $phone)->first();

            if (!$user) {
                $user = User::create([
                    'email' => $email,
                    'phone' => $phone,
                    'full_name' => "Hub User $i",
                    'role' => 'hub',
                    'status' => 1,
                    'password_hash' => Hash::make('123456'),
                ]);
            }

            // Tạo hub, nếu đã có user_id thì bỏ qua
            DB::table('hubs')->updateOrInsert(
                ['user_id' => $user->id],
                [
                    'post_office_id' => $hubData['post_office_id'],
                    'hub_latitude' => $hubData['hub_latitude'],
                    'hub_longitude' => $hubData['hub_longitude'],
                    'hub_address' => $hubData['hub_address'],
                ]
            );
        }
    }
}
