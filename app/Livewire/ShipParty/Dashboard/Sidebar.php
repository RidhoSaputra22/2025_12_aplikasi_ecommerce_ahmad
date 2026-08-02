<?php

namespace App\Livewire\ShipParty\Dashboard;

use App\Enums\ShipmentStatus;
use App\Models\Shipment;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Sidebar extends Component
{
    public string $tab = 'overview';

    public function getPendingRequestCountProperty(): int
    {
        $courier = Auth::user()?->managedShipmentCourier;

        if (! $courier) {
            return 0;
        }

        return Shipment::query()
            ->where('shipment_courier_id', $courier->id)
            ->where('status', ShipmentStatus::Pending)
            ->count();
    }

    public function render()
    {
        return view('ship-party.dashboard.sidebar');
    }
}
