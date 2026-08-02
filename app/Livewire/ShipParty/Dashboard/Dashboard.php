<?php

namespace App\Livewire\ShipParty\Dashboard;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.app')]
class Dashboard extends Component
{
    #[Url]
    public string $tab = 'overview';

    #[Url]
    public ?int $order_id = null;

    public function mount(): void
    {
        if (! Auth::check()) {
            $this->redirectRoute('user.login');

            return;
        }

        if (! Auth::user()->role()->where('name', 'pihak_kapal')->exists()) {
            $this->redirectRoute('user.login');

            return;
        }

        if (! Auth::user()->managedShipmentCourier) {
            $this->redirectRoute('user.login');

            return;
        }

        $allowedTabs = ['overview', 'shipments', 'order-detail', 'tracking'];

        if (! in_array($this->tab, $allowedTabs, true)) {
            $this->tab = 'overview';
        }
    }

    public function render()
    {
        return view('ship-party.dashboard.dashboard');
    }
}
