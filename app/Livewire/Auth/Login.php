<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Validate;
use Livewire\Component;

/**
 * লগইন — tenant প্যানেল ও central উভয়ের জন্য।
 *
 * Which user pool is searched is decided by TenantUserProvider from the
 * current host, so this component needs no tenant logic of its own.
 */
class Login extends Component
{
    #[Validate('required|string')]
    public string $email = '';

    #[Validate('required|string')]
    public string $password = '';

    public bool $remember = false;

    public function login(): void
    {
        $this->validate([
            'email' => 'required|string',
            'password' => 'required|string',
        ], attributes: [
            'email' => 'ইমেইল',
            'password' => 'পাসওয়ার্ড',
        ]);

        $this->ensureIsNotRateLimited();

        // Allow signing in with either email or username.
        $field = filter_var($this->email, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        if (! Auth::attempt([$field => $this->email, 'password' => $this->password, 'is_active' => true], $this->remember)) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => 'এই তথ্য দিয়ে কোনো অ্যাকাউন্ট পাওয়া যায়নি।',
            ]);
        }

        RateLimiter::clear($this->throttleKey());
        session()->regenerate();

        $this->redirectIntended($this->homeRoute(), navigate: false);
    }

    private function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => "অনেকবার চেষ্টা করা হয়েছে। {$seconds} সেকেন্ড পর আবার চেষ্টা করুন।",
        ]);
    }

    private function throttleKey(): string
    {
        $tenant = tenancy()->initialized ? tenant()->getTenantKey() : 'central';

        return 'login:'.$tenant.'|'.mb_strtolower($this->email).'|'.request()->ip();
    }

    private function homeRoute(): string
    {
        return tenancy()->initialized ? route('tenant.dashboard') : route('central.dashboard');
    }

    /**
     * ডেমো লগইন তথ্য — শুধু local/staging-এ।
     *
     * Guarded on the environment, never on a config flag, so these can never
     * be switched on in production by accident.
     *
     * @return list<array{label: string, email: string, password: string}>
     */
    public function demoCredentials(): array
    {
        if (! app()->environment(['local', 'testing'])) {
            return [];
        }

        if (tenancy()->initialized) {
            $admin = User::query()
                ->where('tenant_id', tenant()->getTenantKey())
                ->orderBy('id')
                ->first();

            return $admin === null ? [] : [[
                'label' => 'মাদরাসা অ্যাডমিন',
                'email' => $admin->email,
                'password' => 'password',
            ]];
        }

        $superAdmin = User::query()->whereNull('tenant_id')->orderBy('id')->first();

        return $superAdmin === null ? [] : [[
            'label' => 'সুপার অ্যাডমিন',
            'email' => $superAdmin->email,
            'password' => 'password',
        ]];
    }

    /**
     * ডেমো তথ্য দিয়ে ফর্ম পূরণ করে।
     */
    public function fillDemo(string $email, string $password): void
    {
        $this->email = $email;
        $this->password = $password;
    }

    public function render(): View
    {
        return view('livewire.auth.login', [
            'demoCredentials' => $this->demoCredentials(),
        ]);
    }
}
