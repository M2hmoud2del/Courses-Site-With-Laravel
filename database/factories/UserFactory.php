<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition()
    {
        return [
            'name' => $this->faker->userName(),
            'full_name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => Hash::make('password'), // default password
            'profile_picture' => $this->faker->imageUrl(100, 100, 'people'),
            'role' => $this->faker->randomElement(['STUDENT', 'INSTRUCTOR', 'ADMIN']),
        ];
    }

    public function student()
    {
        return $this->state(function (array $attributes) {
            return [
                'role' => 'STUDENT',
            ];
        });
    }

    public function instructor()
    {
        return $this->state(function (array $attributes) {
            return [
                'role' => 'INSTRUCTOR',
            ];
        });
    }

    public function admin()
    {
        return $this->state(function (array $attributes) {
            return [
                'role' => 'ADMIN',
            ];
        });
    }

    public function withProfilePicture($url)
    {
        return $this->state(function (array $attributes) use ($url) {
            return [
                'profile_picture' => $url,
            ];
        });
    }
}