<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'full_name'     => $this->faker->name(),
            'email'         => $this->faker->unique()->safeEmail(),
            'phone'         => $this->faker->unique()->numerify('09########'),
            'password_hash' => Hash::make('123456'), // mặc định pass = "password"
            'role'          => 'customer',
            'status'        => 'active',
            'avatar_url'    => $this->faker->imageUrl(200, 200, 'people'),
            'last_login_at' => now(),
        ];
    }

    public function admin()
    {
        return $this->state(fn () => [
            'full_name' => 'Admin User',
            'email'     => 'admin@example.com',
            'phone'     => '0900000001',
            'role'      => 'admin',
            'status'    => 'active',
        ]);
    }

    public function driver()
    {
        return $this->state(fn () => [
            'full_name' => 'Driver User',
            'email'     => 'driver@example.com',
            'phone'     => '0900000002',
            'role'      => 'driver',
            'status'    => 'active',
        ]);
    }

    public function customer()
    {
        return $this->state(fn () => [
            'full_name' => 'Customer User',
            'email'     => 'customer@example.com',
            'phone'     => '0900000003',
            'role'      => 'customer',
            'status'    => 'active',
        ]);
    }
}
