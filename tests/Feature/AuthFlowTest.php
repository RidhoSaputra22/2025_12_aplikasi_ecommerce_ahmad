<?php

namespace Tests\Feature;

use App\Enums\UserStatus;
use App\Livewire\User\Auth\Login;
use App\Livewire\User\Auth\Regist;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class AuthFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_creates_the_user_and_role_atomically(): void
    {
        Role::query()->create(['name' => 'customer']);

        Livewire::test(Regist::class)
            ->set('nama', 'Customer Baru')
            ->set('email', 'customer.baru@example.test')
            ->set('phone', '08123456789')
            ->set('password', 'password-aman')
            ->set('password_confirmation', 'password-aman')
            ->set('role', 'customer')
            ->call('regist')
            ->assertHasNoErrors()
            ->assertRedirect(route('user.login'));

        $user = User::query()->where('email', 'customer.baru@example.test')->firstOrFail();
        $this->assertSame('customer', $user->role?->name);
    }

    public function test_suspended_user_cannot_log_in(): void
    {
        User::factory()->create([
            'email' => 'blocked@example.test',
            'password' => Hash::make('password-aman'),
            'status' => UserStatus::Suspended,
        ]);

        Livewire::test(Login::class)
            ->set('email', 'blocked@example.test')
            ->set('password', 'password-aman')
            ->call('login')
            ->assertHasErrors('email');

        $this->assertGuest();
    }

    public function test_logout_requires_post_and_invalidates_authentication(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('user.logout'))
            ->assertMethodNotAllowed();

        $this->actingAs($user)
            ->post(route('user.logout'))
            ->assertRedirect(route('user.login'));

        $this->assertGuest();
    }
}
