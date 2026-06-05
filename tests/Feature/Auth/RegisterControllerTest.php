<?php

namespace Pterodactyl\Tests\Feature\Auth;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Notification;
use Pterodactyl\Notifications\AccountCreated;
use Pterodactyl\Tests\TestCase;

class RegisterControllerTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('recaptcha.enabled', false);
    }

    public function test_registration_creates_pending_password_user(): void
    {
        Notification::fake();

        config()->set('arix.registration.enabled', true);
        config()->set('arix.registration.allowed_domains', ['example.com']);

        $response = $this->postJson('/auth/register', [
            'email' => 'new.user@example.com',
            'username' => 'newuser',
            'first_name' => 'New',
            'last_name' => 'User',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('users', [
            'email' => 'new.user@example.com',
            'username' => 'newuser',
            'password_setup_pending' => true,
        ]);
    }

    public function test_registration_accepts_allowlisted_domain_and_subdomain(): void
    {
        Notification::fake();

        config()->set('arix.registration.enabled', true);
        config()->set('arix.registration.allowed_domains', ['example.com']);

        $allowed = $this->postJson('/auth/register', [
            'email' => 'allowed@example.com',
            'username' => 'alloweduser',
            'first_name' => 'Allow',
            'last_name' => 'Listed',
        ]);

        $subdomain = $this->postJson('/auth/register', [
            'email' => 'sub@team.example.com',
            'username' => 'subdomainuser',
            'first_name' => 'Sub',
            'last_name' => 'Domain',
        ]);

        $allowed->assertOk();
        $subdomain->assertOk();
    }

    public function test_registration_rejects_non_allowlisted_suffix(): void
    {
        config()->set('arix.registration.enabled', true);
        config()->set('arix.registration.allowed_domains', ['example.com']);

        $response = $this->postJson('/auth/register', [
            'email' => 'blocked@example.com.evil.tld',
            'username' => 'blockeduser',
            'first_name' => 'Blocked',
            'last_name' => 'User',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('errors.0.meta.source_field', 'email');
    }

    public function test_pending_password_user_cannot_log_in(): void
    {
        $user = \Pterodactyl\Models\User::factory()->create([
            'email' => 'pending@example.com',
            'username' => 'pendinguser',
            'password' => bcrypt('password123'),
            'password_setup_pending' => true,
        ]);

        $response = $this->postJson('/auth/login', [
            'user' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertStatus(400);
    }

    public function test_password_reset_clears_pending_password_flag(): void
    {
        $user = \Pterodactyl\Models\User::factory()->create([
            'password_setup_pending' => true,
        ]);

        $token = app('auth.password.broker')->createToken($user);

        $response = $this->postJson('/auth/password/reset', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'password_setup_pending' => false,
        ]);
    }

    public function test_resend_sends_setup_email_for_pending_user(): void
    {
        Notification::fake();

        config()->set('arix.registration.enabled', true);

        $user = \Pterodactyl\Models\User::factory()->create([
            'email' => 'pending@example.com',
            'password_setup_pending' => true,
        ]);

        $response = $this->postJson('/auth/register/resend', [
            'email' => $user->email,
        ]);

        $response->assertOk();
        Notification::assertSentTo($user, AccountCreated::class);
    }

    public function test_resend_returns_success_for_unknown_email_without_sending(): void
    {
        Notification::fake();

        config()->set('arix.registration.enabled', true);

        $response = $this->postJson('/auth/register/resend', [
            'email' => 'missing@example.com',
        ]);

        $response->assertOk();
        Notification::assertNothingSent();
    }
}
