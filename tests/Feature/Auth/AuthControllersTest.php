<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Event;

class AuthControllersTest extends TestCase
{
    use RefreshDatabase;

    /* ================= Registration Tests ================= */

    /** @test */
    public function registration_page_can_be_rendered()
    {
        $response = $this->get(route('register'));
        
        $response->assertStatus(200);
        $response->assertViewIs('auth.register');
    }

    /** @test */
    public function new_users_can_register()
    {
        Event::fake();

        $response = $this->post(route('register'), [
            'name' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard'));
        
        $this->assertDatabaseHas('users', [
            'name' => 'testuser',
            'email' => 'test@example.com',
        ]);
        
        Event::assertDispatched(Registered::class);
        
        $this->assertAuthenticated();
    }

    /** @test */
    public function registration_requires_name()
    {
        $response = $this->post(route('register'), [
            'name' => '',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('name');
        $this->assertGuest();
    }

    /** @test */
    public function registration_requires_valid_email()
    {
        $response = $this->post(route('register'), [
            'name' => 'testuser',
            'email' => 'invalid-email',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /** @test */
    public function registration_requires_unique_email()
    {
        User::factory()->create(['email' => 'test@example.com']);

        $response = $this->post(route('register'), [
            'name' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /** @test */
    public function registration_requires_password_confirmation()
    {
        $response = $this->post(route('register'), [
            'name' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertGuest();
    }

    /** @test */
    public function registration_sets_full_name_to_name_by_default()
    {
        $response = $this->post(route('register'), [
            'name' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertDatabaseHas('users', [
            'name' => 'testuser',
            'full_name' => 'testuser',
            'email' => 'test@example.com',
        ]);
    }

    /* ================= Login Tests ================= */

    /** @test */
    public function login_page_can_be_rendered()
    {
        $response = $this->get(route('login'));
        
        $response->assertStatus(200);
        $response->assertViewIs('auth.login');
    }

    /** @test */
    public function users_can_authenticate_with_valid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123')
        ]);

        $response = $this->post(route('login'), [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
    }

    /** @test */
    public function users_cannot_authenticate_with_invalid_password()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123')
        ]);

        $response = $this->post(route('login'), [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors();
        $this->assertGuest();
    }

    /** @test */
    public function users_cannot_authenticate_with_non_existent_email()
    {
        $response = $this->post(route('login'), [
            'email' => 'nonexistent@example.com',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors();
        $this->assertGuest();
    }

    /** @test */
    public function authenticated_users_can_logout()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('logout'));

        $response->assertRedirect('/');
        $this->assertGuest();
    }

    /* ================= Password Reset Tests ================= */

    /** @test */
    public function password_reset_link_page_can_be_rendered()
    {
        $response = $this->get(route('password.request'));
        
        $response->assertStatus(200);
        $response->assertViewIs('auth.forgot-password');
    }

    /** @test */
    public function password_reset_link_can_be_requested()
    {
        $user = User::factory()->create();

        $response = $this->post(route('password.email'), [
            'email' => $user->email,
        ]);

        $response->assertSessionHas('status');
    }

    /** @test */
    public function password_reset_link_requires_valid_email()
    {
        $response = $this->post(route('password.email'), [
            'email' => 'invalid-email',
        ]);

        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function password_reset_page_can_be_rendered()
    {
        $user = User::factory()->create();
        $token = Password::createToken($user);

        $response = $this->get(route('password.reset', ['token' => $token]));
        
        $response->assertStatus(200);
        $response->assertViewIs('auth.reset-password');
    }

    /** @test */
    public function password_can_be_reset_with_valid_token()
    {
        $user = User::factory()->create();
        $token = Password::createToken($user);

        $response = $this->post(route('password.store'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('status');
        
        $user->refresh();
        $this->assertTrue(Hash::check('newpassword123', $user->password));
    }

    /** @test */
    public function password_reset_requires_token()
    {
        $user = User::factory()->create();

        $response = $this->post(route('password.store'), [
            'token' => '',
            'email' => $user->email,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertSessionHasErrors('token');
    }

    /** @test */
    public function password_reset_requires_password_confirmation()
    {
        $user = User::factory()->create();
        $token = Password::createToken($user);

        $response = $this->post(route('password.store'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'newpassword123',
            'password_confirmation' => 'different',
        ]);

        $response->assertSessionHasErrors('password');
    }

    /* ================= Password Update Tests ================= */

    /** @test */
    public function authenticated_user_can_update_password()
    {
        $user = User::factory()->create([
            'password' => Hash::make('oldpassword')
        ]);

        $response = $this->actingAs($user)->put(route('password.update'), [
            'current_password' => 'oldpassword',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status', 'password-updated');
        
        $user->refresh();
        $this->assertTrue(Hash::check('newpassword123', $user->password));
    }

    /** @test */
    public function password_update_requires_correct_current_password()
    {
        $user = User::factory()->create([
            'password' => Hash::make('oldpassword')
        ]);

        $response = $this->actingAs($user)->put(route('password.update'), [
            'current_password' => 'wrongpassword',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertSessionHasErrors('current_password');
    }

    /** @test */
    public function password_update_requires_confirmation()
    {
        $user = User::factory()->create([
            'password' => Hash::make('oldpassword')
        ]);

        $response = $this->actingAs($user)->put(route('password.update'), [
            'current_password' => 'oldpassword',
            'password' => 'newpassword123',
            'password_confirmation' => 'different',
        ]);

        $response->assertSessionHasErrors('password');
    }

    /* ================= Email Verification Tests ================= */

    /** @test */
    public function email_verification_page_can_be_rendered()
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $response = $this->actingAs($user)->get(route('verification.notice'));
        
        $response->assertStatus(200);
        $response->assertViewIs('auth.verify-email');
    }

    /** @test */
    public function email_can_be_verified()
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        $user->refresh();
        $this->assertNotNull($user->email_verified_at);
        $response->assertRedirect(route('dashboard') . '?verified=1');
    }

    /** @test */
    public function already_verified_email_redirects_to_dashboard()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        $response->assertRedirect(route('dashboard') . '?verified=1');
    }

    /* ================= Password Confirmation Tests ================= */

    /** @test */
    public function password_confirmation_page_can_be_rendered()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('password.confirm'));
        
        $response->assertStatus(200);
        $response->assertViewIs('auth.confirm-password');
    }

    /** @test */
    public function password_can_be_confirmed()
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123')
        ]);

        $response = $this->actingAs($user)->post(route('password.confirm'), [
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('auth.password_confirmed_at');
    }

    /** @test */
    public function password_confirmation_requires_correct_password()
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123')
        ]);

        $response = $this->actingAs($user)->post(route('password.confirm'), [
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors('password');
    }

    /* ================= Guest Middleware Tests ================= */

    /** @test */
    public function authenticated_users_are_redirected_from_guest_pages()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->get(route('login'));
        $response->assertRedirect(route('dashboard'));

        $response = $this->get(route('register'));
        $response->assertRedirect(route('dashboard'));
    }

    /* ================= Auth Middleware Tests ================= */

    /** @test */
    public function guests_cannot_access_protected_routes()
    {
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));

        $response = $this->get(route('profile.edit'));
        $response->assertRedirect(route('login'));
    }
}

// Add this to use URL facade
use Illuminate\Support\Facades\URL;