<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Notification;
use App\Models\User;

class NotificationFactory extends Factory
{
    protected $model = Notification::class;

    public function definition()
    {
        return [
            'recipient_id' => User::factory(),
            'message' => $this->faker->sentence(10),
            'date' => $this->faker->dateTimeBetween('-1 week', 'now'),
            'is_read' => $this->faker->boolean(30), // 30% chance of being read
        ];
    }

    public function read()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_read' => true,
            ];
        });
    }

    public function unread()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_read' => false,
            ];
        });
    }
}