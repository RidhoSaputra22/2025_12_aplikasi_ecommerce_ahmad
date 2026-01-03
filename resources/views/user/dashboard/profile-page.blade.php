@php
    $tabActive = "bg-primary text-white  ";
@endphp
<div class="p-6 ">

    <div class="flex gap-6">

        <div class="mb-6 rounded-sm hover:bg-primary px-3 py-2 cursor-pointer hover:text-white {{ $tab === 1 ? $tabActive : '' }}" wire:click="changeTab(1)">
            <h2 class="text-lg font-semibold">Profil Saya</h2>
            <!-- <p class="text-xs font-light ">Perbarui data akun Anda.</p> -->
        </div>
        <div class="mb-6 rounded-sm hover:bg-primary px-3 py-2 cursor-pointer hover:text-white {{ $tab === 2 ? $tabActive : '' }}" wire:click="changeTab(2)">
            <h2 class="text-lg font-semibold">Alamat Saya</h2>
            <!-- <p class="text-xs font-light ">Lihat alamat pengiriman anda</p> -->
        </div>
    </div>


    @if ($tab === 1)
    <div wire:loading.class="opacity-50 pointer-events-none">


        <div class="mb-8 p-4 ">
            <div class="flex items-center justify-between gap-6">
                <div class="flex items-center gap-4">
                    @if (!empty($foto))
                    <img src="{{ asset('storage/' . $foto) }}" alt="Foto Profil"
                        class="w-16 h-16 rounded-full object-cover border" />
                    @else
                    <div class="w-16 h-16 rounded-full border bg-gray-50"></div>
                    @endif

                    <div>
                        <div class="font-semibold">Foto Profil</div>
                        <div class="text-sm text-gray-500">Upload foto untuk akun Anda.</div>
                    </div>
                </div>

                <button type="button" wire:click="openPhotoUploadModal"
                    class="px-4 py-2 rounded-sm text-sm font-semibold border hover:bg-gray-50">
                    Upload Foto
                </button>
            </div>
        </div>

        @if (session()->has('success'))
        <div class="p-3 rounded-sm border border-green-200 bg-green-50 text-green-700 mb-6 hover:cursor-pointer" wire:click="changeTab(1)">
            {{ session('success') }}
        </div>
        @endif

        <form wire:submit.prevent="save" class="space-y-5  ">
            <div class="grid grid-cols-3 gap-10">

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

                @component('components.form.textarea', [
                'label' => 'Deskripsi',
                'wireModel' => 'description',
                'placeholder' => 'Ceritakan tentang diri Anda',
                'class' => 'col-span-3',

                'required' => false,
                ])

                @endcomponent
            </div>
            <div>
                <button type="submit" wire:loading.attr="disabled" wire:target="save"
                    class="bg-primary text-white px-6 py-2 rounded-sm text-sm font-semibold hover:opacity-90">
                    Simpan Perubahan
                </button>
            </div>

        </form>

    </div>
    @else
    <div wire:loading.class="opacity-50 pointer-events-none">
        <div class="flex items-start justify-between gap-4 p-4">
            <div>
                <h3 class="text-lg font-semibold">Alamat Pengiriman</h3>
                <p class="text-sm text-gray-500">Kelola alamat yang bisa dipakai untuk pengiriman.</p>
            </div>

            <button type="button" wire:click="openShipmentAddressCreateModal"
                class="bg-primary text-white px-4 py-2 rounded-sm text-sm font-semibold hover:opacity-90">
                Tambah Alamat
            </button>
        </div>

        <div class="space-y-3">
            @forelse (($addresses ?? []) as $address)
            <div class="p-4 rounded-sm border bg-white">
                <div class="flex items-start justify-between gap-4 cursor-pointer" wire:click="openShipmentAddressEditModal({{ (int) $address->id }})">
                    <div>
                        <div class="font-medium">{{ $address->address }}</div>
                        <div class="text-sm text-gray-500">
                            {{ $address->district }}, {{ $address->city }}, {{ $address->province }}
                            {{ $address->postal_code }}
                        </div>
                    </div>


                </div>
            </div>
            @empty
            <div class="p-4 rounded-sm border bg-gray-50 text-gray-600 text-sm">
                Belum ada alamat pengiriman.
            </div>
            @endforelse
        </div>
    </div>

    @endif

</div>
