<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\InvestorProfile;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use RuntimeException;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:190', 'unique:users,email'],
            'password' => ['required', 'confirmed', 'string', 'min:12', 'max:200'],
            'terms' => ['accepted'],
        ]);

        $user = DB::transaction(function () use ($validated): User {
            $investorRole = Role::query()->where('slug', 'investor')->first();
            if (! $investorRole) {
                throw new RuntimeException('Investor roles have not been seeded. Run php artisan db:seed.');
            }

            $user = User::query()->create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
            ]);
            $user->forceFill(['role_id' => $investorRole->id])->save();

            InvestorProfile::query()->create(['user_id' => $user->id]);

            return $user;
        });

        event(new Registered($user));
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('verification.notice');
    }
}