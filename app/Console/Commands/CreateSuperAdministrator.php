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

        if (User::query()->where('role_id', $role->id)->exists()) {
            $this->info('A super administrator already exists; no account was changed.');

            return self::SUCCESS;
        }

        $nonInteractive = ! $this->input->isInteractive();
        $name = trim((string) ($nonInteractive
            ? env('INITIAL_ADMIN_NAME')
            : $this->ask('Administrator name')));
        $email = strtolower(trim((string) ($this->argument('email') ?: ($nonInteractive
            ? env('INITIAL_ADMIN_EMAIL')
            : $this->ask('Administrator email')))));

        if ($name === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Provide INITIAL_ADMIN_NAME and a valid administrator email address.');

            return self::INVALID;
        }

        $password = (string) ($nonInteractive
            ? env('INITIAL_ADMIN_PASSWORD')
            : $this->secret('Choose a unique password (at least 16 characters)'));
        $confirmation = $nonInteractive
            ? $password
            : (string) $this->secret('Confirm the password');

        if (strlen($password) < 16 || ! hash_equals($password, $confirmation)) {
            $this->error($nonInteractive
                ? 'INITIAL_ADMIN_PASSWORD must contain at least 16 characters.'
                : 'The password must be at least 16 characters, and both entries must match.');

            return self::FAILURE;
        }

        $user = DB::transaction(function () use ($name, $email, $password, $role): User {
            $user = User::query()->firstOrNew(['email' => $email]);
            $user->name = $name;
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
