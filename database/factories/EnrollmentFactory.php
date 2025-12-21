<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Enrollment;
use App\Models\User;
use App\Models\Course;

class EnrollmentFactory extends Factory
{
    protected $model = Enrollment::class;

    public function definition()
    {
        return [
            'student_id' => User::factory()->state(['role' => 'STUDENT']),
            'course_id' => Course::factory(),
            'enrolled_at' => $this->faker->dateTimeBetween('-6 months', 'now'),
        ];
    }

    public function withProgress($progress)
    {
        return $this->state(function (array $attributes) use ($progress) {
            return [
                'progress' => $progress,
            ];
        });
    }
}