<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\JoinRequest;
use App\Models\User;
use App\Models\Course;

class JoinRequestFactory extends Factory
{
    protected $model = JoinRequest::class;

    public function definition()
    {
        return [
            'student_id' => User::factory()->state(['role' => 'STUDENT']),
            'course_id' => Course::factory(),
            'request_date' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'status' => $this->faker->randomElement(['PENDING', 'APPROVED', 'REJECTED']),
        ];
    }

    public function pending()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'PENDING',
            ];
        });
    }

    public function approved()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'APPROVED',
            ];
        });
    }

    public function rejected()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'REJECTED',
            ];
        });
    }
}