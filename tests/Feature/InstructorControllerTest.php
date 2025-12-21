<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Course;
use App\Models\Category;
use App\Models\Enrollment;
use App\Models\JoinRequest;
use App\Models\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InstructorControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $instructor;
    protected $student;
    protected $category;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->instructor = User::factory()->instructor()->create();
        $this->student = User::factory()->student()->create();
        $this->category = Category::factory()->create();
    }

    /** @test */
    public function instructor_can_access_dashboard()
    {
        $response = $this->actingAs($this->instructor)
            ->get(route('instructor.dashboard'));
            
        $response->assertStatus(200);
        $response->assertViewIs('instructor.dashboard');
        $response->assertViewHas([
            'totalCourses',
            'publishedCourses',
            'pendingEnrollments',
            'totalStudents'
        ]);
    }

    /** @test */
    public function student_cannot_access_instructor_dashboard()
    {
        $response = $this->actingAs($this->student)
            ->get(route('instructor.dashboard'));
            
        $response->assertStatus(403);
    }

    /** @test */
    public function instructor_can_view_their_courses()
    {
        Course::factory()->count(3)->create([
            'instructor_id' => $this->instructor->id,
            'category_id' => $this->category->id
        ]);

        $response = $this->actingAs($this->instructor)
            ->get(route('instructor.courses.index'));
            
        $response->assertStatus(200);
        $response->assertViewIs('instructor.courses.index');
        $response->assertViewHas('courses');
    }

    /** @test */
    public function instructor_can_create_course()
    {
        $courseData = [
            'title' => 'New Course',
            'description' => 'This is a test course description',
            'price' => 199.99,
            'category_id' => $this->category->id,
            'is_closed' => false,
        ];

        $response = $this->actingAs($this->instructor)
            ->post(route('instructor.courses.store'), $courseData);
            
        $response->assertRedirect(route('instructor.courses.index'));
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('courses', [
            'title' => 'New Course',
            'instructor_id' => $this->instructor->id,
            'price' => '199.99'
        ]);
    }

    /** @test */
    public function course_creation_requires_valid_data()
    {
        $courseData = [
            'title' => 'AB', // Too short
            'description' => 'Short', // Too short
            'price' => -10, // Negative price
            'category_id' => 999, // Non-existent category
        ];

        $response = $this->actingAs($this->instructor)
            ->post(route('instructor.courses.store'), $courseData);
            
        $response->assertSessionHasErrors(['title', 'description', 'price', 'category_id']);
    }

    /** @test */
    public function instructor_can_view_own_course()
    {
        $course = Course::factory()->create([
            'instructor_id' => $this->instructor->id,
            'category_id' => $this->category->id
        ]);

        $response = $this->actingAs($this->instructor)
            ->get(route('instructor.courses.show', $course->id));
            
        $response->assertStatus(200);
        $response->assertViewIs('instructor.courses.show');
        $response->assertViewHas('course');
    }

    /** @test */
    public function instructor_cannot_view_other_instructor_course()
    {
        $otherInstructor = User::factory()->instructor()->create();
        $course = Course::factory()->create([
            'instructor_id' => $otherInstructor->id,
            'category_id' => $this->category->id
        ]);

        $response = $this->actingAs($this->instructor)
            ->get(route('instructor.courses.show', $course->id));
            
        $response->assertRedirect(route('instructor.courses.index'));
        $response->assertSessionHas('error');
    }

    /** @test */
    public function instructor_can_update_own_course()
    {
        $course = Course::factory()->create([
            'instructor_id' => $this->instructor->id,
            'category_id' => $this->category->id
        ]);

        $updateData = [
            'title' => 'Updated Course Title',
            'description' => 'Updated course description',
            'price' => 299.99,
            'is_closed' => true,
        ];

        $response = $this->actingAs($this->instructor)
            ->put(route('instructor.courses.update', $course->id), $updateData);
            
        $response->assertRedirect(route('instructor.courses.index'));
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('courses', [
            'id' => $course->id,
            'title' => 'Updated Course Title',
            'price' => '299.99',
            'is_closed' => true
        ]);
    }

    /** @test */
    public function instructor_can_delete_course_without_enrollments()
    {
        $course = Course::factory()->create([
            'instructor_id' => $this->instructor->id,
            'category_id' => $this->category->id
        ]);

        $response = $this->actingAs($this->instructor)
            ->delete(route('instructor.courses.delete', $course->id));
            
        $response->assertRedirect(route('instructor.courses.index'));
        $response->assertSessionHas('success');
        
        $this->assertDatabaseMissing('courses', ['id' => $course->id]);
    }

    /** @test */
    public function instructor_cannot_delete_course_with_enrollments()
    {
        $course = Course::factory()->create([
            'instructor_id' => $this->instructor->id,
            'category_id' => $this->category->id
        ]);

        Enrollment::create([
            'student_id' => $this->student->id,
            'course_id' => $course->id,
            'enrolled_at' => now()
        ]);

        $response = $this->actingAs($this->instructor)
            ->delete(route('instructor.courses.delete', $course->id));
            
        $response->assertRedirect(route('instructor.courses.index'));
        $response->assertSessionHas('error');
        
        $this->assertDatabaseHas('courses', ['id' => $course->id]);
    }

    /** @test */
    public function instructor_can_view_join_requests()
    {
        $course = Course::factory()->create([
            'instructor_id' => $this->instructor->id,
            'category_id' => $this->category->id
        ]);

        JoinRequest::create([
            'student_id' => $this->student->id,
            'course_id' => $course->id,
            'request_date' => now(),
            'status' => 'PENDING'
        ]);

        $response = $this->actingAs($this->instructor)
            ->get(route('instructor.join-requests.index'));
            
        $response->assertStatus(200);
        $response->assertViewIs('instructor.join-requests.index');
        $response->assertViewHas('joinRequests');
    }

    /** @test */
    public function instructor_can_approve_join_request()
    {
        $course = Course::factory()->create([
            'instructor_id' => $this->instructor->id,
            'category_id' => $this->category->id
        ]);

        $joinRequest = JoinRequest::create([
            'student_id' => $this->student->id,
            'course_id' => $course->id,
            'request_date' => now(),
            'status' => 'PENDING'
        ]);

        $response = $this->actingAs($this->instructor)
            ->post(route('instructor.join-requests.approve', $joinRequest->id));
            
        $response->assertRedirect(route('instructor.join-requests.index'));
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('join_requests', [
            'id' => $joinRequest->id,
            'status' => 'ACCEPTED'
        ]);
        
        $this->assertDatabaseHas('enrollments', [
            'student_id' => $this->student->id,
            'course_id' => $course->id
        ]);
        
        $this->assertDatabaseHas('notifications', [
            'recipient_id' => $this->student->id
        ]);
    }

    /** @test */
    public function instructor_can_reject_join_request()
    {
        $course = Course::factory()->create([
            'instructor_id' => $this->instructor->id,
            'category_id' => $this->category->id
        ]);

        $joinRequest = JoinRequest::create([
            'student_id' => $this->student->id,
            'course_id' => $course->id,
            'request_date' => now(),
            'status' => 'PENDING'
        ]);

        $response = $this->actingAs($this->instructor)
            ->post(route('instructor.join-requests.reject', $joinRequest->id));
            
        $response->assertRedirect(route('instructor.join-requests.index'));
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('join_requests', [
            'id' => $joinRequest->id,
            'status' => 'REJECTED'
        ]);
        
        $this->assertDatabaseMissing('enrollments', [
            'student_id' => $this->student->id,
            'course_id' => $course->id
        ]);
    }

    /** @test */
    public function instructor_cannot_approve_already_processed_request()
    {
        $course = Course::factory()->create([
            'instructor_id' => $this->instructor->id,
            'category_id' => $this->category->id
        ]);

        $joinRequest = JoinRequest::create([
            'student_id' => $this->student->id,
            'course_id' => $course->id,
            'request_date' => now(),
            'status' => 'ACCEPTED'
        ]);

        $response = $this->actingAs($this->instructor)
            ->post(route('instructor.join-requests.approve', $joinRequest->id));
            
        $response->assertSessionHas('error');
    }

    /** @test */
    public function instructor_can_view_enrollments()
    {
        $course = Course::factory()->create([
            'instructor_id' => $this->instructor->id,
            'category_id' => $this->category->id
        ]);

        Enrollment::create([
            'student_id' => $this->student->id,
            'course_id' => $course->id,
            'enrolled_at' => now()
        ]);

        $response = $this->actingAs($this->instructor)
            ->get(route('instructor.enrollments.index'));
            
        $response->assertStatus(200);
        $response->assertViewIs('instructor.enrollments.index');
        $response->assertViewHas('enrollments');
    }

    /** @test */
    public function instructor_can_remove_enrollment()
    {
        $course = Course::factory()->create([
            'instructor_id' => $this->instructor->id,
            'category_id' => $this->category->id
        ]);

        $enrollment = Enrollment::create([
            'student_id' => $this->student->id,
            'course_id' => $course->id,
            'enrolled_at' => now()
        ]);

        $response = $this->actingAs($this->instructor)
            ->delete(route('instructor.enrollments.remove', $enrollment->id));
            
        $response->assertRedirect(route('instructor.enrollments.index'));
        $response->assertSessionHas('success');
        
        $this->assertDatabaseMissing('enrollments', ['id' => $enrollment->id]);
        
        $this->assertDatabaseHas('notifications', [
            'recipient_id' => $this->student->id
        ]);
    }

    /** @test */
    public function instructor_can_view_analytics()
    {
        $response = $this->actingAs($this->instructor)
            ->get(route('instructor.analytics'));
            
        $response->assertStatus(200);
        $response->assertViewIs('instructor.analytics.index');
        $response->assertViewHas([
            'coursesStats',
            'enrollmentStats',
            'totalRevenue',
            'topCourses'
        ]);
    }

    /** @test */
    public function analytics_show_correct_statistics()
    {
        // Create courses
        $course1 = Course::factory()->create([
            'instructor_id' => $this->instructor->id,
            'category_id' => $this->category->id,
            'price' => 100,
            'is_closed' => false
        ]);

        $course2 = Course::factory()->create([
            'instructor_id' => $this->instructor->id,
            'category_id' => $this->category->id,
            'price' => 200,
            'is_closed' => true
        ]);

        // Create enrollments
        $student2 = User::factory()->student()->create();
        
        Enrollment::create([
            'student_id' => $this->student->id,
            'course_id' => $course1->id,
            'enrolled_at' => now(),
            'progress' => 50
        ]);

        Enrollment::create([
            'student_id' => $student2->id,
            'course_id' => $course1->id,
            'enrolled_at' => now(),
            'progress' => 75
        ]);

        $response = $this->actingAs($this->instructor)
            ->get(route('instructor.analytics'));
            
        $response->assertStatus(200);
        
        $coursesStats = $response->viewData('coursesStats');
        $this->assertEquals(2, $coursesStats->total);
        $this->assertEquals(1, $coursesStats->closed);
        $this->assertEquals(1, $coursesStats->open);
    }

    /** @test */
    public function instructor_can_view_students_in_course()
    {
        $course = Course::factory()->create([
            'instructor_id' => $this->instructor->id,
            'category_id' => $this->category->id
        ]);

        Enrollment::create([
            'student_id' => $this->student->id,
            'course_id' => $course->id,
            'enrolled_at' => now()
        ]);

        $response = $this->actingAs($this->instructor)
            ->get(route('instructor.courses.students', $course->id));
            
        $response->assertStatus(200);
        $response->assertViewIs('instructor.courses.students');
        $response->assertViewHas('course');
    }

    /** @test */
    public function dashboard_shows_correct_counts()
    {
        // Create multiple courses
        Course::factory()->count(3)->create([
            'instructor_id' => $this->instructor->id,
            'category_id' => $this->category->id,
            'is_closed' => false
        ]);

        Course::factory()->count(2)->create([
            'instructor_id' => $this->instructor->id,
            'category_id' => $this->category->id,
            'is_closed' => true
        ]);

        $response = $this->actingAs($this->instructor)
            ->get(route('instructor.dashboard'));
            
        $response->assertStatus(200);
        
        $totalCourses = $response->viewData('totalCourses');
        $publishedCourses = $response->viewData('publishedCourses');
        
        $this->assertEquals(5, $totalCourses);
        $this->assertEquals(3, $publishedCourses);
    }
}