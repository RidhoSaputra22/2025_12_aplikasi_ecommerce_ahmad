<?php

namespace App\Livewire\Vendor\Dashboard;

use App\Models\VendorBankAccount;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class BankAccountPage extends Component
{
    public ?string $bank_name = null;
    public ?string $account_number = null;
    public ?string $account_holder = null;

    public bool $showForm = false;
    public ?int $editingId = null;

    public function toggleForm(): void
    {
        $this->showForm = !$this->showForm;
        if (!$this->showForm) {
            $this->resetForm();
        }
    }

    public function resetForm(): void
    {
        $this->bank_name = null;
        $this->account_number = null;
        $this->account_holder = null;
        $this->editingId = null;
        $this->showForm = false;
    }

    public function edit(int $id): void
    {
        $vendor = Auth::user()?->vendor;
        if (!$vendor) {
            return;
        }

        $account = VendorBankAccount::where('id', $id)->where('vendor_id', $vendor->id)->first();
        if (!$account) {
            return;
        }

        $this->editingId = $id;
        $this->bank_name = $account->bank_name;
        $this->account_number = $account->account_number;
        $this->account_holder = $account->account_holder;
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validate([
            'bank_name' => ['required', 'string', 'max:100'],
            'account_number' => ['required', 'string', 'max:50'],
            'account_holder' => ['required', 'string', 'max:255'],
        ], [
            'bank_name.required' => 'Nama bank wajib diisi.',
            'account_number.required' => 'Nomor rekening wajib diisi.',
            'account_holder.required' => 'Nama pemilik rekening wajib diisi.',
        ]);

        $vendor = Auth::user()?->vendor;
        if (!$vendor) {
            return;
        }

        if ($this->editingId) {
            VendorBankAccount::where('id', $this->editingId)
                ->where('vendor_id', $vendor->id)
                ->update([
                    'bank_name' => $this->bank_name,
                    'account_number' => $this->account_number,
                    'account_holder' => $this->account_holder,
                ]);
            session()->flash('success', 'Rekening bank berhasil diperbarui.');
        } else {
            VendorBankAccount::create([
                'vendor_id' => $vendor->id,
                'bank_name' => $this->bank_name,
                'account_number' => $this->account_number,
                'account_holder' => $this->account_holder,
                'is_active' => true,
            ]);
            session()->flash('success', 'Rekening bank berhasil ditambahkan.');
        }

        $this->resetForm();
    }

    public function delete(int $id): void
    {
        $vendor = Auth::user()?->vendor;
        if (!$vendor) {
            return;
        }

        VendorBankAccount::where('id', $id)->where('vendor_id', $vendor->id)->delete();
        session()->flash('success', 'Rekening bank berhasil dihapus.');
    }

    public function render()
    {
        $vendor = Auth::user()?->vendor;
        $accounts = $vendor
            ? VendorBankAccount::where('vendor_id', $vendor->id)->latest()->get()
            : collect();

        return view('vendor.dashboard.bank-account-page', [
            'accounts' => $accounts,
        ]);
    }
}
