<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create([
            'password' => Hash::make('password123')
        ]);
    }

    /** @test */
    public function user_can_view_profile_edit_page()
    {
        $response = $this->actingAs($this->user)
            ->get(route('profile.edit'));
            
        $response->assertStatus(200);
        $response->assertViewIs('profile.edit');
        $response->assertViewHas('user');
    }

    /** @test */
    public function user_can_update_profile_information()
    {
        $updateData = [
            'name' => 'Updated Name',
            'full_name' => 'Updated Full Name',
            'email' => $this->user->email,
        ];

        $response = $this->actingAs($this->user)
            ->patch(route('profile.update'), $updateData);
            
        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHas('status', 'profile-updated');
        
        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'name' => 'Updated Name',
            'full_name' => 'Updated Full Name'
        ]);
    }

    /** @test */
    public function user_can_update_email()
    {
        $updateData = [
            'name' => $this->user->name,
            'full_name' => $this->user->full_name,
            'email' => 'newemail@example.com',
        ];

        $response = $this->actingAs($this->user)
            ->patch(route('profile.update'), $updateData);
            
        $response->assertRedirect(route('profile.edit'));
        
        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'email' => 'newemail@example.com',
            'email_verified_at' => null
        ]);
    }

    /** @test */
    public function user_can_upload_profile_picture()
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('avatar.jpg', 500, 500);

        $updateData = [
            'name' => $this->user->name,
            'full_name' => $this->user->full_name,
            'email' => $this->user->email,
            'profile_picture' => $file,
        ];

        $response = $this->actingAs($this->user)
            ->patch(route('profile.update'), $updateData);
            
        $response->assertRedirect(route('profile.edit'));
        
        $this->user->refresh();
        $this->assertNotNull($this->user->profile_picture);
        $this->assertStringContainsString('profile-' . $this->user->id, $this->user->profile_picture);
    }

    /** @test */
    public function old_profile_picture_is_deleted_when_uploading_new_one()
    {
        // Create a fake old profile picture file
        $oldPicturePath = public_path('private/profile-pictures/profile-' . $this->user->id . '-123456.jpg');
        
        // Make directory if not exists
        $directory = dirname($oldPicturePath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        
        // Create dummy file
        file_put_contents($oldPicturePath, 'old picture content');
        
        $this->user->profile_picture = '/private/profile-pictures/profile-' . $this->user->id . '-123456.jpg';
        $this->user->save();
        
        $this->assertTrue(file_exists($oldPicturePath));

        // Upload new picture
        $file = UploadedFile::fake()->image('new-avatar.jpg');

        $updateData = [
            'name' => $this->user->name,
            'full_name' => $this->user->full_name,
            'email' => $this->user->email,
            'profile_picture' => $file,
        ];

        $response = $this->actingAs($this->user)
            ->patch(route('profile.update'), $updateData);
            
        $response->assertRedirect(route('profile.edit'));
        
        // Old file should be deleted
        $this->assertFalse(file_exists($oldPicturePath));
        
        // Clean up
        $this->user->refresh();
        if ($this->user->profile_picture && file_exists(public_path(ltrim($this->user->profile_picture, '/')))) {
            unlink(public_path(ltrim($this->user->profile_picture, '/')));
        }
    }

    /** @test */
    public function user_can_remove_profile_picture()
    {
        // Set up profile picture
        $picturePath = public_path('private/profile-pictures/profile-' . $this->user->id . '-123456.jpg');
        
        $directory = dirname($picturePath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        
        file_put_contents($picturePath, 'picture content');
        
        $this->user->profile_picture = '/private/profile-pictures/profile-' . $this->user->id . '-123456.jpg';
        $this->user->save();

        $response = $this->actingAs($this->user)
            ->delete(route('profile.remove-picture'));
            
        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHas('status', 'profile-picture-removed');
        
        $this->user->refresh();
        $this->assertNull($this->user->profile_picture);
        $this->assertFalse(file_exists($picturePath));
    }

    /** @test */
    public function cannot_remove_non_existent_profile_picture()
    {
        $this->user->profile_picture = null;
        $this->user->save();

        $response = $this->actingAs($this->user)
            ->delete(route('profile.remove-picture'));
            
        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHas('error');
    }

    /** @test */
    public function profile_picture_validation_works()
    {
        Storage::fake('public');

        // Try to upload non-image file
        $file = UploadedFile::fake()->create('document.pdf', 1000);

        $updateData = [
            'name' => $this->user->name,
            'full_name' => $this->user->full_name,
            'email' => $this->user->email,
            'profile_picture' => $file,
        ];

        $response = $this->actingAs($this->user)
            ->patch(route('profile.update'), $updateData);
            
        $response->assertSessionHasErrors('profile_picture');
    }

    /** @test */
    public function profile_picture_size_validation_works()
    {
        Storage::fake('public');

        // Try to upload file larger than 2MB
        $file = UploadedFile::fake()->image('large.jpg')->size(3000); // 3MB

        $updateData = [
            'name' => $this->user->name,
            'full_name' => $this->user->full_name,
            'email' => $this->user->email,
            'profile_picture' => $file,
        ];

        $response = $this->actingAs($this->user)
            ->patch(route('profile.update'), $updateData);
            
        $response->assertSessionHasErrors('profile_picture');
    }

    /** @test */
    public function user_can_delete_their_account()
    {
        $response = $this->actingAs($this->user)
            ->delete(route('profile.destroy'), [
                'password' => 'password123'
            ]);
            
        $response->assertRedirect('/');
        
        $this->assertDatabaseMissing('users', ['id' => $this->user->id]);
    }

    /** @test */
    public function account_deletion_requires_correct_password()
    {
        $response = $this->actingAs($this->user)
            ->delete(route('profile.destroy'), [
                'password' => 'wrongpassword'
            ]);
            
        $response->assertSessionHasErrors('password');
        
        $this->assertDatabaseHas('users', ['id' => $this->user->id]);
    }

    /** @test */
    public function profile_picture_is_deleted_when_account_is_deleted()
    {
        // Set up profile picture
        $picturePath = public_path('private/profile-pictures/profile-' . $this->user->id . '-123456.jpg');
        
        $directory = dirname($picturePath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        
        file_put_contents($picturePath, 'picture content');
        
        $this->user->profile_picture = '/private/profile-pictures/profile-' . $this->user->id . '-123456.jpg';
        $this->user->save();

        $this->assertTrue(file_exists($picturePath));

        $response = $this->actingAs($this->user)
            ->delete(route('profile.destroy'), [
                'password' => 'password123'
            ]);
            
        $response->assertRedirect('/');
        
        // File should be deleted
        $this->assertFalse(file_exists($picturePath));
    }

    /** @test */
    public function guest_cannot_access_profile_pages()
    {
        $response = $this->get(route('profile.edit'));
        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function profile_update_validates_required_fields()
    {
        $response = $this->actingAs($this->user)
            ->patch(route('profile.update'), [
                'name' => '',
                'full_name' => '',
                'email' => '',
            ]);
            
        $response->assertSessionHasErrors(['name', 'full_name', 'email']);
    }

    /** @test */
    public function profile_update_validates_email_format()
    {
        $response = $this->actingAs($this->user)
            ->patch(route('profile.update'), [
                'name' => $this->user->name,
                'full_name' => $this->user->full_name,
                'email' => 'invalid-email',
            ]);
            
        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function profile_update_validates_unique_email()
    {
        $otherUser = User::factory()->create([
            'email' => 'other@example.com'
        ]);

        $response = $this->actingAs($this->user)
            ->patch(route('profile.update'), [
                'name' => $this->user->name,
                'full_name' => $this->user->full_name,
                'email' => 'other@example.com',
            ]);
            
        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function user_can_keep_same_email_when_updating()
    {
        $response = $this->actingAs($this->user)
            ->patch(route('profile.update'), [
                'name' => 'Updated Name',
                'full_name' => 'Updated Full Name',
                'email' => $this->user->email, // Same email
            ]);
            
        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHas('status', 'profile-updated');
        
        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'email' => $this->user->email
        ]);
    }
}