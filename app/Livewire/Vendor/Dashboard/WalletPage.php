<?php

namespace App\Livewire\Vendor\Dashboard;

use App\Models\VendorBankAccount;
use App\Models\VendorWallet;
use App\Models\VendorWalletTransaction;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class WalletPage extends Component
{
    use WithPagination;

    public function render()
    {
        $vendor = Auth::user()?->vendor;

        if (!$vendor) {
            return view('vendor.dashboard.wallet-page', [
                'wallet' => null,
                'transactions' => collect(),
            ]);
        }

        $wallet = VendorWallet::where('vendor_id', $vendor->id)->first();

        $transactions = VendorWalletTransaction::query()
            ->when($wallet, function ($query) use ($wallet) {
                $query->where('vendor_wallet_id', $wallet->id);
            }, function ($query) {
                $query->whereRaw('1 = 0'); // no results
            })
            ->latest('created_at')
            ->paginate(10);

        return view('vendor.dashboard.wallet-page', [
            'wallet' => $wallet,
            'transactions' => $transactions,
        ]);
    }
}
