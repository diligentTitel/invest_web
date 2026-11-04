<?php

namespace Tests\Feature\Admin;

use App\Models\AuditLog;
use App\Models\InvestorProfile;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvestorManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_authorized_admin_can_search_view_and_suspend_a_real_investor_with_audit_reason(): void
    {
        $this->seed();
        $admin = $this->userWithRole('super_administrator');
        $investor = $this->userWithRole('investor', ['name' => 'Jordan Rivers', 'email' => 'jordan@example.test']);
        InvestorProfile::query()->create([
            'user_id' => $investor->id,
            'legal_name' => 'Jordan Rivers',
            'country_code' => 'NG',
            'preferred_currency' => 'NGN',
        ]);
        $demoInvestor = $this->userWithRole('investor', ['name' => 'Demo account', 'is_demo' => true]);

        $this->actingAs($admin)
            ->get(route('admin.investors.index', ['search' => 'Jordan']))
            ->assertOk()
            ->assertSee('jordan@example.test')
            ->assertDontSee('demo@example.test');

        $this->get(route('admin.investors.show', $investor))
            ->assertOk()
            ->assertSee('Jordan Rivers')
            ->assertSee('ACCOUNT ACCESS');

        $this->patch(route('admin.investors.status', $investor), [
            'status' => 'suspended',
            'reason' => 'Requested by the account owner.',
        ])->assertRedirect(route('admin.investors.show', $investor));

        $this->assertDatabaseHas('users', ['id' => $investor->id, 'status' => 'suspended']);
        $this->assertDatabaseHas('users', ['id' => $demoInvestor->id, 'status' => 'active']);
        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $admin->id,
            'action' => 'investor.suspended',
            'subject_type' => $investor->getMorphClass(),
            'subject_id' => $investor->id,
        ]);

        $event = AuditLog::query()->where('subject_id', $investor->id)->firstOrFail();
        $this->assertSame('active', $event->properties['previous_status']);
        $this->assertSame('suspended', $event->properties['new_status']);
        $this->assertSame('Requested by the account owner.', $event->properties['reason']);
    }

    public function test_investor_view_permission_does_not_grant_access_management(): void
    {
        $this->seed();
        $viewerRole = Role::query()->create(['name' => 'Investor reviewer', 'slug' => 'investor_reviewer']);
        $viewPermission = Permission::query()->where('name', 'investors.view')->firstOrFail();
        $viewerRole->permissions()->attach($viewPermission);
        $viewer = User::factory()->create();
        $viewer->forceFill(['role_id' => $viewerRole->id])->save();
        $investor = $this->userWithRole('investor');

        $this->actingAs($viewer)
            ->patch(route('admin.investors.status', $investor), [
                'status' => 'suspended',
                'reason' => 'This action is not permitted.',
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('users', ['id' => $investor->id, 'status' => 'active']);
        $this->assertDatabaseMissing('audit_logs', ['action' => 'investor.suspended']);
    }

    public function test_investor_directory_does_not_expose_administrator_accounts(): void
    {
        $this->seed();
        $admin = $this->userWithRole('super_administrator');
        $anotherAdmin = $this->userWithRole('administrator');

        $this->actingAs($admin)
            ->get(route('admin.investors.show', $anotherAdmin))
            ->assertNotFound();
    }

    private function userWithRole(string $slug, array $attributes = []): User
    {
        $role = Role::query()->where('slug', $slug)->firstOrFail();
        $user = User::factory()->create($attributes);
        $user->forceFill(['role_id' => $role->id])->save();

        return $user->refresh();
    }
}
