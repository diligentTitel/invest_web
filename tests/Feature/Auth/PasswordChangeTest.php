<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordChangeTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_change_password_after_confirming_current_password(): void
    {
        $user = User::factory()->create(['password' => 'current-password']);
        $this->actingAs($user);

        $this->put(route('password.change.update'), [
            'current_password' => 'current-password',
            'password' => 'a-new-strong-password-2026',
            'password_confirmation' => 'a-new-strong-password-2026',
        ])->assertRedirect()
            ->assertSessionHas('status', 'Password updated successfully.');

        $this->assertTrue(Hash::check('a-new-strong-password-2026', $user->fresh()->password));
    }

    public function test_password_change_requires_the_correct_current_password(): void
    {
        $user = User::factory()->create(['password' => 'current-password']);
        $this->actingAs($user);

        $this->put(route('password.change.update'), [
            'current_password' => 'incorrect-password',
            'password' => 'a-new-strong-password-2026',
            'password_confirmation' => 'a-new-strong-password-2026',
        ])->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check('current-password', $user->fresh()->password));
    }
}
