<?php

namespace App\Livewire\User\Auth;

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
        ];

        if (Auth::attempt($credentials)) {
            session()->regenerate();

            return redirect()->intended('/');
        }

        $this->addError('email', 'Email atau password salah.');
    }

    public function render()
    {
        return view('user.auth.login')->extends('layouts.app')->section('content');
    }
}
