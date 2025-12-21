<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Course;
use App\Models\User;
use App\Models\Category;

class CourseFactory extends Factory
{
    protected $model = Course::class;

    public function definition()
    {
        return [
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->paragraphs(3, true),
            'price' => $this->faker->randomFloat(2, 0, 500),
            'is_closed' => $this->faker->boolean(20), // 20% chance of being closed
            'instructor_id' => User::factory()->state(['role' => 'INSTRUCTOR']),
            'category_id' => Category::factory(),
        ];
    }

    public function closed()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_closed' => true,
            ];
        });
    }

    public function open()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_closed' => false,
            ];
        });
    }

    public function free()
    {
        return $this->state(function (array $attributes) {
            return [
                'price' => 0.00,
            ];
        });
    }

    public function expensive()
    {
        return $this->state(function (array $attributes) {
            return [
                'price' => $this->faker->randomFloat(2, 300, 1000),
            ];
        });
    }
}