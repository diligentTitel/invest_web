<?php

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $permission = Permission::query()->firstOrCreate(
            ['name' => 'investors.manage'],
            ['description' => 'Manage investor account access.'],
        );

        Role::query()
            ->whereIn('slug', ['administrator', 'super_administrator'])
            ->get()
            ->each(fn (Role $role) => $role->permissions()->syncWithoutDetaching([$permission->id]));
    }

    public function down(): void
    {
        $permissionId = DB::table('permissions')->where('name', 'investors.manage')->value('id');

        if ($permissionId === null) {
            return;
        }

        $roleIds = DB::table('roles')->whereIn('slug', ['administrator', 'super_administrator'])->pluck('id');
        DB::table('permission_role')->where('permission_id', $permissionId)->whereIn('role_id', $roleIds)->delete();

        if (! DB::table('permission_role')->where('permission_id', $permissionId)->exists()) {
            DB::table('permissions')->where('id', $permissionId)->delete();
        }
    }
};
