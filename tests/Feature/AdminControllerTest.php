<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Course;
use App\Models\Category;
use App\Models\Enrollment;
use App\Models\JoinRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class AdminControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $student;
    protected $instructor;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test users
        $this->admin = User::factory()->create([
            'role' => 'ADMIN',
            'email' => 'admin@test.com',
            'password' => Hash::make('password123')
        ]);
        
        $this->student = User::factory()->create([
            'role' => 'STUDENT',
            'email' => 'student@test.com'
        ]);
        
        $this->instructor = User::factory()->create([
            'role' => 'INSTRUCTOR',
            'email' => 'instructor@test.com'
        ]);
    }

    /** @test */
    public function admin_can_access_dashboard()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.dashboard'));
            
        $response->assertStatus(200);
        $response->assertViewIs('admin.dashboard');
    }

    /** @test */
    public function non_admin_cannot_access_dashboard()
    {
        $response = $this->actingAs($this->student)
            ->get(route('admin.dashboard'));
            
        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_view_users_list()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.users.index'));
            
        $response->assertStatus(200);
        $response->assertViewIs('admin.users.index');
        $response->assertViewHas('users');
    }

    /** @test */
    public function admin_can_create_user()
    {
        $userData = [
            'name' => 'newuser',
            'full_name' => 'New User',
            'email' => 'newuser@test.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'role' => 'STUDENT',
        ];
        
        $response = $this->actingAs($this->admin)
            ->post(route('admin.users.store'), $userData);
            
        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('users', [
            'email' => 'newuser@test.com',
            'role' => 'STUDENT'
        ]);
    }

    /** @test */
    public function user_creation_fails_with_invalid_email()
    {
        $userData = [
            'name' => 'newuser',
            'full_name' => 'New User',
            'email' => 'invalid-email',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'role' => 'STUDENT',
        ];
        
        $response = $this->actingAs($this->admin)
            ->post(route('admin.users.store'), $userData);
            
        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function user_creation_fails_with_weak_password()
    {
        $userData = [
            'name' => 'newuser',
            'full_name' => 'New User',
            'email' => 'newuser@test.com',
            'password' => 'weak',
            'password_confirmation' => 'weak',
            'role' => 'STUDENT',
        ];
        
        $response = $this->actingAs($this->admin)
            ->post(route('admin.users.store'), $userData);
            
        $response->assertSessionHasErrors('password');
    }

    /** @test */
    public function admin_can_update_user()
    {
        $user = User::factory()->create(['role' => 'STUDENT']);
        
        $updateData = [
            'name' => 'updateduser',
            'full_name' => 'Updated User',
            'email' => $user->email,
            'role' => 'INSTRUCTOR',
        ];
        
        $response = $this->actingAs($this->admin)
            ->put(route('admin.users.update', $user->id), $updateData);
            
        $response->assertRedirect(route('admin.users.index'));
        
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'updateduser',
            'role' => 'INSTRUCTOR'
        ]);
    }

    /** @test */
    public function admin_can_delete_user_without_enrollments()
    {
        $user = User::factory()->create(['role' => 'STUDENT']);
        
        $response = $this->actingAs($this->admin)
            ->delete(route('admin.users.delete', $user->id));
            
        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');
        
        $this->assertDatabaseMissing('users', [
            'id' => $user->id
        ]);
    }

    /** @test */
    public function admin_cannot_delete_user_with_enrollments()
    {
        $category = Category::factory()->create();
        $course = Course::factory()->create([
            'instructor_id' => $this->instructor->id,
            'category_id' => $category->id
        ]);
        
        Enrollment::create([
            'student_id' => $this->student->id,
            'course_id' => $course->id,
            'enrolled_at' => now()
        ]);
        
        $response = $this->actingAs($this->admin)
            ->delete(route('admin.users.delete', $this->student->id));
            
        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('error');
        
        $this->assertDatabaseHas('users', [
            'id' => $this->student->id
        ]);
    }

    /** @test */
    public function admin_can_view_courses_list()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.courses.index'));
            
        $response->assertStatus(200);
        $response->assertViewIs('admin.courses.index');
    }

    /** @test */
    public function admin_can_create_course()
    {
        $category = Category::factory()->create();
        
        $courseData = [
            'title' => 'New Test Course',
            'description' => 'This is a test course description',
            'price' => 99.99,
            'category_id' => $category->id,
            'instructor_id' => $this->instructor->id,
            'is_closed' => false,
        ];
        
        $response = $this->actingAs($this->admin)
            ->post(route('admin.courses.store'), $courseData);
            
        $response->assertRedirect(route('admin.courses.index'));
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('courses', [
            'title' => 'New Test Course',
            'price' => '99.99'
        ]);
    }

    /** @test */
    public function course_creation_fails_with_invalid_price()
    {
        $category = Category::factory()->create();
        
        $courseData = [
            'title' => 'Test Course',
            'description' => 'Test description',
            'price' => -10,
            'category_id' => $category->id,
            'instructor_id' => $this->instructor->id,
        ];
        
        $response = $this->actingAs($this->admin)
            ->post(route('admin.courses.store'), $courseData);
            
        $response->assertSessionHasErrors('price');
    }

    /** @test */
    public function course_creation_fails_with_non_instructor()
    {
        $category = Category::factory()->create();
        
        $courseData = [
            'title' => 'Test Course',
            'description' => 'Test description',
            'price' => 99.99,
            'category_id' => $category->id,
            'instructor_id' => $this->student->id, // Student, not instructor
        ];
        
        $response = $this->actingAs($this->admin)
            ->post(route('admin.courses.store'), $courseData);
            
        $response->assertSessionHasErrors('instructor_id');
    }

    /** @test */
    public function admin_can_update_course()
    {
        $category = Category::factory()->create();
        $course = Course::factory()->create([
            'instructor_id' => $this->instructor->id,
            'category_id' => $category->id
        ]);
        
        $updateData = [
            'title' => 'Updated Course Title',
            'description' => 'Updated description',
            'price' => 149.99,
            'category_id' => $category->id,
            'instructor_id' => $this->instructor->id,
            'is_closed' => true,
        ];
        
        $response = $this->actingAs($this->admin)
            ->put(route('admin.courses.update', $course->id), $updateData);
            
        $response->assertRedirect(route('admin.courses.index'));
        
        $this->assertDatabaseHas('courses', [
            'id' => $course->id,
            'title' => 'Updated Course Title',
            'price' => '149.99',
            'is_closed' => true
        ]);
    }

    /** @test */
    public function admin_can_delete_course_without_enrollments()
    {
        $category = Category::factory()->create();
        $course = Course::factory()->create([
            'instructor_id' => $this->instructor->id,
            'category_id' => $category->id
        ]);
        
        $response = $this->actingAs($this->admin)
            ->delete(route('admin.courses.delete', $course->id));
            
        $response->assertRedirect(route('admin.courses.index'));
        $response->assertSessionHas('success');
        
        $this->assertDatabaseMissing('courses', [
            'id' => $course->id
        ]);
    }

    /** @test */
    public function admin_cannot_delete_course_with_enrollments()
    {
        $category = Category::factory()->create();
        $course = Course::factory()->create([
            'instructor_id' => $this->instructor->id,
            'category_id' => $category->id
        ]);
        
        Enrollment::create([
            'student_id' => $this->student->id,
            'course_id' => $course->id,
            'enrolled_at' => now()
        ]);
        
        $response = $this->actingAs($this->admin)
            ->delete(route('admin.courses.delete', $course->id));
            
        $response->assertRedirect(route('admin.courses.index'));
        $response->assertSessionHas('error');
        
        $this->assertDatabaseHas('courses', [
            'id' => $course->id
        ]);
    }

    /** @test */
    public function admin_can_create_category()
    {
        $categoryData = [
            'name' => 'Programming',
            'description' => 'Programming courses'
        ];
        
        $response = $this->actingAs($this->admin)
            ->post(route('admin.categories.store'), $categoryData);
            
        $response->assertRedirect(route('admin.categories.index'));
        
        $this->assertDatabaseHas('categories', [
            'name' => 'Programming'
        ]);
    }

    /** @test */
    public function category_creation_fails_with_duplicate_name()
    {
        Category::factory()->create(['name' => 'Programming']);
        
        $categoryData = [
            'name' => 'Programming',
            'description' => 'Another programming category'
        ];
        
        $response = $this->actingAs($this->admin)
            ->post(route('admin.categories.store'), $categoryData);
            
        $response->assertSessionHasErrors('name');
    }

    /** @test */
    public function admin_can_update_category()
    {
        $category = Category::factory()->create(['name' => 'Old Name']);
        
        $updateData = [
            'name' => 'New Name',
            'description' => 'Updated description'
        ];
        
        $response = $this->actingAs($this->admin)
            ->put(route('admin.categories.update', $category->id), $updateData);
            
        $response->assertRedirect(route('admin.categories.index'));
        
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'New Name'
        ]);
    }

    /** @test */
    public function admin_can_delete_category_without_courses()
    {
        $category = Category::factory()->create();
        
        $response = $this->actingAs($this->admin)
            ->delete(route('admin.categories.delete', $category->id));
            
        $response->assertRedirect(route('admin.categories.index'));
        
        $this->assertDatabaseMissing('categories', [
            'id' => $category->id
        ]);
    }

    /** @test */
    public function admin_cannot_delete_category_with_courses()
    {
        $category = Category::factory()->create();
        Course::factory()->create([
            'category_id' => $category->id,
            'instructor_id' => $this->instructor->id
        ]);
        
        $response = $this->actingAs($this->admin)
            ->delete(route('admin.categories.delete', $category->id));
            
        $response->assertRedirect(route('admin.categories.index'));
        $response->assertSessionHas('error');
        
        $this->assertDatabaseHas('categories', [
            'id' => $category->id
        ]);
    }

    /** @test */
    public function admin_can_view_statistics()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.statistics'));
            
        $response->assertStatus(200);
        $response->assertViewIs('admin.statistics');
        $response->assertViewHas(['totalUsers', 'students', 'instructors', 'courses']);
    }
}