<?php

namespace App\Livewire\User\Dashboard;

use App\Models\ShipmentAddress;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;

class ProfilePage extends Component
{
    public string $name = '';

    public string $email = '';

    public ?string $phone = null;

    public ?string $foto = null;

    public ?string $description = 'Has';

    public int $tab = 1;

    // Address fields
    public string $addrProvince = '';

    public string $addrCity = '';

    public string $addrDistrict = '';

    public string $addrPostalCode = '';

    public string $addrAddress = '';

    public function getShipmentAddressProperty(): ?ShipmentAddress
    {
        if (! Auth::check()) {
            return null;
        }

        return ShipmentAddress::query()
            ->where('user_id', Auth::id())
            ->first();
    }

    public function mount(): void
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            $this->redirectRoute('user.login');

            return;
        }

        $this->name = (string) $user->name;
        $this->email = (string) $user->email;
        $this->phone = $user->phone;
        $this->foto = $user->foto;
        $this->description = $user->description;

        // Load single address
        $address = ShipmentAddress::query()->where('user_id', $user->id)->first();
        if ($address) {
            $this->addrProvince = (string) ($address->province ?? '');
            $this->addrCity = (string) ($address->city ?? '');
            $this->addrDistrict = (string) ($address->district ?? '');
            $this->addrPostalCode = (string) ($address->postal_code ?? '');
            $this->addrAddress = (string) ($address->address ?? '');
        }
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
        if (! $user instanceof User) {
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
        // No longer used — address is edited inline
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
        if (! $user instanceof User) {
            $this->redirectRoute('user.login');

            return;
        }

        abort_unless(dirname($path) === 'users/'.$user->id.'/photos', 403);

        $oldPhoto = $user->foto;
        $user->update([
            'foto' => $path,
        ]);

        if ($oldPhoto && $oldPhoto !== $path) {
            Storage::disk('public')->delete($oldPhoto);
        }

        $this->foto = $path;
        session()->flash('success', 'Foto profil berhasil diperbarui.');
    }

    public function openShipmentAddressEditModal(int $shipmentAddressId): void
    {
        // No longer used — address is edited inline
    }

    public function saveAddress(): void
    {
        $this->validate([
            'addrProvince' => ['required', 'string', 'max:100'],
            'addrCity' => ['required', 'string', 'max:100'],
            'addrDistrict' => ['required', 'string', 'max:100'],
            'addrPostalCode' => ['required', 'string', 'max:20'],
            'addrAddress' => ['required', 'string', 'max:500'],
        ], [
            'addrProvince.required' => 'Provinsi wajib diisi.',
            'addrCity.required' => 'Kota wajib diisi.',
            'addrDistrict.required' => 'Kecamatan wajib diisi.',
            'addrPostalCode.required' => 'Kode pos wajib diisi.',
            'addrAddress.required' => 'Alamat lengkap wajib diisi.',
        ]);

        $user = Auth::user();
        if (! $user instanceof User) {
            return;
        }

        ShipmentAddress::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'province' => $this->addrProvince,
                'city' => $this->addrCity,
                'district' => $this->addrDistrict,
                'postal_code' => $this->addrPostalCode,
                'address' => $this->addrAddress,
            ]
        );

        session()->flash('success', 'Alamat pengiriman berhasil disimpan.');
    }

    public function render()
    {
        return view('user.dashboard.profile-page');
    }
}
