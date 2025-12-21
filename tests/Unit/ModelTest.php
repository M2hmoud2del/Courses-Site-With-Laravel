<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Course;
use App\Models\Category;
use App\Models\Enrollment;
use App\Models\JoinRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ModelTests extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_be_created()
    {
        $user = User::factory()->create([
            'name' => 'testuser',
            'email' => 'test@example.com',
            'role' => 'STUDENT'
        ]);

        $this->assertDatabaseHas('users', [
            'name' => 'testuser',
            'email' => 'test@example.com'
        ]);
    }

    /** @test */
    public function user_has_correct_role_methods()
    {
        $student = User::factory()->student()->create();
        $instructor = User::factory()->instructor()->create();
        $admin = User::factory()->admin()->create();

        $this->assertTrue($student->isStudent());
        $this->assertFalse($student->isInstructor());
        $this->assertFalse($student->isAdmin());

        $this->assertTrue($instructor->isInstructor());
        $this->assertFalse($instructor->isStudent());

        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($admin->isStudent());
    }

    /** @test */
    public function user_can_have_enrollments()
    {
        $student = User::factory()->student()->create();
        $course = Course::factory()->create();

        Enrollment::create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'enrolled_at' => now()
        ]);

        $this->assertCount(1, $student->courses);
        $this->assertEquals($course->id, $student->courses->first()->id);
    }

    /** @test */
    public function user_can_have_join_requests()
    {
        $student = User::factory()->student()->create();
        $course = Course::factory()->create();

        JoinRequest::create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'request_date' => now(),
            'status' => 'PENDING'
        ]);

        $this->assertCount(1, $student->joinRequests);
    }

    /** @test */
    public function category_can_be_created()
    {
        $category = Category::factory()->create([
            'name' => 'Programming',
            'description' => 'Programming courses'
        ]);

        $this->assertDatabaseHas('categories', [
            'name' => 'Programming'
        ]);
    }

    /** @test */
    public function category_can_have_courses()
    {
        $category = Category::factory()->create();
        $course1 = Course::factory()->create(['category_id' => $category->id]);
        $course2 = Course::factory()->create(['category_id' => $category->id]);

        $this->assertCount(2, $category->courses);
    }

    /** @test */
    public function course_can_be_created()
    {
        $instructor = User::factory()->instructor()->create();
        $category = Category::factory()->create();

        $course = Course::factory()->create([
            'title' => 'Test Course',
            'price' => 99.99,
            'instructor_id' => $instructor->id,
            'category_id' => $category->id
        ]);

        $this->assertDatabaseHas('courses', [
            'title' => 'Test Course',
            'price' => '99.99'
        ]);
    }

    /** @test */
    public function course_belongs_to_instructor()
    {
        $instructor = User::factory()->instructor()->create();
        $course = Course::factory()->create(['instructor_id' => $instructor->id]);

        $this->assertEquals($instructor->id, $course->instructor->id);
        $this->assertInstanceOf(User::class, $course->instructor);
    }

    /** @test */
    public function course_belongs_to_category()
    {
        $category = Category::factory()->create();
        $course = Course::factory()->create(['category_id' => $category->id]);

        $this->assertEquals($category->id, $course->category->id);
        $this->assertInstanceOf(Category::class, $course->category);
    }

    /** @test */
    public function course_can_have_students()
    {
        $course = Course::factory()->create();
        $student1 = User::factory()->student()->create();
        $student2 = User::factory()->student()->create();

        Enrollment::create([
            'student_id' => $student1->id,
            'course_id' => $course->id,
            'enrolled_at' => now()
        ]);

        Enrollment::create([
            'student_id' => $student2->id,
            'course_id' => $course->id,
            'enrolled_at' => now()
        ]);

        $this->assertCount(2, $course->students);
    }

    /** @test */
    public function course_can_have_join_requests()
    {
        $course = Course::factory()->create();
        $student = User::factory()->student()->create();

        JoinRequest::create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'request_date' => now(),
            'status' => 'PENDING'
        ]);

        $this->assertCount(1, $course->joinRequests);
    }

    /** @test */
    public function course_can_be_closed()
    {
        $course = Course::factory()->closed()->create();

        $this->assertTrue($course->is_closed);
    }

    /** @test */
    public function enrollment_belongs_to_student_and_course()
    {
        $student = User::factory()->student()->create();
        $course = Course::factory()->create();

        $enrollment = Enrollment::create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'enrolled_at' => now(),
            'progress' => 50
        ]);

        $this->assertEquals($student->id, $enrollment->student->id);
        $this->assertEquals($course->id, $enrollment->course->id);
        $this->assertEquals(50, $enrollment->progress);
    }

    /** @test */
    public function join_request_has_status()
    {
        $student = User::factory()->student()->create();
        $course = Course::factory()->create();

        $request = JoinRequest::create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'request_date' => now(),
            'status' => 'PENDING'
        ]);

        $this->assertEquals('PENDING', $request->status);

        $request->update(['status' => 'APPROVED']);
        $this->assertEquals('APPROVED', $request->status);
    }

    /** @test */
    public function join_request_belongs_to_student_and_course()
    {
        $student = User::factory()->student()->create();
        $course = Course::factory()->create();

        $request = JoinRequest::create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'request_date' => now(),
            'status' => 'PENDING'
        ]);

        $this->assertEquals($student->id, $request->student->id);
        $this->assertEquals($course->id, $request->course->id);
        $this->assertInstanceOf(User::class, $request->student);
        $this->assertInstanceOf(Course::class, $request->course);
    }

    /** @test */
    public function course_price_is_decimal()
    {
        $course = Course::factory()->create(['price' => 99.99]);

        $this->assertEquals(99.99, $course->price);
        $this->assertIsFloat($course->price);
    }

    /** @test */
    public function user_password_is_hashed()
    {
        $user = User::factory()->create([
            'password' => 'plaintext'
        ]);

        $this->assertNotEquals('plaintext', $user->password);
        $this->assertTrue(strlen($user->password) > 50); // Hashed password is longer
    }
}