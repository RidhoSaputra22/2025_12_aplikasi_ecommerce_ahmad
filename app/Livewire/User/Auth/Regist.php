<?php

namespace App\Livewire\User\Auth;

use App\Models\Role;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
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

    #[Validate('required|min:8|confirmed', message: 'Kata sandi minimal 8 karakter dan konfirmasi harus sama.')]
    public string $password = '';

    #[Validate('required|min:8', message: 'Konfirmasi kata sandi wajib diisi.')]
    public string $password_confirmation = '';

    #[Validate('required|in:customer,vendor', message: 'Pilih peran akun Anda.')]
    public string $role = 'customer';

    public function regist()
    {
        $this->nama = trim($this->nama);
        $this->email = trim(mb_strtolower($this->email));

        // Validasi input
        $this->validate();

        $userRole = Role::query()->where('name', $this->role)->first();
        if (! $userRole) {
            throw ValidationException::withMessages([
                'role' => 'Peran akun tidak tersedia. Hubungi administrator.',
            ]);
        }

        DB::transaction(function () use ($userRole): void {
            $user = User::query()->create([
                'name' => $this->nama,
                'email' => $this->email,
                'phone' => $this->phone ?: null,
                'password' => bcrypt($this->password),
            ]);

            UserRole::create([
                'user_id' => $user->id,
                'role_id' => $userRole->id,
            ]);

            if ($this->role === 'vendor') {
                $user->vendor()->create([
                    'store_name' => $this->nama,
                ]);
            }
        });

        $roleLabel = $this->role === 'vendor' ? 'Vendor' : 'Customer';
        session()->flash('message', 'Pendaftaran berhasil sebagai '.$roleLabel.'! Silakan login.');

        return redirect()->route('user.login');
    }

    public function render()
    {
        return view('user.auth.regist')->extends('layouts.app')->section('content');
    }
}
