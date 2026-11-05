<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $grants = [
            'investor' => [
                'dashboard.view', 'opportunities.view', 'investments.view-own',
                'deposits.create-own', 'withdrawals.create-own', 'profile.manage-own',
            ],
            'administrator' => [
                'admin.dashboard.view', 'investors.view', 'investors.manage', 'opportunities.manage',
                'kyc.review', 'payments.view', 'deposits.review', 'withdrawals.review',
                'support.manage', 'reports.view',
            ],
            'super_administrator' => ['*'],
            'finance_manager' => [
                'admin.dashboard.view', 'payments.view', 'deposits.review',
                'withdrawals.review', 'withdrawals.approve', 'ledger.view', 'reports.export',
            ],
            'support_manager' => [
                'admin.dashboard.view', 'investors.view-limited', 'support.manage',
            ],
        ];

        $permissionNames = collect($grants)->flatten()->reject(fn (string $name) => $name === '*')->unique();
        foreach ($permissionNames as $name) {
            Permission::query()->firstOrCreate(
                ['name' => $name],
                ['description' => str($name)->replace(['.', '-'], ' ')->ucfirst()],
            );
        }

        $descriptions = [
            'investor' => 'Customer access to their own investor account.',
            'administrator' => 'Administrative operations excluding unrestricted financial authority.',
            'super_administrator' => 'System administration; sensitive financial actions still require approval controls.',
            'finance_manager' => 'Deposit, withdrawal, ledger, and finance reporting operations.',
            'support_manager' => 'Customer support access with limited investor data.',
        ];

        foreach ($grants as $slug => $roleGrants) {
            $role = Role::query()->firstOrCreate(
                ['slug' => $slug],
                ['name' => str($slug)->replace('_', ' ')->title(), 'description' => $descriptions[$slug]],
            );

            $names = $roleGrants === ['*'] ? $permissionNames : collect($roleGrants);
            $role->permissions()->syncWithoutDetaching(Permission::query()->whereIn('name', $names)->pluck('id'));
        }
    }
}