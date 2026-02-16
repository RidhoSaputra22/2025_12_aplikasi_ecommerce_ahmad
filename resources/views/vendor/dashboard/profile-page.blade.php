@php
    $tabActive = "bg-primary text-white";
@endphp
<div class="p-6">

    <div class="flex gap-6">
        <div class="mb-6 rounded-sm hover:bg-primary px-3 py-2 cursor-pointer hover:text-white {{ $tab === 1 ? $tabActive : '' }}" wire:click="changeTab(1)">
            <h2 class="text-lg font-semibold">Profil Toko</h2>
        </div>
        <div class="mb-6 rounded-sm hover:bg-primary px-3 py-2 cursor-pointer hover:text-white {{ $tab === 2 ? $tabActive : '' }}" wire:click="changeTab(2)">
            <h2 class="text-lg font-semibold">Alamat Toko</h2>
        </div>
    </div>

    @if ($tab === 1)
    <div wire:loading.class="opacity-50 pointer-events-none">

        {{-- Store info --}}
        <div class="mb-8 p-4">
            <div class="flex items-center justify-between gap-6">
                <div class="flex items-center gap-4">
                    @if (!empty($logo))
                    <img src="{{ asset('storage/' . $logo) }}" alt="Logo Toko"
                        class="w-16 h-16 rounded-full object-cover border" />
                    @else
                    <div class="w-16 h-16 rounded-full border bg-gray-50 flex items-center justify-center text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 0 0 3.75-.615A2.993 2.993 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 0 0 2.25 1.016c.896 0 1.7-.393 2.25-1.015a3.001 3.001 0 0 0 3.75.614m-16.5 0a3.004 3.004 0 0 1-.621-4.72l1.189-1.19A1.5 1.5 0 0 1 5.378 3h13.243a1.5 1.5 0 0 1 1.06.44l1.19 1.189a3 3 0 0 1-.621 4.72M6.75 18h3.75a.75.75 0 0 0 .75-.75V13.5a.75.75 0 0 0-.75-.75H6.75a.75.75 0 0 0-.75.75v3.75c0 .414.336.75.75.75Z" /></svg>
                    </div>
                    @endif

                    <div>
                        <div class="font-semibold text-lg">{{ $store_name }}</div>
                        <div class="text-sm text-gray-500">{{ $email }}</div>
                        @php
                            $vendorStatus = Auth::user()?->vendor?->status;
                            $statusLabel = $vendorStatus?->getLabel() ?? '-';
                            $statusColor = match($vendorStatus?->value) {
                                'active' => 'bg-green-100 text-green-700',
                                'inactive' => 'bg-yellow-100 text-yellow-700',
                                'banned' => 'bg-red-100 text-red-700',
                                default => 'bg-gray-100 text-gray-700',
                            };
                        @endphp
                        <span class="inline-block mt-1 px-2 py-0.5 text-xs font-medium rounded {{ $statusColor }}">
                            Vendor: {{ $statusLabel }}
                        </span>
                    </div>
                </div>

                <button type="button" wire:click="openPhotoUploadModal"
                    class="px-4 py-2 rounded-sm text-sm font-semibold border hover:bg-gray-50">
                    Upload Logo
                </button>
            </div>
        </div>

        @if (session()->has('success'))
        <div class="p-3 rounded-sm border border-green-200 bg-green-50 text-green-700 mb-6">
            {{ session('success') }}
        </div>
        @endif

        <form wire:submit.prevent="save" class="space-y-5">
            <div class="grid grid-cols-3 gap-10">
                @component('components.form.input', [
                    'label' => 'Nama Pemilik',
                    'type' => 'text',
                    'wireModel' => 'name',
                    'placeholder' => 'Masukkan nama',
                    'required' => true,
                ]) @endcomponent

                @component('components.form.input', [
                    'label' => 'Email',
                    'type' => 'email',
                    'wireModel' => 'email',
                    'placeholder' => 'Masukkan email',
                    'required' => true,
                ]) @endcomponent

                @component('components.form.input', [
                    'label' => 'No. HP',
                    'type' => 'text',
                    'wireModel' => 'phone',
                    'placeholder' => 'Masukkan nomor HP',
                    'required' => false,
                ]) @endcomponent

                @component('components.form.input', [
                    'label' => 'Nama Toko',
                    'type' => 'text',
                    'wireModel' => 'store_name',
                    'placeholder' => 'Masukkan nama toko',
                    'required' => true,
                ]) @endcomponent

                @component('components.form.textarea', [
                    'label' => 'Deskripsi Toko',
                    'wireModel' => 'store_description',
                    'placeholder' => 'Ceritakan tentang toko Anda',
                    'class' => 'col-span-2',
                    'required' => false,
                ]) @endcomponent
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

        @if (session()->has('success'))
        <div class="p-3 rounded-sm border border-green-200 bg-green-50 text-green-700 mb-4">
            {{ session('success') }}
        </div>
        @endif

        <div class="flex items-start justify-between gap-4 p-4">
            <div>
                <h3 class="text-lg font-semibold">Alamat Toko</h3>
                <p class="text-sm text-gray-500">Kelola alamat lokasi toko Anda.</p>
            </div>
        </div>

        {{-- Add address form --}}
        <div class="p-4 border rounded-sm mb-4">
            <h4 class="text-sm font-semibold mb-3">Tambah Alamat Baru</h4>
            <form wire:submit.prevent="saveAddress" class="space-y-3">
                <div class="grid grid-cols-2 gap-4">
                    @component('components.form.input', [
                        'label' => 'Provinsi',
                        'type' => 'text',
                        'wireModel' => 'province',
                        'placeholder' => 'Provinsi',
                        'required' => true,
                    ]) @endcomponent

                    @component('components.form.input', [
                        'label' => 'Kota',
                        'type' => 'text',
                        'wireModel' => 'city',
                        'placeholder' => 'Kota',
                        'required' => true,
                    ]) @endcomponent

                    @component('components.form.input', [
                        'label' => 'Kecamatan',
                        'type' => 'text',
                        'wireModel' => 'district',
                        'placeholder' => 'Kecamatan',
                        'required' => true,
                    ]) @endcomponent

                    @component('components.form.input', [
                        'label' => 'Kode Pos',
                        'type' => 'text',
                        'wireModel' => 'postal_code',
                        'placeholder' => 'Kode Pos',
                        'required' => true,
                    ]) @endcomponent
                </div>

                @component('components.form.textarea', [
                    'label' => 'Alamat Lengkap',
                    'wireModel' => 'address',
                    'placeholder' => 'Masukkan alamat lengkap',
                    'required' => true,
                ]) @endcomponent

                <button type="submit" wire:loading.attr="disabled"
                    class="bg-primary text-white px-4 py-2 rounded-sm text-sm font-semibold hover:opacity-90">
                    Tambah Alamat
                </button>
            </form>
        </div>

        {{-- List addresses --}}
        <div class="space-y-3">
            @forelse (($vendorAddresses ?? []) as $addr)
            <div class="p-4 rounded-sm border bg-white">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="font-medium">{{ $addr->address }}</div>
                        <div class="text-sm text-gray-500">
                            {{ $addr->district }}, {{ $addr->city }}, {{ $addr->province }}
                            {{ $addr->postal_code }}
                        </div>
                    </div>
                    <button type="button" wire:click="deleteAddress({{ $addr->id }})"
                        wire:confirm="Yakin ingin menghapus alamat ini?"
                        class="text-red-500 text-sm hover:underline">
                        Hapus
                    </button>
                </div>
            </div>
            @empty
            <div class="p-4 rounded-sm border bg-gray-50 text-gray-600 text-sm">
                Belum ada alamat toko.
            </div>
            @endforelse
        </div>
    </div>
    @endif
</div>
