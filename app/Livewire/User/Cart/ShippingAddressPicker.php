<?php

namespace App\Livewire\User\Cart;

use App\Models\ShipmentAddress;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class ShippingAddressPicker extends Component
{
    public ?int $selectedId = null;

    public function mount(?int $selectedId = null): void
    {
        $this->selectedId = $selectedId;
    }

    public function getAddressesProperty(): Collection
    {
        if (!Auth::check()) {
            return collect();
        }

        return ShipmentAddress::query()
            ->where('user_id', Auth::id())
            ->latest('id')
            ->get();
    }

    public function select(int $shipmentAddressId): void
    {
        if (!Auth::check()) {
            return;
        }

        $exists = ShipmentAddress::query()
            ->where('id', $shipmentAddressId)
            ->where('user_id', Auth::id())
            ->exists();

        if (!$exists) {
            return;
        }

        $this->selectedId = $shipmentAddressId;

        $this->dispatch('shipping-address:selected', shipmentAddressId: $shipmentAddressId);
        $this->dispatch('closeModal');
    }

    public function openCreateAddressModal(): void
    {
        $this->dispatch(
            'openModal',
            component: 'user.cart.shipping-address-create',
            arguments: [],
            title: 'Tambah Alamat Pengiriman',
            maxWidth: '3xl',
        );
    }

    #[On('shipping-address:created')]
    public function onAddressCreated(int $shipmentAddressId): void
    {
        // Preselect newly created address in the picker.
        $this->selectedId = $shipmentAddressId;
        // The list will refresh on re-render.
    }

    public function render()
    {
        return view('livewire.user.cart.shipping-address-picker');
    }
}
