<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

new class extends Component
{
    public string $email = '';

    public string $password = '';

    public bool $remember = false;

    public function login(): void
    {
        $credentials = $this->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $this->remember)) {
            throw ValidationException::withMessages([
                'email' => 'These credentials do not match our records.',
            ]);
        }

        session()->regenerate();

        $this->redirectRoute('portal', navigate: true);
    }
};
?>

<section class="auth-card">
    <div class="auth-brand">
        <span class="auth-mark">SS</span>
        <div>
            <h1>SS Rentals Portal</h1>
            <p>Sign in to continue</p>
        </div>
    </div>

    <form wire:submit="login" class="auth-form">
        <label class="field">
            <span>Email</span>
            <input
                wire:model="email"
                type="email"
                autocomplete="email"
                autofocus
                required
            >
            @error('email')
                <small>{{ $message }}</small>
            @enderror
        </label>

        <label class="field">
            <span>Password</span>
            <input
                wire:model="password"
                type="password"
                autocomplete="current-password"
                required
            >
            @error('password')
                <small>{{ $message }}</small>
            @enderror
        </label>

        <label class="remember-row">
            <input wire:model="remember" type="checkbox">
            <span>Remember me</span>
        </label>

        <button type="submit" class="primary-button" wire:loading.attr="disabled">
            <span wire:loading.remove>Sign in</span>
            <span wire:loading>Signing in...</span>
        </button>
    </form>
</section>
