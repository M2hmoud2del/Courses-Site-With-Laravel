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
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class StudentControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $student;
    protected $instructor;
    protected $category;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->student = User::factory()->student()->create();
        $this->instructor = User::factory()->instructor()->create();
        $this->category = Category::factory()->create();
    }

    /** @test */
    public function student_can_access_dashboard()
    {
        $response = $this->actingAs($this->student)
            ->get(route('dashboard'));
            
        $response->assertStatus(200);
        $response->assertViewIs('dashboard');
        $response->assertViewHas([
            'enrolledCourses',
            'notifications',
            'recommendedCourses',
            'joinRequests'
        ]);
    }

    /** @test */
    public function student_can_browse_courses()
    {
        Course::factory()->count(5)->create([
            'instructor_id' => $this->instructor->id,
            'category_id' => $this->category->id,
            'is_closed' => false
        ]);

        $response = $this->actingAs($this->student)
            ->getJson(route('student.browse'));
            
        $response->assertStatus(200);
        $response->assertJsonCount(5);
    }

    /** @test */
    public function browse_excludes_enrolled_courses()
    {
        $course1 = Course::factory()->create([
            'instructor_id' => $this->instructor->id,
            'category_id' => $this->category->id,
            'is_closed' => false
        ]);

        $course2 = Course::factory()->create([
            'instructor_id' => $this->instructor->id,
            'category_id' => $this->category->id,
            'is_closed' => false
        ]);

        // Enroll in course1
        Enrollment::create([
            'student_id' => $this->student->id,
            'course_id' => $course1->id,
            'enrolled_at' => now()
        ]);

        $response = $this->actingAs($this->student)
            ->getJson(route('student.browse'));
            
        $response->assertStatus(200);
        $response->assertJsonCount(1);
        $response->assertJsonFragment(['id' => $course2->id]);
        $response->assertJsonMissing(['id' => $course1->id]);
    }

    /** @test */
    public function browse_can_filter_by_category()
    {
        $category2 = Category::factory()->create(['name' => 'Design']);

        Course::factory()->create([
            'instructor_id' => $this->instructor->id,
            'category_id' => $this->category->id,
            'is_closed' => false
        ]);

        Course::factory()->create([
            'instructor_id' => $this->instructor->id,
            'category_id' => $category2->id,
            'is_closed' => false
        ]);

        $response = $this->actingAs($this->student)
            ->getJson(route('student.browse', ['category' => 'Design']));
            
        $response->assertStatus(200);
        $response->assertJsonCount(1);
    }

    /** @test */
    public function browse_can_search_courses()
    {
        Course::factory()->create([
            'title' => 'Laravel Development',
            'instructor_id' => $this->instructor->id,
            'category_id' => $this->category->id,
            'is_closed' => false
        ]);

        Course::factory()->create([
            'title' => 'Python Programming',
            'instructor_id' => $this->instructor->id,
            'category_id' => $this->category->id,
            'is_closed' => false
        ]);

        $response = $this->actingAs($this->student)
            ->getJson(route('student.browse', ['search' => 'Laravel']));
            
        $response->assertStatus(200);
        $response->assertJsonCount(1);
        $response->assertJsonFragment(['title' => 'Laravel Development']);
    }

    /** @test */
    public function browse_can_filter_by_price()
    {
        Course::factory()->create([
            'price' => 0,
            'instructor_id' => $this->instructor->id,
            'category_id' => $this->category->id,
            'is_closed' => false
        ]);

        Course::factory()->create([
            'price' => 99.99,
            'instructor_id' => $this->instructor->id,
            'category_id' => $this->category->id,
            'is_closed' => false
        ]);

        $response = $this->actingAs($this->student)
            ->getJson(route('student.browse', ['price' => 'free']));
            
        $response->assertStatus(200);
        $response->assertJsonCount(1);
    }

    /** @test */
    public function student_can_view_enrolled_courses()
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

        $response = $this->actingAs($this->student)
            ->getJson(route('student.enrolled'));
            
        $response->assertStatus(200);
        $response->assertJsonCount(1);
        $response->assertJsonFragment(['id' => $course->id]);
    }

    /** @test */
    public function student_can_request_to_join_course()
    {
        $course = Course::factory()->create([
            'instructor_id' => $this->instructor->id,
            'category_id' => $this->category->id,
            'is_closed' => false
        ]);

        $response = $this->actingAs($this->student)
            ->postJson(route('student.request-join', $course->id));
            
        $response->assertStatus(201);
        
        $this->assertDatabaseHas('join_requests', [
            'student_id' => $this->student->id,
            'course_id' => $course->id,
            'status' => 'PENDING'
        ]);
    }

    /** @test */
    public function student_cannot_request_join_if_already_enrolled()
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

        $response = $this->actingAs($this->student)
            ->postJson(route('student.request-join', $course->id));
            
        $response->assertStatus(400);
        $response->assertJson(['message' => 'Already enrolled']);
    }

    /** @test */
    public function student_cannot_request_join_if_request_pending()
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

        $response = $this->actingAs($this->student)
            ->postJson(route('student.request-join', $course->id));
            
        $response->assertStatus(400);
        $response->assertJson(['message' => 'Request already pending']);
    }

    /** @test */
    public function student_can_cancel_pending_join_request()
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

        $response = $this->actingAs($this->student)
            ->deleteJson(route('student.cancel-join-request', $joinRequest->id));
            
        $response->assertStatus(200);
        $response->assertJson(['message' => 'Request cancelled']);
        
        $this->assertDatabaseMissing('join_requests', ['id' => $joinRequest->id]);
    }

    /** @test */
    public function student_cannot_cancel_processed_join_request()
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

        $response = $this->actingAs($this->student)
            ->deleteJson(route('student.cancel-join-request', $joinRequest->id));
            
        $response->assertStatus(400);
        $response->assertJson(['message' => 'Cannot cancel processed request']);
    }

    /** @test */
    public function student_can_enroll_directly_in_open_course()
    {
        $course = Course::factory()->create([
            'instructor_id' => $this->instructor->id,
            'category_id' => $this->category->id,
            'is_closed' => false
        ]);

        $response = $this->actingAs($this->student)
            ->postJson(route('student.enroll', $course->id));
            
        $response->assertStatus(200);
        $response->assertJson(['message' => 'Successfully enrolled']);
        
        $this->assertDatabaseHas('enrollments', [
            'student_id' => $this->student->id,
            'course_id' => $course->id
        ]);
    }

    /** @test */
    public function student_cannot_enroll_in_closed_course()
    {
        $course = Course::factory()->create([
            'instructor_id' => $this->instructor->id,
            'category_id' => $this->category->id,
            'is_closed' => true
        ]);

        $response = $this->actingAs($this->student)
            ->postJson(route('student.enroll', $course->id));
            
        $response->assertStatus(400);
        $response->assertJson(['message' => 'Course is closed for direct enrollment']);
    }

    /** @test */
    public function enrollment_cancels_pending_join_requests()
    {
        $course = Course::factory()->create([
            'instructor_id' => $this->instructor->id,
            'category_id' => $this->category->id,
            'is_closed' => false
        ]);

        JoinRequest::create([
            'student_id' => $this->student->id,
            'course_id' => $course->id,
            'request_date' => now(),
            'status' => 'PENDING'
        ]);

        $response = $this->actingAs($this->student)
            ->postJson(route('student.enroll', $course->id));
            
        $response->assertStatus(200);
        
        $this->assertDatabaseMissing('join_requests', [
            'student_id' => $this->student->id,
            'course_id' => $course->id
        ]);
    }

    /** @test */
    public function student_can_view_notifications()
    {
        Notification::create([
            'recipient_id' => $this->student->id,
            'message' => 'Test notification',
            'date' => now(),
            'is_read' => false
        ]);

        $response = $this->actingAs($this->student)
            ->getJson(route('student.notifications'));
            
        $response->assertStatus(200);
        $response->assertJsonCount(1);
        $response->assertJsonFragment(['message' => 'Test notification']);
    }

    /** @test */
    public function student_can_mark_notification_as_read()
    {
        $notification = Notification::create([
            'recipient_id' => $this->student->id,
            'message' => 'Test notification',
            'date' => now(),
            'is_read' => false
        ]);

        $response = $this->actingAs($this->student)
            ->putJson(route('student.mark-notification-read', $notification->id));
            
        $response->assertStatus(200);
        
        $this->assertDatabaseHas('notifications', [
            'id' => $notification->id,
            'is_read' => true
        ]);
    }

    /** @test */
    public function student_can_update_profile()
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('avatar.jpg');

        $response = $this->actingAs($this->student)
            ->postJson(route('student.update-profile'), [
                'name' => 'Updated Name',
                'profile_picture' => $file
            ]);
            
        $response->assertStatus(200);
        $response->assertJson(['message' => 'Profile updated successfully']);
        
        $this->assertDatabaseHas('users', [
            'id' => $this->student->id,
            'name' => 'Updated Name'
        ]);
    }

    /** @test */
    public function profile_update_requires_valid_data()
    {
        $response = $this->actingAs($this->student)
            ->postJson(route('student.update-profile'), [
                'name' => '', // Empty name
            ]);
            
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('name');
    }

    /** @test */
    public function student_can_update_password()
    {
        $response = $this->actingAs($this->student)
            ->postJson(route('student.update-password'), [
                'current_password' => 'password',
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123'
            ]);
            
        $response->assertStatus(200);
        $response->assertJson(['message' => 'Password updated successfully']);
    }

    /** @test */
    public function password_update_requires_correct_current_password()
    {
        $response = $this->actingAs($this->student)
            ->postJson(route('student.update-password'), [
                'current_password' => 'wrongpassword',
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123'
            ]);
            
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('current_password');
    }

    /** @test */
    public function password_update_requires_confirmation()
    {
        $response = $this->actingAs($this->student)
            ->postJson(route('student.update-password'), [
                'current_password' => 'password',
                'password' => 'newpassword123',
                'password_confirmation' => 'differentpassword'
            ]);
            
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('password');
    }

    /** @test */
    public function student_can_get_categories()
    {
        Category::factory()->count(3)->create();

        $response = $this->actingAs($this->student)
            ->getJson(route('student.categories'));
            
        $response->assertStatus(200);
        $response->assertJsonCount(4); // 3 + 1 from setUp
    }

    /** @test */
    public function dashboard_shows_recommended_courses()
    {
        // Create courses
        Course::factory()->count(5)->create([
            'instructor_id' => $this->instructor->id,
            'category_id' => $this->category->id,
            'is_closed' => false
        ]);

        $response = $this->actingAs($this->student)
            ->get(route('dashboard'));
            
        $response->assertStatus(200);
        
        $recommendedCourses = $response->viewData('recommendedCourses');
        $this->assertLessThanOrEqual(4, $recommendedCourses->count());
    }

    /** @test */
    public function dashboard_excludes_enrolled_courses_from_recommendations()
    {
        $course1 = Course::factory()->create([
            'instructor_id' => $this->instructor->id,
            'category_id' => $this->category->id,
            'is_closed' => false
        ]);

        $course2 = Course::factory()->create([
            'instructor_id' => $this->instructor->id,
            'category_id' => $this->category->id,
            'is_closed' => false
        ]);

        // Enroll in course1
        Enrollment::create([
            'student_id' => $this->student->id,
            'course_id' => $course1->id,
            'enrolled_at' => now()
        ]);

        $response = $this->actingAs($this->student)
            ->get(route('dashboard'));
            
        $recommendedCourses = $response->viewData('recommendedCourses');
        
        $this->assertFalse($recommendedCourses->contains('id', $course1->id));
    }
}