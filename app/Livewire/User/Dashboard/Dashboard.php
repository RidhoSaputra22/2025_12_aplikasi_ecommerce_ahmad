<?php

namespace App\Livewire\User\Dashboard;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;

#[Layout('layouts.app')]
class Dashboard extends Component
{
    #[Url]
    public string $tab = 'profile';

    public function mount(): void
    {
        if (!Auth::check()) {
            $this->redirectRoute('user.login');
            return;
        }

        if (!in_array($this->tab, ['profile', 'history'], true)) {
            $this->tab = 'profile';
        }
    }

    public function render()
    {
        return view('user.dashboard.dashboard');
    }
}
