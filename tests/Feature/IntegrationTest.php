<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Course;
use App\Models\Category;
use App\Models\Enrollment;
use App\Models\JoinRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

class IntegrationTests extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function complete_course_enrollment_workflow()
    {
        // Setup
        $admin = User::factory()->admin()->create();
        $instructor = User::factory()->instructor()->create();
        $student = User::factory()->student()->create();
        $category = Category::factory()->create();

        // Admin creates a course
        $courseData = [
            'title' => 'Laravel Development',
            'description' => 'Learn Laravel from scratch',
            'price' => 199.99,
            'category_id' => $category->id,
            'instructor_id' => $instructor->id,
            'is_closed' => false,
        ];

        $response = $this->actingAs($admin)
            ->post(route('admin.courses.store'), $courseData);

        $response->assertRedirect(route('admin.courses.index'));
        $this->assertDatabaseHas('courses', ['title' => 'Laravel Development']);

        $course = Course::where('title', 'Laravel Development')->first();

        // Student requests to join
        JoinRequest::create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'request_date' => now(),
            'status' => 'PENDING'
        ]);

        $this->assertDatabaseHas('join_requests', [
            'student_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'PENDING'
        ]);

        // Admin approves and enrolls student
        $joinRequest = JoinRequest::first();
        $joinRequest->update(['status' => 'APPROVED']);

        Enrollment::create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'enrolled_at' => now(),
            'progress' => 0
        ]);

        $this->assertDatabaseHas('enrollments', [
            'student_id' => $student->id,
            'course_id' => $course->id
        ]);

        // Verify relationships
        $this->assertCount(1, $student->courses);
        $this->assertCount(1, $course->students);
    }

    /** @test */
    public function admin_can_manage_complete_user_lifecycle()
    {
        $admin = User::factory()->admin()->create();

        // Create user
        $userData = [
            'name' => 'newstudent',
            'full_name' => 'New Student',
            'email' => 'student@test.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'role' => 'STUDENT',
        ];

        $this->actingAs($admin)
            ->post(route('admin.users.store'), $userData);

        $user = User::where('email', 'student@test.com')->first();
        $this->assertNotNull($user);

        // Update user to instructor
        $updateData = [
            'name' => 'newinstructor',
            'full_name' => 'New Instructor',
            'email' => 'student@test.com',
            'role' => 'INSTRUCTOR',
        ];

        $this->actingAs($admin)
            ->put(route('admin.users.update', $user->id), $updateData);

        $user->refresh();
        $this->assertEquals('INSTRUCTOR', $user->role);
        $this->assertTrue($user->isInstructor());

        // Delete user (should succeed as no enrollments)
        $this->actingAs($admin)
            ->delete(route('admin.users.delete', $user->id));

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    /** @test */
    public function course_with_enrollments_cannot_be_deleted()
    {
        $admin = User::factory()->admin()->create();
        $instructor = User::factory()->instructor()->create();
        $student = User::factory()->student()->create();
        $category = Category::factory()->create();

        $course = Course::factory()->create([
            'instructor_id' => $instructor->id,
            'category_id' => $category->id
        ]);

        // Enroll student
        Enrollment::create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'enrolled_at' => now()
        ]);

        // Try to delete course
        $response = $this->actingAs($admin)
            ->delete(route('admin.courses.delete', $course->id));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('courses', ['id' => $course->id]);
    }

    /** @test */
    public function category_with_courses_cannot_be_deleted()
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();
        $instructor = User::factory()->instructor()->create();

        Course::factory()->create([
            'category_id' => $category->id,
            'instructor_id' => $instructor->id
        ]);

        $response = $this->actingAs($admin)
            ->delete(route('admin.categories.delete', $category->id));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    /** @test */
    public function instructor_can_have_multiple_courses()
    {
        $instructor = User::factory()->instructor()->create();
        $category = Category::factory()->create();

        $course1 = Course::factory()->create([
            'instructor_id' => $instructor->id,
            'category_id' => $category->id
        ]);

        $course2 = Course::factory()->create([
            'instructor_id' => $instructor->id,
            'category_id' => $category->id
        ]);

        $this->assertCount(2, Course::where('instructor_id', $instructor->id)->get());
    }

    /** @test */
    public function student_can_enroll_in_multiple_courses()
    {
        $student = User::factory()->student()->create();
        $instructor = User::factory()->instructor()->create();
        $category = Category::factory()->create();

        $course1 = Course::factory()->create([
            'instructor_id' => $instructor->id,
            'category_id' => $category->id
        ]);

        $course2 = Course::factory()->create([
            'instructor_id' => $instructor->id,
            'category_id' => $category->id
        ]);

        Enrollment::create([
            'student_id' => $student->id,
            'course_id' => $course1->id,
            'enrolled_at' => now()
        ]);

        Enrollment::create([
            'student_id' => $student->id,
            'course_id' => $course2->id,
            'enrolled_at' => now()
        ]);

        $this->assertCount(2, $student->courses);
    }

    /** @test */
    public function course_can_have_multiple_students()
    {
        $course = Course::factory()->create();
        $student1 = User::factory()->student()->create();
        $student2 = User::factory()->student()->create();
        $student3 = User::factory()->student()->create();

        foreach ([$student1, $student2, $student3] as $student) {
            Enrollment::create([
                'student_id' => $student->id,
                'course_id' => $course->id,
                'enrolled_at' => now()
            ]);
        }

        $this->assertCount(3, $course->students);
    }

    /** @test */
    public function closed_course_workflow()
    {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create(['is_closed' => false]);

        $this->assertFalse($course->is_closed);

        // Close the course
        $this->actingAs($admin)
            ->put(route('admin.courses.update', $course->id), [
                'title' => $course->title,
                'description' => $course->description,
                'price' => $course->price,
                'category_id' => $course->category_id,
                'instructor_id' => $course->instructor_id,
                'is_closed' => true,
            ]);

        $course->refresh();
        $this->assertTrue($course->is_closed);
    }

    /** @test */
    public function validation_prevents_invalid_data()
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();
        $instructor = User::factory()->instructor()->create();

        // Invalid price
        $response = $this->actingAs($admin)
            ->post(route('admin.courses.store'), [
                'title' => 'Test Course',
                'description' => 'Test description',
                'price' => -100,
                'category_id' => $category->id,
                'instructor_id' => $instructor->id,
            ]);

        $response->assertSessionHasErrors('price');

        // Invalid email
        $response = $this->actingAs($admin)
            ->post(route('admin.users.store'), [
                'name' => 'testuser',
                'full_name' => 'Test User',
                'email' => 'invalid-email',
                'password' => 'Password123!',
                'password_confirmation' => 'Password123!',
                'role' => 'STUDENT',
            ]);

        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function statistics_reflect_correct_counts()
    {
        // Create test data
        User::factory()->student()->count(5)->create();
        User::factory()->instructor()->count(3)->create();
        User::factory()->admin()->count(2)->create();

        $category = Category::factory()->create();
        $instructor = User::where('role', 'INSTRUCTOR')->first();

        Course::factory()->count(4)->create([
            'instructor_id' => $instructor->id,
            'category_id' => $category->id,
            'is_closed' => false
        ]);

        Course::factory()->count(2)->create([
            'instructor_id' => $instructor->id,
            'category_id' => $category->id,
            'is_closed' => true
        ]);

        $admin = User::where('role', 'ADMIN')->first();

        $response = $this->actingAs($admin)
            ->get(route('admin.statistics'));

        $response->assertStatus(200);
        $response->assertViewHas('totalUsers', 10);
        $response->assertViewHas('students', 5);
        $response->assertViewHas('instructors', 3);
        $response->assertViewHas('activeCourses', 4);
        $response->assertViewHas('closedCourses', 2);
    }
}