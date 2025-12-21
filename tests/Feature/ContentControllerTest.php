<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Course;
use App\Models\Category;
use App\Models\CourseContent;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ContentControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $instructor;
    protected $course;
    protected $category;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->instructor = User::factory()->instructor()->create();
        $this->category = Category::factory()->create();
        $this->course = Course::factory()->create([
            'instructor_id' => $this->instructor->id,
            'category_id' => $this->category->id
        ]);
    }

    /** @test */
    public function instructor_can_view_course_contents()
    {
        CourseContent::factory()->count(3)->create([
            'course_id' => $this->course->id
        ]);

        $response = $this->actingAs($this->instructor)
            ->get(route('instructor.content.index', $this->course->id));
            
        $response->assertStatus(200);
        $response->assertViewIs('instructor.content.index');
        $response->assertViewHas('course');
    }

    /** @test */
    public function instructor_cannot_view_other_instructor_course_contents()
    {
        $otherInstructor = User::factory()->instructor()->create();
        $otherCourse = Course::factory()->create([
            'instructor_id' => $otherInstructor->id,
            'category_id' => $this->category->id
        ]);

        $response = $this->actingAs($this->instructor)
            ->get(route('instructor.content.index', $otherCourse->id));
            
        $response->assertRedirect(route('instructor.courses.index'));
        $response->assertSessionHas('error');
    }

    /** @test */
    public function instructor_can_create_lesson_content()
    {
        $contentData = [
            'title' => 'Introduction to Laravel',
            'description' => 'Learn the basics',
            'content_type' => 'LESSON',
            'content' => 'This is the lesson content',
            'order' => 1,
        ];

        $response = $this->actingAs($this->instructor)
            ->post(route('instructor.content.store', $this->course->id), $contentData);
            
        $response->assertRedirect(route('instructor.content.index', $this->course->id));
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('course_contents', [
            'course_id' => $this->course->id,
            'title' => 'Introduction to Laravel',
            'content_type' => 'LESSON'
        ]);
    }

    /** @test */
    public function instructor_can_create_video_content()
    {
        $contentData = [
            'title' => 'Laravel Tutorial Video',
            'description' => 'Video tutorial',
            'content_type' => 'VIDEO',
            'external_link' => 'https://www.youtube.com/watch?v=test123',
            'order' => 1,
        ];

        $response = $this->actingAs($this->instructor)
            ->post(route('instructor.content.store', $this->course->id), $contentData);
            
        $response->assertRedirect(route('instructor.content.index', $this->course->id));
        
        $this->assertDatabaseHas('course_contents', [
            'course_id' => $this->course->id,
            'title' => 'Laravel Tutorial Video',
            'content_type' => 'VIDEO',
            'external_link' => 'https://www.youtube.com/watch?v=test123'
        ]);
    }

    /** @test */
    public function instructor_can_create_document_content()
    {
        $contentData = [
            'title' => 'Course Documentation',
            'description' => 'PDF document',
            'content_type' => 'DOCUMENT',
            'external_link' => 'https://drive.google.com/file/d/test123',
            'order' => 1,
        ];

        $response = $this->actingAs($this->instructor)
            ->post(route('instructor.content.store', $this->course->id), $contentData);
            
        $response->assertRedirect(route('instructor.content.index', $this->course->id));
        
        $this->assertDatabaseHas('course_contents', [
            'course_id' => $this->course->id,
            'content_type' => 'DOCUMENT'
        ]);
    }

    /** @test */
    public function instructor_can_create_link_content()
    {
        $contentData = [
            'title' => 'Additional Resources',
            'description' => 'External link',
            'content_type' => 'LINK',
            'external_link' => 'https://example.com/resources',
            'order' => 1,
        ];

        $response = $this->actingAs($this->instructor)
            ->post(route('instructor.content.store', $this->course->id), $contentData);
            
        $response->assertRedirect(route('instructor.content.index', $this->course->id));
        
        $this->assertDatabaseHas('course_contents', [
            'course_id' => $this->course->id,
            'content_type' => 'LINK'
        ]);
    }

    /** @test */
    public function lesson_content_requires_content_field()
    {
        $contentData = [
            'title' => 'Test Lesson',
            'content_type' => 'LESSON',
            'order' => 1,
            // Missing 'content' field
        ];

        $response = $this->actingAs($this->instructor)
            ->post(route('instructor.content.store', $this->course->id), $contentData);
            
        $response->assertSessionHasErrors('content');
    }

    /** @test */
    public function video_content_requires_external_link()
    {
        $contentData = [
            'title' => 'Test Video',
            'content_type' => 'VIDEO',
            'order' => 1,
            // Missing 'external_link' field
        ];

        $response = $this->actingAs($this->instructor)
            ->post(route('instructor.content.store', $this->course->id), $contentData);
            
        $response->assertSessionHasErrors('external_link');
    }

    /** @test */
    public function document_content_requires_external_link()
    {
        $contentData = [
            'title' => 'Test Document',
            'content_type' => 'DOCUMENT',
            'order' => 1,
            // Missing 'external_link' field
        ];

        $response = $this->actingAs($this->instructor)
            ->post(route('instructor.content.store', $this->course->id), $contentData);
            
        $response->assertSessionHasErrors('external_link');
    }

    /** @test */
    public function link_content_requires_external_link()
    {
        $contentData = [
            'title' => 'Test Link',
            'content_type' => 'LINK',
            'order' => 1,
            // Missing 'external_link' field
        ];

        $response = $this->actingAs($this->instructor)
            ->post(route('instructor.content.store', $this->course->id), $contentData);
            
        $response->assertSessionHasErrors('external_link');
    }

    /** @test */
    public function content_creation_validates_url_format()
    {
        $contentData = [
            'title' => 'Test Video',
            'content_type' => 'VIDEO',
            'external_link' => 'not-a-valid-url',
            'order' => 1,
        ];

        $response = $this->actingAs($this->instructor)
            ->post(route('instructor.content.store', $this->course->id), $contentData);
            
        $response->assertSessionHasErrors('external_link');
    }

    /** @test */
    public function instructor_can_edit_content()
    {
        $content = CourseContent::create([
            'course_id' => $this->course->id,
            'title' => 'Original Title',
            'content_type' => 'LESSON',
            'content' => 'Original content',
            'order' => 0
        ]);

        $response = $this->actingAs($this->instructor)
            ->get(route('instructor.content.edit', [$this->course->id, $content->id]));
            
        $response->assertStatus(200);
        $response->assertViewIs('instructor.content.edit');
        $response->assertViewHas('content');
    }

    /** @test */
    public function instructor_can_update_content()
    {
        $content = CourseContent::create([
            'course_id' => $this->course->id,
            'title' => 'Original Title',
            'content_type' => 'LESSON',
            'content' => 'Original content',
            'order' => 0
        ]);

        $updateData = [
            'title' => 'Updated Title',
            'description' => 'Updated description',
            'content_type' => 'LESSON',
            'content' => 'Updated content',
            'order' => 1,
        ];

        $response = $this->actingAs($this->instructor)
            ->put(route('instructor.content.update', [$this->course->id, $content->id]), $updateData);
            
        $response->assertRedirect(route('instructor.content.index', $this->course->id));
        
        $this->assertDatabaseHas('course_contents', [
            'id' => $content->id,
            'title' => 'Updated Title',
            'content' => 'Updated content'
        ]);
    }

    /** @test */
    public function instructor_can_delete_content()
    {
        $content = CourseContent::create([
            'course_id' => $this->course->id,
            'title' => 'Test Content',
            'content_type' => 'LESSON',
            'content' => 'Test content',
            'order' => 0
        ]);

        $response = $this->actingAs($this->instructor)
            ->delete(route('instructor.content.destroy', [$this->course->id, $content->id]));
            
        $response->assertRedirect(route('instructor.content.index', $this->course->id));
        $response->assertSessionHas('success');
        
        $this->assertDatabaseMissing('course_contents', ['id' => $content->id]);
    }

    /** @test */
    public function instructor_cannot_edit_other_instructor_content()
    {
        $otherInstructor = User::factory()->instructor()->create();
        $otherCourse = Course::factory()->create([
            'instructor_id' => $otherInstructor->id,
            'category_id' => $this->category->id
        ]);

        $content = CourseContent::create([
            'course_id' => $otherCourse->id,
            'title' => 'Test Content',
            'content_type' => 'LESSON',
            'content' => 'Test',
            'order' => 0
        ]);

        $response = $this->actingAs($this->instructor)
            ->get(route('instructor.content.edit', [$otherCourse->id, $content->id]));
            
        $response->assertRedirect(route('instructor.courses.index'));
        $response->assertSessionHas('error');
    }

    /** @test */
    public function instructor_can_reorder_contents()
    {
        $content1 = CourseContent::create([
            'course_id' => $this->course->id,
            'title' => 'Content 1',
            'content_type' => 'LESSON',
            'content' => 'Content 1',
            'order' => 0
        ]);

        $content2 = CourseContent::create([
            'course_id' => $this->course->id,
            'title' => 'Content 2',
            'content_type' => 'LESSON',
            'content' => 'Content 2',
            'order' => 1
        ]);

        $content3 = CourseContent::create([
            'course_id' => $this->course->id,
            'title' => 'Content 3',
            'content_type' => 'LESSON',
            'content' => 'Content 3',
            'order' => 2
        ]);

        // Reorder: 3, 1, 2
        $response = $this->actingAs($this->instructor)
            ->postJson(route('instructor.content.reorder', $this->course->id), [
                'order' => [$content3->id, $content1->id, $content2->id]
            ]);
            
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        
        $this->assertDatabaseHas('course_contents', [
            'id' => $content3->id,
            'order' => 0
        ]);
        
        $this->assertDatabaseHas('course_contents', [
            'id' => $content1->id,
            'order' => 1
        ]);
        
        $this->assertDatabaseHas('course_contents', [
            'id' => $content2->id,
            'order' => 2
        ]);
    }

    /** @test */
    public function content_title_is_sanitized()
    {
        $contentData = [
            'title' => '<script>alert("xss")</script>Test Title',
            'content_type' => 'LESSON',
            'content' => 'Test content',
            'order' => 1,
        ];

        $response = $this->actingAs($this->instructor)
            ->post(route('instructor.content.store', $this->course->id), $contentData);
            
        $this->assertDatabaseHas('course_contents', [
            'course_id' => $this->course->id,
            'title' => 'Test Title'
        ]);
        
        $this->assertDatabaseMissing('course_contents', [
            'title' => '<script>alert("xss")</script>Test Title'
        ]);
    }

    /** @test */
    public function contents_are_ordered_correctly()
    {
        CourseContent::create([
            'course_id' => $this->course->id,
            'title' => 'Content 3',
            'content_type' => 'LESSON',
            'content' => 'Third',
            'order' => 2
        ]);

        CourseContent::create([
            'course_id' => $this->course->id,
            'title' => 'Content 1',
            'content_type' => 'LESSON',
            'content' => 'First',
            'order' => 0
        ]);

        CourseContent::create([
            'course_id' => $this->course->id,
            'title' => 'Content 2',
            'content_type' => 'LESSON',
            'content' => 'Second',
            'order' => 1
        ]);

        $response = $this->actingAs($this->instructor)
            ->get(route('instructor.content.index', $this->course->id));
            
        $contents = $response->viewData('course')->contents;
        
        $this->assertEquals('Content 1', $contents[0]->title);
        $this->assertEquals('Content 2', $contents[1]->title);
        $this->assertEquals('Content 3', $contents[2]->title);
    }

    /** @test */
    public function next_order_number_is_calculated_correctly()
    {
        CourseContent::create([
            'course_id' => $this->course->id,
            'title' => 'Content 1',
            'content_type' => 'LESSON',
            'content' => 'First',
            'order' => 0
        ]);

        CourseContent::create([
            'course_id' => $this->course->id,
            'title' => 'Content 2',
            'content_type' => 'LESSON',
            'content' => 'Second',
            'order' => 1
        ]);

        $response = $this->actingAs($this->instructor)
            ->get(route('instructor.content.create', $this->course->id));
            
        $nextOrder = $response->viewData('nextOrder');
        
        $this->assertEquals(2, $nextOrder);
    }
}