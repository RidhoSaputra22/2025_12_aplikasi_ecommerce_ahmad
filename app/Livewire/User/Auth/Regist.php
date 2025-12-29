<?php

namespace App\Livewire\User\Auth;

use App\Models\Role;
use App\Models\User;
use Livewire\Component;
use App\Models\UserRole;
use Livewire\Attributes\Validate;

class Regist extends Component
{

    #[Validate('required|min:3', message: 'Nama lengkap harus diisi dan minimal 3 karakter.')]
    public string $nama = '';

    #[Validate('required|email|unique:users,email', message: 'Email harus diisi, valid, dan belum terdaftar.')]
    public string $email = '';
    #[Validate('required|min:6', message: 'Kata sandi harus diisi dan minimal 6 karakter.')]
    public string $password = '';

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
            'password' => bcrypt($this->password),
        ]);

        $userRole = Role::where('name', 'user')->first();

        UserRole::create([
            'user_id' => $user->id,
            'role_id' => $userRole->id,
        ]);


        // Setelah pendaftaran berhasil, arahkan pengguna ke halaman lain
        session()->flash('message', 'Pendaftaran berhasil! Silakan login.');
        return redirect()->route('user.login');
    }

    public function render()
    {
        return view('user.auth.regist')->extends('layouts.app')->section('content');
    }
}
