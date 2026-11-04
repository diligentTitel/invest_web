<?php

namespace Tests\Feature\Auth;

use App\Models\InvestorProfile;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_creates_an_investor_profile_and_sends_verification(): void
    {
        $this->seed();
        Notification::fake();

        $response = $this->post(route('register.store'), [
            'name' => 'Jordan Davis',
            'email' => 'jordan@example.test',
            'password' => 'SecurePassphrase!2026',
            'password_confirmation' => 'SecurePassphrase!2026',
            'terms' => '1',
        ]);

        $user = User::query()->where('email', 'jordan@example.test')->firstOrFail();

        $response->assertRedirect(route('verification.notice'));
        $this->assertAuthenticatedAs($user);
        $this->assertSame('investor', $user->role->slug);
        $this->assertDatabaseHas('investor_profiles', ['user_id' => $user->id]);
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_registration_requires_terms_and_a_long_password(): void
    {
        $this->seed();

        $response = $this->post(route('register.store'), [
            'name' => 'Jordan Davis',
            'email' => 'jordan@example.test',
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertSessionHasErrors(['terms', 'password']);
        $this->assertDatabaseCount('users', 0);
    }
}