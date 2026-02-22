<?php

namespace App\Livewire\Vendor\Dashboard;

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

    #[Url]
    public ?int $product_id = null;

    public function mount(): void
    {
        if (! Auth::check()) {
            $this->redirectRoute('user.login');

            return;
        }

        $vendor = Auth::user()->vendor;
        if (! $vendor) {
            // dd('Vendor not found for the authenticated user.');
            $this->redirectRoute('user.login');

            return;
        }

        $allowedTabs = [
            'overview', 'profile', 'orders', 'order-detail',
            'products', 'product-form', 'wallet', 'bank-accounts', 'shipments', 'tracking',
        ];

        if (! in_array($this->tab, $allowedTabs, true)) {
            $this->tab = 'overview';
        }
    }

    public function render()
    {
        return view('vendor.dashboard.dashboard');
    }
}
