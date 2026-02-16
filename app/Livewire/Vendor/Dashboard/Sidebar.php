<?php

namespace App\Livewire\Vendor\Dashboard;

use App\Models\OrderVendor;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Sidebar extends Component
{
    public string $tab = 'overview';

    public function getNewOrderCountProperty(): int
    {
        $vendor = Auth::user()?->vendor;
        if (!$vendor) {
            return 0;
        }

        return OrderVendor::where('vendor_id', $vendor->id)
            ->where('status', 'pending')
            ->count();
    }

    public function render()
    {
        return view('vendor.dashboard.sidebar');
    }
}
