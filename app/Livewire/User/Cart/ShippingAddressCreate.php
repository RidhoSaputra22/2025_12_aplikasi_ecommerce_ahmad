<?php

namespace App\Livewire\User\Cart;

use App\Models\ShipmentAddress;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ShippingAddressCreate extends Component
{
    public ?int $shipmentAddressId = null;

    public string $province = '';
    public string $city = '';
    public string $district = '';
    public string $postal_code = '';
    public string $address = '';

    public function mount(?int $shipmentAddressId = null): void
    {
        $this->shipmentAddressId = $shipmentAddressId;

        if (!$shipmentAddressId) {
            return;
        }

        if (!Auth::check()) {
            return;
        }

        $existing = ShipmentAddress::query()
            ->where('id', $shipmentAddressId)
            ->where('user_id', Auth::id())
            ->first();

        if (!$existing) {
            $this->shipmentAddressId = null;
            return;
        }

        $this->province = (string) $existing->province;
        $this->city = (string) $existing->city;
        $this->district = (string) $existing->district;
        $this->postal_code = (string) $existing->postal_code;
        $this->address = (string) $existing->address;
    }

    public function save(): void
    {
        if (!Auth::check()) {
            return;
        }

        $validated = $this->validate([
            'province' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'district' => ['required', 'string', 'max:255'],
            'postal_code' => ['required', 'string', 'max:30'],
            'address' => ['required', 'string'],
        ], [
            'province.required' => 'Provinsi wajib diisi.',
            'city.required' => 'Kota wajib diisi.',
            'district.required' => 'Kecamatan wajib diisi.',
            'postal_code.required' => 'Kode pos wajib diisi.',
            'address.required' => 'Alamat wajib diisi.',
        ]);

        if ($this->shipmentAddressId) {
            $shipmentAddress = ShipmentAddress::query()
                ->where('id', $this->shipmentAddressId)
                ->where('user_id', Auth::id())
                ->first();

            if (!$shipmentAddress) {
                return;
            }

            $shipmentAddress->update([
                'province' => $validated['province'],
                'city' => $validated['city'],
                'district' => $validated['district'],
                'postal_code' => $validated['postal_code'],
                'address' => $validated['address'],
            ]);
        } else {
            $shipmentAddress = ShipmentAddress::query()->create([
                'user_id' => Auth::id(),
                'province' => $validated['province'],
                'city' => $validated['city'],
                'district' => $validated['district'],
                'postal_code' => $validated['postal_code'],
                'address' => $validated['address'],
            ]);
        }

        $id = (int) $shipmentAddress->id;

        // Update the underlying page selection (CartSummary listens to this).
        $this->dispatch('shipping-address:selected', shipmentAddressId: $id);

        // Notify pages/pickers to refresh.
        if ($this->shipmentAddressId) {
            $this->dispatch('shipping-address:updated', shipmentAddressId: $id);
        } else {
            $this->dispatch('shipping-address:created', shipmentAddressId: $id);
        }

        // Close ALL modals (child + parent) and return to the underlying page.
        $this->dispatch('forceCloseModal');
    }

    public function delete(): void
    {
        if (!Auth::check() || !$this->shipmentAddressId) {
            return;
        }

        $shipmentAddress = ShipmentAddress::query()
            ->where('id', $this->shipmentAddressId)
            ->where('user_id', Auth::id())
            ->first();

        if (!$shipmentAddress) {
            return;
        }

        $id = (int) $shipmentAddress->id;
        $shipmentAddress->delete();

        $this->dispatch('shipping-address:deleted', shipmentAddressId: $id);
        $this->dispatch('forceCloseModal');
    }

    public function render()
    {
        return view('livewire.user.cart.shipping-address-create');
    }
}
