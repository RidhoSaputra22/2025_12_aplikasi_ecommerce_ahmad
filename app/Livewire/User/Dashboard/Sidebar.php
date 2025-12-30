<?php

namespace App\Livewire\User\Dashboard;

use Livewire\Component;

class Sidebar extends Component
{
    public string $tab = 'profile';

    public function render()
    {
        return view('user.dashboard.sidebar');
    }
}
