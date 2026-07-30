<?php

namespace App\Livewire\User\Auth;

use App\Enums\UserStatus;
use App\Models\Vendor;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Validate;
use Livewire\Component;

class Login extends Component
{
    #[Validate('required|email', message: 'Email harus diisi dan harus berupa alamat email yang valid.')]
    public string $email = '';

    #[Validate('required|min:8', message: 'Kata sandi harus diisi dan minimal 8 karakter.')]
    public string $password = '';

    public function login()
    {
        $this->validate();

        $credentials = [
            'email' => $this->email,
            'password' => $this->password,
            'status' => UserStatus::Active->value,
        ];

        if (Auth::attempt($credentials)) {
            session()->regenerate();

            // Update last login
            Auth::user()->update(['last_login_at' => now()]);

            $roleName = Auth::user()->role?->name;

            if ($roleName === 'admin') {
                session()->flash('login_role', 'Admin');

                return redirect()->intended(route('filament.admin.pages.dashboard'));
            }

            if ($roleName === 'vendor' && Vendor::where('user_id', Auth::id())->exists()) {
                session()->flash('login_role', 'Vendor');
                // dd($roleName);

                return redirect()->intended(route('vendor.dashboard'));
            }

            // Customer
            session()->flash('login_role', 'Customer');

            return redirect()->intended(route('welcome'));
        }

        $this->addError('email', 'Email atau password salah.');
    }

    public function render()
    {
        return view('user.auth.login')->extends('layouts.app')->section('content');
    }
}
