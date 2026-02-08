<?php

namespace App\Livewire\User\Auth;

use App\Models\Role;
use App\Models\User;
use App\Models\UserRole;
use Livewire\Attributes\Validate;
use Livewire\Component;

class Regist extends Component
{
    #[Validate('required|min:3', message: 'Nama lengkap harus diisi dan minimal 3 karakter.')]
    public string $nama = '';

    #[Validate('required|email|unique:users,email', message: 'Email harus diisi, valid, dan belum terdaftar.')]
    public string $email = '';

    #[Validate('nullable|string|max:20')]
    public string $phone = '';

    #[Validate('required|min:8', message: 'Kata sandi harus diisi dan minimal 8 karakter.')]
    public string $password = '';

    #[Validate('required|in:customer,vendor', message: 'Pilih peran akun Anda.')]
    public string $role = 'customer';

    public function regist()
    {
        $this->nama = trim($this->nama);
        $this->email = trim(mb_strtolower($this->email));

        // Validasi input
        $this->validate();

        // Logika pendaftaran pengguna baru
        $user = User::create([
            'name' => $this->nama,
            'email' => $this->email,
            'phone' => $this->phone ?: null,
            'password' => bcrypt($this->password),
        ]);

        $userRole = Role::where('name', $this->role)->first();

        if ($userRole) {
            UserRole::create([
                'user_id' => $user->id,
                'role_id' => $userRole->id,
            ]);
        }

        $roleLabel = $this->role === 'vendor' ? 'Vendor' : 'Customer';
        session()->flash('message', 'Pendaftaran berhasil sebagai ' . $roleLabel . '! Silakan login.');

        return redirect()->route('user.login');
    }

    public function render()
    {
        return view('user.auth.regist')->extends('layouts.app')->section('content');
    }
}
