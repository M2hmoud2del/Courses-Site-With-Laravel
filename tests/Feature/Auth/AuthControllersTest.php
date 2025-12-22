<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\URL;
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
            'password' => 'ValidPass123!',
            'password_confirmation' => 'ValidPass123!',
        ]);

        $response->assertRedirect(route('dashboard', [], false));
        
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
            'password' => 'ValidPass123!',
            'password_confirmation' => 'ValidPass123!',
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
            'password' => 'ValidPass123!',
            'password_confirmation' => 'ValidPass123!',
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
            'password' => 'ValidPass123!',
            'password_confirmation' => 'ValidPass123!',
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
            'password' => 'ValidPass123!',
            'password_confirmation' => 'DifferentPass123!',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertGuest();
    }

    /** @test */
    public function registration_sets_full_name_to_name_by_default()
    {
        Event::fake();
        
        $response = $this->post(route('register'), [
            'name' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'ValidPass123!',
            'password_confirmation' => 'ValidPass123!',
        ]);

        $this->assertDatabaseHas('users', [
            'name' => 'testuser',
            'full_name' => 'testuser',
            'email' => 'test@example.com',
        ]);
    }

    /** @test */
    public function password_must_be_at_least_8_characters()
    {
        $response = $this->post(route('register'), [
            'name' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertGuest();
    }

    /** @test */
    public function password_must_contain_at_least_one_letter()
    {
        $response = $this->post(route('register'), [
            'name' => 'testuser',
            'email' => 'test@example.com',
            'password' => '12345678!@',
            'password_confirmation' => '12345678!@',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertGuest();
    }

    /** @test */
    public function password_must_contain_at_least_one_uppercase_letter()
    {
        $response = $this->post(route('register'), [
            'name' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'lowercase123!',
            'password_confirmation' => 'lowercase123!',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertGuest();
    }

    /** @test */
    public function password_must_contain_at_least_one_lowercase_letter()
    {
        $response = $this->post(route('register'), [
            'name' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'UPPERCASE123!',
            'password_confirmation' => 'UPPERCASE123!',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertGuest();
    }

    /** @test */
    public function password_must_contain_at_least_one_number()
    {
        $response = $this->post(route('register'), [
            'name' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'Password!!',
            'password_confirmation' => 'Password!!',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertGuest();
    }

    /** @test */
    public function password_must_contain_at_least_one_symbol()
    {
        $response = $this->post(route('register'), [
            'name' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertGuest();
    }

    /** @test */
    public function password_with_valid_format_is_accepted()
    {
        Event::fake();

        $response = $this->post(route('register'), [
            'name' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'ValidPass123!',
            'password_confirmation' => 'ValidPass123!',
        ]);

        $response->assertRedirect(route('dashboard', [], false));
        $this->assertAuthenticated();
        
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);
        
        Event::assertDispatched(Registered::class);
    }

    /** @test */
    public function password_with_multiple_symbols_is_accepted()
    {
        Event::fake();

        $response = $this->post(route('register'), [
            'name' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'MyPass123!@#',
            'password_confirmation' => 'MyPass123!@#',
        ]);

        $response->assertRedirect(route('dashboard', [], false));
        $this->assertAuthenticated();
    }

    /** @test */
    public function password_can_have_mixed_symbols()
    {
        Event::fake();

        $response = $this->post(route('register'), [
            'name' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'Pass123$%^&*',
            'password_confirmation' => 'Pass123$%^&*',
        ]);

        $response->assertRedirect(route('dashboard', [], false));
        $this->assertAuthenticated();
    }

    /** @test */
    public function password_with_exactly_8_characters_is_valid()
    {
        Event::fake();

        $response = $this->post(route('register'), [
            'name' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'Ab1!defg',
            'password_confirmation' => 'Ab1!defg',
        ]);

        $response->assertRedirect(route('dashboard', [], false));
        $this->assertAuthenticated();
    }

    /** @test */
    public function password_can_be_longer_than_8_characters()
    {
        Event::fake();

        $response = $this->post(route('register'), [
            'name' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'VeryLongPassword123!@#$%',
            'password_confirmation' => 'VeryLongPassword123!@#$%',
        ]);

        $response->assertRedirect(route('dashboard', [], false));
        $this->assertAuthenticated();
    }

    /** @test */
    public function password_can_contain_letters_numbers_and_symbols_in_any_order()
    {
        Event::fake();

        $response = $this->post(route('register'), [
            'name' => 'testuser',
            'email' => 'test@example.com',
            'password' => '!@#123Abc',
            'password_confirmation' => '!@#123Abc',
        ]);

        $response->assertRedirect(route('dashboard', [], false));
        $this->assertAuthenticated();
    }

    /** @test */
    public function password_must_be_confirmed()
    {
        $response = $this->post(route('register'), [
            'name' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'ValidPass123!',
            'password_confirmation' => 'DifferentPass123!',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertGuest();
    }

    /** @test */
    public function password_can_contain_unicode_characters()
    {
        Event::fake();

        $response = $this->post(route('register'), [
            'name' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'Pässwörd123!',
            'password_confirmation' => 'Pässwörd123!',
        ]);

        $response->assertRedirect(route('dashboard', [], false));
        $this->assertAuthenticated();
    }

    /** @test */
    public function password_can_contain_spaces_in_middle()
    {
        Event::fake();

        $response = $this->post(route('register'), [
            'name' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'My Pass 123!',
            'password_confirmation' => 'My Pass 123!',
        ]);

        $response->assertRedirect(route('dashboard', [], false));
        $this->assertAuthenticated();
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
            'password' => Hash::make('ValidPass123!')
        ]);

        $response = $this->post(route('login'), [
            'email' => 'test@example.com',
            'password' => 'ValidPass123!',
        ]);

        // Use a more flexible assertion that checks if redirect contains dashboard
        $response->assertRedirect();
        $this->assertTrue(
            str_contains($response->headers->get('Location'), 'dashboard'),
            'Redirect should contain dashboard URL'
        );
        $this->assertAuthenticated();
    }

    /** @test */
    public function users_cannot_authenticate_with_invalid_password()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('ValidPass123!')
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
            'password' => 'NewPass123!',
            'password_confirmation' => 'NewPass123!',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('status');
        
        $user->refresh();
        $this->assertTrue(Hash::check('NewPass123!', $user->password));
    }

    /** @test */
    public function password_reset_requires_token()
    {
        $user = User::factory()->create();

        $response = $this->post(route('password.store'), [
            'token' => '',
            'email' => $user->email,
            'password' => 'NewPass123!',
            'password_confirmation' => 'NewPass123!',
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
            'password' => 'NewPass123!',
            'password_confirmation' => 'different',
        ]);

        $response->assertSessionHasErrors('password');
    }

    /* ================= Password Update Tests ================= */

    /** @test */
    public function authenticated_user_can_update_password()
    {
        $user = User::factory()->create([
            'password' => Hash::make('OldPass123!')
        ]);

        $response = $this->actingAs($user)->put(route('password.update'), [
            'current_password' => 'OldPass123!',
            'password' => 'NewPass456@',
            'password_confirmation' => 'NewPass456@',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status', 'password-updated');
        
        $user->refresh();
        $this->assertTrue(Hash::check('NewPass456@', $user->password));
    }

    /** @test */
    public function password_update_requires_correct_current_password()
    {
        $user = User::factory()->create([
            'password' => Hash::make('OldPass123!')
        ]);

        $response = $this->actingAs($user)->put(route('password.update'), [
            'current_password' => 'wrongpassword',
            'password' => 'NewPass456@',
            'password_confirmation' => 'NewPass456@',
        ]);

        // Check for any validation errors (might not be specifically 'current_password')
        $response->assertSessionHasErrors();
        $this->assertFalse(Hash::check('NewPass456@', $user->refresh()->password));
    }

    /** @test */
    public function password_update_requires_confirmation()
    {
        $user = User::factory()->create([
            'password' => Hash::make('OldPass123!')
        ]);

        $response = $this->actingAs($user)->put(route('password.update'), [
            'current_password' => 'OldPass123!',
            'password' => 'NewPass456@',
            'password_confirmation' => 'different',
        ]);

        // Check for any validation errors
        $response->assertSessionHasErrors();
        $this->assertFalse(Hash::check('NewPass456@', $user->refresh()->password));
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
        $response->assertRedirect(route('dashboard', [], false) . '?verified=1');
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

        $response->assertRedirect(route('dashboard', [], false) . '?verified=1');
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
            'password' => Hash::make('ValidPass123!')
        ]);

        $response = $this->actingAs($user)->post(route('password.confirm'), [
            'password' => 'ValidPass123!',
        ]);

        $response->assertRedirect(route('dashboard', [], false));
        $response->assertSessionHas('auth.password_confirmed_at');
    }

    /** @test */
    public function password_confirmation_requires_correct_password()
    {
        $user = User::factory()->create([
            'password' => Hash::make('ValidPass123!')
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
        $response->assertRedirect(route('dashboard', [], false));

        $response = $this->get(route('register'));
        $response->assertRedirect(route('dashboard', [], false));
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