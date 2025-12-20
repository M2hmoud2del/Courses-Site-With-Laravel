<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Course;
use App\Models\User;
use App\Models\JoinRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentBackendTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_browse_courses()
    {
        $student = User::factory()->create(['role' => 'STUDENT']);
        $category = Category::create(['name' => 'Tech', 'description' => 'Tech Courses']);
        $instructor = User::factory()->create(['role' => 'INSTRUCTOR']);
        
        Course::create([
            'title' => 'Learn Laravel',
            'description' => 'Best course',
            'price' => 100,
            'instructor_id' => $instructor->id,
            'category_id' => $category->id,
            'is_closed' => false,
        ]);

        $response = $this->actingAs($student)->getJson('/api/courses');

        $response->assertStatus(200)
            ->assertJsonStructure([
                '*' => ['id', 'title', 'instructor', 'category']
            ]);
    }

    public function test_student_can_request_to_join()
    {
        $student = User::factory()->create(['role' => 'STUDENT']);
        $category = Category::create(['name' => 'Tech']);
        $instructor = User::factory()->create(['role' => 'INSTRUCTOR']);
        
        $course = Course::create([
            'title' => 'Learn Laravel',
            'description' => 'Best course',
            'price' => 100,
            'instructor_id' => $instructor->id,
            'category_id' => $category->id,
            'is_closed' => false,
        ]);

        $response = $this->actingAs($student)->postJson("/api/courses/{$course->id}/join");

        $response->assertStatus(201);
        
        $this->assertDatabaseHas('join_requests', [
            'student_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'PENDING',
        ]);
    }
}
