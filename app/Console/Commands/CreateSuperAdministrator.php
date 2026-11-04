<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CreateSuperAdministrator extends Command
{
    protected $signature = 'platform:create-super-admin {email? : Email address for the new administrator}';

    protected $description = 'Create the first Commonwealth super administrator without a default password';

    public function handle(): int
    {
        $role = Role::query()->where('slug', 'super_administrator')->first();

        if (! $role) {
            $this->error('Administrator roles are missing. Run the role and permission seeder first.');

            return self::FAILURE;
        }

        $name = trim((string) $this->ask('Administrator name'));
        $email = strtolower(trim((string) ($this->argument('email') ?: $this->ask('Administrator email'))));

        if ($name === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Enter a name and a valid email address.');

            return self::INVALID;
        }

        if (User::query()->where('email', $email)->exists()) {
            $this->error('That email address already belongs to an account.');

            return self::FAILURE;
        }

        $password = (string) $this->secret('Choose a unique password (at least 16 characters)');
        $confirmation = (string) $this->secret('Confirm the password');

        if (strlen($password) < 16 || ! hash_equals($password, $confirmation)) {
            $this->error('The password must be at least 16 characters, and both entries must match.');

            return self::FAILURE;
        }

        $user = DB::transaction(function () use ($name, $email, $password, $role): User {
            $user = new User();
            $user->name = $name;
            $user->email = $email;
            $user->password = $password;
            $user->forceFill([
                'role_id' => $role->id,
                'status' => 'active',
                'is_demo' => false,
                'email_verified_at' => now(),
            ]);
            $user->save();

            return $user;
        });

        $this->info("Super administrator created for {$user->email}.");
        $this->warn('Store the password in your password manager. It was not written to application output or source files.');

        return self::SUCCESS;
    }
}
