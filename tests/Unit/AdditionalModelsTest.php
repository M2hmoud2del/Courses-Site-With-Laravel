<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Course;
use App\Models\CourseContent;
use App\Models\AuditLog;
use App\Models\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdditionalModelsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function audit_log_can_be_created()
    {
        $admin = User::factory()->admin()->create();
        
        $log = AuditLog::factory()->create([
            'actor_id' => $admin->id,
            'action' => 'TEST_ACTION',
            'details' => 'Test details'
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'TEST_ACTION',
            'details' => 'Test details'
        ]);

        $this->assertInstanceOf(User::class, $log->actor);
        $this->assertEquals($admin->id, $log->actor->id);
    }

    /** @test */
    public function notification_can_be_created()
    {
        $user = User::factory()->create();
        
        $notification = Notification::factory()->create([
            'recipient_id' => $user->id,
            'message' => 'Test Message',
            'is_read' => false
        ]);

        $this->assertDatabaseHas('notifications', [
            'message' => 'Test Message',
            'is_read' => 0
        ]);

        $this->assertInstanceOf(User::class, $notification->recipient);
        $this->assertEquals($user->id, $notification->recipient->id);
        $this->assertFalse($notification->is_read);
    }

    /** @test */
    public function course_content_can_be_created()
    {
        $course = Course::factory()->create();
        
        $content = CourseContent::factory()->create([
            'course_id' => $course->id,
            'title' => 'Test Content',
            'content_type' => 'LESSON'
        ]);

        $this->assertDatabaseHas('course_contents', [
            'title' => 'Test Content',
            'content_type' => 'LESSON'
        ]);

        $this->assertInstanceOf(Course::class, $content->course);
        $this->assertEquals($course->id, $content->course->id);
    }

    /** @test */
    public function course_content_correctly_identifies_types()
    {
        $lesson = CourseContent::factory()->make(['content_type' => 'LESSON']);
        $video = CourseContent::factory()->make(['content_type' => 'VIDEO']);
        $doc = CourseContent::factory()->make(['content_type' => 'DOCUMENT']);
        $link = CourseContent::factory()->make(['content_type' => 'LINK']);
        $unknown = CourseContent::factory()->make(['content_type' => 'UNKNOWN']);

        // Test getTypeLabelAttribute
        $this->assertEquals('Lesson', $lesson->type_label);
        $this->assertEquals('Video', $video->type_label);
        $this->assertEquals('Document', $doc->type_label);
        $this->assertEquals('Link', $link->type_label);
        $this->assertEquals('Unknown', $unknown->type_label);

        // Test getTypeIconAttribute
        $this->assertEquals('fas fa-book-open', $lesson->type_icon);
        $this->assertEquals('fas fa-video', $video->type_icon);
        $this->assertEquals('fas fa-file', $doc->type_icon);
        $this->assertEquals('fas fa-link', $link->type_icon);
        $this->assertEquals('fas fa-question', $unknown->type_icon);

        // Test getTypeColorAttribute
        $this->assertEquals('blue', $lesson->type_color);
        $this->assertEquals('purple', $video->type_color);
        $this->assertEquals('green', $doc->type_color);
        $this->assertEquals('yellow', $link->type_color);
        $this->assertEquals('gray', $unknown->type_color);
    }

    /** @test */
    public function course_content_identifies_external_link()
    {
        $lesson = CourseContent::factory()->make(['content_type' => 'LESSON']);
        $video = CourseContent::factory()->make(['content_type' => 'VIDEO']);
        $link = CourseContent::factory()->make(['content_type' => 'LINK']);
        $doc = CourseContent::factory()->make(['content_type' => 'DOCUMENT']);

        $this->assertFalse($lesson->isExternalLink());
        $this->assertTrue($video->isExternalLink());
        $this->assertTrue($link->isExternalLink());
        $this->assertTrue($doc->isExternalLink());
    }

    /** @test */
    public function course_content_identifies_platform()
    {
        $youtube = CourseContent::factory()->make(['external_link' => 'https://www.youtube.com/watch?v=123']);
        $youtu_be = CourseContent::factory()->make(['external_link' => 'https://youtu.be/123']);
        $drive = CourseContent::factory()->make(['external_link' => 'https://drive.google.com/file/d/123']);
        $dropbox = CourseContent::factory()->make(['external_link' => 'https://dropbox.com/s/123']);
        $onedrive = CourseContent::factory()->make(['external_link' => 'https://onedrive.live.com/view?id=123']);
        $vimeo = CourseContent::factory()->make(['external_link' => 'https://vimeo.com/123']);
        $other = CourseContent::factory()->make(['external_link' => 'https://example.com']);
        $empty = CourseContent::factory()->make(['external_link' => null]);

        $this->assertEquals('YouTube', $youtube->platform);
        $this->assertEquals('YouTube', $youtu_be->platform);
        $this->assertEquals('Google Drive', $drive->platform);
        $this->assertEquals('Dropbox', $dropbox->platform);
        $this->assertEquals('OneDrive', $onedrive->platform);
        $this->assertEquals('Vimeo', $vimeo->platform);
        $this->assertEquals('External Link', $other->platform);
        $this->assertNull($empty->platform);
    }

    /** @test */
    public function course_content_generates_embed_url()
    {
        $youtube = CourseContent::factory()->make(['external_link' => 'https://www.youtube.com/watch?v=xyz123']);
        $shortYoutube = CourseContent::factory()->make(['external_link' => 'https://youtu.be/xyz123']);
        $other = CourseContent::factory()->make(['external_link' => 'https://example.com']);

        $this->assertEquals('https://www.youtube.com/embed/xyz123', $youtube->embed_url);
        $this->assertEquals('https://www.youtube.com/embed/xyz123', $shortYoutube->embed_url);
        $this->assertEquals('https://example.com', $other->embed_url);
    }
}
