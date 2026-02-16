<?php

namespace App\Livewire\Vendor\Dashboard;

use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorAddress;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;

class ProfilePage extends Component
{
    // User fields
    public string $name = '';
    public string $email = '';
    public ?string $phone = null;
    public ?string $foto = null;

    // Vendor fields
    public string $store_name = '';
    public ?string $store_description = null;
    public ?string $logo = null;
    public ?string $banner = null;

    public int $tab = 1;

    // Address fields
    public ?string $address = null;
    public ?string $province = null;
    public ?string $city = null;
    public ?string $district = null;
    public ?string $postal_code = null;

    public function getVendorAddressesProperty(): Collection
    {
        $vendor = Auth::user()?->vendor;
        if (!$vendor) {
            return collect();
        }

        return VendorAddress::where('vendor_id', $vendor->id)->latest('id')->get();
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

        $vendor = $user->vendor;
        if ($vendor) {
            $this->store_name = (string) $vendor->store_name;
            $this->store_description = $vendor->description;
            $this->logo = $vendor->logo;
            $this->banner = $vendor->banner;
        }
    }

    public function rules(): array
    {
        $userId = Auth::id();

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'phone' => ['nullable', 'string', 'max:30'],
            'store_name' => ['required', 'string', 'max:255'],
            'store_description' => ['nullable', 'string'],
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
        ]);
        $user->save();

        $vendor = $user->vendor;
        if ($vendor) {
            $vendor->update([
                'store_name' => $validated['store_name'],
                'description' => $validated['store_description'],
            ]);
        }

        session()->flash('success', 'Profil berhasil diperbarui.');
    }

    public function changeTab(int $tab): void
    {
        $this->tab = $tab;
    }

    public function openPhotoUploadModal(): void
    {
        $this->dispatch(
            'openModal',
            component: 'components.file-upload-modal',
            arguments: [
                'disk' => 'public',
                'directory' => 'vendors/logos',
                'accept' => 'image/*',
                'maxSizeKb' => 2048,
                'imageOnly' => true,
                'returnEvent' => 'vendor-logo:uploaded',
            ],
            title: 'Upload Logo Toko',
            maxWidth: '3xl',
        );
    }

    #[On('vendor-logo:uploaded')]
    public function onLogoUploaded(string $path): void
    {
        $vendor = Auth::user()?->vendor;
        if (!$vendor) {
            return;
        }

        $vendor->update(['logo' => $path]);
        $this->logo = $path;
        session()->flash('success', 'Logo toko berhasil diperbarui.');
    }

    // Address management
    public function saveAddress(): void
    {
        $this->validate([
            'address' => ['required', 'string', 'max:500'],
            'province' => ['required', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'district' => ['required', 'string', 'max:100'],
            'postal_code' => ['required', 'string', 'max:10'],
        ]);

        $vendor = Auth::user()?->vendor;
        if (!$vendor) {
            return;
        }

        VendorAddress::create([
            'vendor_id' => $vendor->id,
            'address' => $this->address,
            'province' => $this->province,
            'city' => $this->city,
            'district' => $this->district,
            'postal_code' => $this->postal_code,
        ]);

        $this->reset(['address', 'province', 'city', 'district', 'postal_code']);
        session()->flash('success', 'Alamat toko berhasil ditambahkan.');
    }

    public function deleteAddress(int $addressId): void
    {
        $vendor = Auth::user()?->vendor;
        if (!$vendor) {
            return;
        }

        VendorAddress::where('vendor_id', $vendor->id)->where('id', $addressId)->delete();
        session()->flash('success', 'Alamat berhasil dihapus.');
    }

    public function render()
    {
        return view('vendor.dashboard.profile-page', [
            'vendorAddresses' => $this->vendorAddresses,
        ]);
    }
}
