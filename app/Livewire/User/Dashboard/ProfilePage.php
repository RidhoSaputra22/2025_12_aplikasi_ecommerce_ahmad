<?php

namespace App\Livewire\User\Dashboard;

use App\Models\ShipmentAddress;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;

class ProfilePage extends Component
{
    public string $name = '';
    public string $email = '';
    public ?string $phone = null;
    public ?string $foto = null;
    public ?string $description = "Has";

    public int $tab = 1;


    public function getShipmentAddressesProperty(): Collection
    {
        if (!Auth::check()) {
            return collect();
        }

        return ShipmentAddress::query()
            ->where('user_id', Auth::id())
            ->latest('id')
            ->get();
    }

    public function mount(): void
    {
        $user = Auth::user();

        if (!$user instanceof User) {
            $this->redirectRoute('user.login');
            return;
        }

        $this->name = (string) $user->name;
        $this->email = (string) $user->email;
        $this->phone = $user->phone;
        $this->foto = $user->foto;
        $this->description = $user->description;
    }

    public function rules(): array
    {
        $userId = Auth::id();

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'phone' => ['nullable', 'string', 'max:30'],
            'description' => ['nullable', 'string'],
        ];
    }

    public function save(): void
    {
        $validated = $this->validate();

        $user = Auth::user();
        if (!$user instanceof User) {
            $this->redirectRoute('user.login');
            return;
        }

        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'description' => $validated['description'],
        ]);

        $user->save();

        session()->flash('success', 'Profil berhasil diperbarui.');

        $this->dispatch('profile-updated');
    }

    public function changeTab(int $tab): void
    {
        $this->tab = $tab;
    }

    public function openShipmentAddressCreateModal(): void
    {
        $this->dispatch(
            'openModal',
            component: 'user.cart.shipping-address-create',
            arguments: [],
            title: 'Tambah Alamat Pengiriman',
            maxWidth: '3xl',
        );
    }

    public function openPhotoUploadModal(): void
    {
        $this->dispatch(
            'openModal',
            component: 'components.file-upload-modal',
            arguments: [
                'disk' => 'public',
                'directory' => 'users/photos',
                'accept' => 'image/*',
                'maxSizeKb' => 2048,
                'imageOnly' => true,
                'returnEvent' => 'profile-photo:uploaded',
            ],
            title: 'Upload Foto Profil',
            maxWidth: '3xl',
        );
    }

    #[On('profile-photo:uploaded')]
    public function onProfilePhotoUploaded(string $path): void
    {
        $user = Auth::user();
        if (!$user instanceof User) {
            $this->redirectRoute('user.login');
            return;
        }

        $user->update([
            'foto' => $path,
        ]);

        $this->foto = $path;
        session()->flash('success', 'Foto profil berhasil diperbarui.');
    }

    public function openShipmentAddressEditModal(int $shipmentAddressId): void
    {
        $this->dispatch(
            'openModal',
            component: 'user.cart.shipping-address-create',
            arguments: ['shipmentAddressId' => $shipmentAddressId],
            title: 'Edit Alamat Pengiriman',
            maxWidth: '3xl',
        );
    }

    #[On('shipping-address:created')]
    public function onShipmentAddressCreated(int $shipmentAddressId): void
    {
        // Trigger re-render so the list refreshes.
    }

    #[On('shipping-address:updated')]
    public function onShipmentAddressUpdated(int $shipmentAddressId): void
    {
        // Trigger re-render so the list refreshes.
    }

    #[On('shipping-address:deleted')]
    public function onShipmentAddressDeleted(int $shipmentAddressId): void
    {
        // Trigger re-render so the list refreshes.
    }

    public function render()
    {
        return view('user.dashboard.profile-page', [
            'addresses' => $this->shipmentAddresses,
        ]);
    }
}
