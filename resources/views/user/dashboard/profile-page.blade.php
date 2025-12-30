<div class="p-6 rounded-xl border bg-white">
    <div class="mb-6">
        <h2 class="text-xl font-semibold">Profil Saya</h2>
        <p class="text-sm text-gray-500">Perbarui data akun Anda.</p>
    </div>

    <div class="mb-8 p-4 rounded-xl border bg-white">
        <div class="flex items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                @if (!empty($foto))
                    <img src="{{ asset('storage/' . $foto) }}" alt="Foto Profil" class="w-16 h-16 rounded-full object-cover border" />
                @else
                    <div class="w-16 h-16 rounded-full border bg-gray-50"></div>
                @endif

                <div>
                    <div class="font-semibold">Foto Profil</div>
                    <div class="text-sm text-gray-500">Upload foto untuk akun Anda.</div>
                </div>
            </div>

            <button
                type="button"
                wire:click="openPhotoUploadModal"
                class="px-4 py-2 rounded-lg text-sm font-semibold border hover:bg-gray-50"
            >
                Upload Foto
            </button>
        </div>
    </div>

    @if (session()->has('success'))
        <div class="p-3 rounded-lg border border-green-200 bg-green-50 text-green-700 mb-6">
            {{ session('success') }}
        </div>
    @endif

    <form wire:submit.prevent="save" class="space-y-5 max-w-2xl">
        @component('components.form.input', [
            'label' => 'Nama',
            'type' => 'text',
            'wireModel' => 'name',
            'placeholder' => 'Masukkan nama',
            'required' => true,
        ])
        @endcomponent

        @component('components.form.input', [
            'label' => 'Email',
            'type' => 'email',
            'wireModel' => 'email',
            'placeholder' => 'Masukkan email',
            'required' => true,
        ])
        @endcomponent

        @component('components.form.input', [
            'label' => 'No. HP',
            'type' => 'text',
            'wireModel' => 'phone',
            'placeholder' => 'Masukkan nomor HP',
            'required' => false,
        ])
        @endcomponent

        @component('components.form.button', [
            'label' => 'Simpan Perubahan',
            'class' => 'bg-primary text-white px-6 py-3 rounded-lg',
            'wireLoadingTarget' => 'save',
            'wireLoadingClass' => 'opacity-70 cursor-not-allowed',
        ])
        @endcomponent
    </form>

    <div class="mt-10 pt-8 border-t">
        <div class="flex items-start justify-between gap-4 mb-4">
            <div>
                <h3 class="text-lg font-semibold">Alamat Pengiriman</h3>
                <p class="text-sm text-gray-500">Kelola alamat yang bisa dipakai untuk pengiriman.</p>
            </div>

            <button
                type="button"
                wire:click="openShipmentAddressCreateModal"
                class="bg-primary text-white px-4 py-2 rounded-lg text-sm font-semibold hover:opacity-90"
            >
                Tambah Alamat
            </button>
        </div>

        <div class="space-y-3">
            @forelse (($addresses ?? []) as $address)
                <div class="p-4 rounded-lg border bg-white">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <div class="font-medium">{{ $address->address }}</div>
                            <div class="text-sm text-gray-500">
                                {{ $address->district }}, {{ $address->city }}, {{ $address->province }} {{ $address->postal_code }}
                            </div>
                        </div>

                        <button
                            type="button"
                            wire:click="openShipmentAddressEditModal({{ (int) $address->id }})"
                            class="px-3 py-2 rounded-lg text-sm font-semibold border hover:bg-gray-50"
                        >
                            Edit
                        </button>
                    </div>
                </div>
            @empty
                <div class="p-4 rounded-lg border bg-gray-50 text-gray-600 text-sm">
                    Belum ada alamat pengiriman.
                </div>
            @endforelse
        </div>
    </div>
</div>
