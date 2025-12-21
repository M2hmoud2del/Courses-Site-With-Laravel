<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\AuditLog;

class AuditLogFactory extends Factory
{
    protected $model = AuditLog::class;

    public function definition()
    {
        return [
            'actor_id' => User::factory(),
            'action' => $this->faker->randomElement(['CREATED', 'UPDATED', 'DELETED', 'VIEWED', 'APPROVED', 'REJECTED']),
            'details' => $this->faker->sentence(10),
            'timestamp' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }
}