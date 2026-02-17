<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test login page is accessible.
     */
    public function test_login_page_is_accessible(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    /**
     * Test user can login with correct credentials.
     */
    public function test_user_can_login_with_correct_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/dashboard');
    }

    /**
     * Test admin is redirected to admin dashboard after login.
     */
    public function test_admin_is_redirected_to_admin_dashboard(): void
    {
        $admin = User::factory()->admin()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->post('/login', [
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/admin');
    }

    /**
     * Test user cannot login with incorrect password.
     */
    public function test_user_cannot_login_with_incorrect_password(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    /**
     * Test user cannot login with non-existent email.
     */
    public function test_user_cannot_login_with_nonexistent_email(): void
    {
        $response = $this->post('/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    /**
     * Test registration page is accessible.
     */
    public function test_registration_page_is_accessible(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    /**
     * Test user can register with valid data.
     */
    public function test_user_can_register_with_valid_data(): void
    {
        // Mock the reCAPTCHA verification
        Http::fake([
            'www.google.com/recaptcha/*' => Http::response(['success' => true], 200),
        ]);

        $response = $this->post('/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'waiver_accepted' => '1',
            'g-recaptcha-response' => 'valid-token',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/dashboard');

        $this->assertDatabaseHas('users', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
        ]);
    }

    /**
     * Test registration fails with invalid reCAPTCHA.
     */
    public function test_registration_fails_with_invalid_recaptcha(): void
    {
        Http::fake([
            'www.google.com/recaptcha/*' => Http::response(['success' => false], 200),
        ]);

        $response = $this->post('/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'waiver_accepted' => '1',
            'g-recaptcha-response' => 'invalid-token',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('g-recaptcha-response');
    }

    /**
     * Test registration requires all fields.
     */
    public function test_registration_requires_all_fields(): void
    {
        $response = $this->post('/register', []);

        $response->assertSessionHasErrors(['first_name', 'last_name', 'email', 'password', 'waiver_accepted', 'g-recaptcha-response']);
    }

    /**
     * Test registration fails with duplicate email.
     */
    public function test_registration_fails_with_duplicate_email(): void
    {
        User::factory()->create(['email' => 'existing@example.com']);

        Http::fake([
            'www.google.com/recaptcha/*' => Http::response(['success' => true], 200),
        ]);

        $response = $this->post('/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'existing@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'waiver_accepted' => '1',
            'g-recaptcha-response' => 'valid-token',
        ]);

        $response->assertSessionHasErrors('email');
    }

    /**
     * Test registration fails with weak password.
     */
    public function test_registration_fails_with_weak_password(): void
    {
        $response = $this->post('/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'password' => 'short',
            'password_confirmation' => 'short',
            'waiver_accepted' => '1',
            'g-recaptcha-response' => 'valid-token',
        ]);

        $response->assertSessionHasErrors('password');
    }

    /**
     * Test authenticated user can logout.
     */
    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }

    /**
     * Test guest cannot access dashboard.
     */
    public function test_guest_cannot_access_dashboard(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }

    /**
     * Test authenticated user can access dashboard.
     */
    public function test_authenticated_user_can_access_dashboard(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
    }

    /**
     * Test non-admin cannot access admin pages.
     */
    public function test_non_admin_cannot_access_admin_pages(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $response = $this->actingAs($user)->get('/admin');

        $response->assertStatus(403);
    }

    /**
     * Test admin can access admin pages.
     */
    public function test_admin_can_access_admin_pages(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertStatus(200);
    }

    /**
     * Test remember me functionality.
     */
    public function test_remember_me_functionality(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
            'remember' => true,
        ]);

        $this->assertAuthenticated();
        // The remember token should be set
        $user->refresh();
        $this->assertNotNull($user->remember_token);
    }
}
