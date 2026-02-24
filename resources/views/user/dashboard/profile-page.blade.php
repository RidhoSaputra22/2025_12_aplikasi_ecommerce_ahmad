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
                    <div class="w-16 h-16 rounded-full border bg-gray-50 flex items-center justify-center text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" /></svg>
                    </div>
                    @endif

                    <div>
                        <div class="font-semibold text-lg">{{ $name }}</div>
                        <div class="text-sm text-gray-500">{{ $email }}</div>
                        @php
                            $roleName = Auth::user()?->role?->name;
                            $roleLabel = match($roleName) {
                                'admin' => 'Administrator',
                                'vendor' => 'Vendor / Penjual',
                                'customer' => 'Customer / Pembeli',
                                default => ucfirst($roleName ?? '-'),
                            };
                            $roleColor = match($roleName) {
                                'admin' => 'bg-red-100 text-red-700',
                                'vendor' => 'bg-blue-100 text-blue-700',
                                'customer' => 'bg-green-100 text-green-700',
                                default => 'bg-gray-100 text-gray-700',
                            };
                        @endphp
                        <span class="inline-block mt-1 px-2 py-0.5 text-xs font-medium rounded {{ $roleColor }}">
                            {{ $roleLabel }}
                        </span>
                        @if (Auth::user()?->last_login_at)
                        <div class="text-xs text-gray-400 mt-1">
                            Login terakhir: {{ Auth::user()->last_login_at->diffForHumans() }}
                        </div>
                        @endif
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
        <div class="p-4">
            <h3 class="text-lg font-semibold">Alamat Pengiriman</h3>
            <p class="text-sm text-gray-500">Isi satu alamat pengiriman yang akan digunakan untuk semua pesanan.</p>
        </div>

        @if (session()->has('success'))
        <div class="mx-4 mb-4 p-3 rounded-sm border border-green-200 bg-green-50 text-green-700 text-sm">
            {{ session('success') }}
        </div>
        @endif

        <form wire:submit.prevent="saveAddress" class="px-4 space-y-4">
            <div class="grid grid-cols-2 gap-4">
                @component('components.form.input', [
                'label' => 'Provinsi',
                'wireModel' => 'addrProvince',
                'placeholder' => 'Contoh: Jawa Barat',
                'required' => true,
                ])
                @endcomponent

                @component('components.form.input', [
                'label' => 'Kota / Kabupaten',
                'wireModel' => 'addrCity',
                'placeholder' => 'Contoh: Bandung',
                'required' => true,
                ])
                @endcomponent

                @component('components.form.input', [
                'label' => 'Kecamatan',
                'wireModel' => 'addrDistrict',
                'placeholder' => 'Contoh: Cicendo',
                'required' => true,
                ])
                @endcomponent

                @component('components.form.input', [
                'label' => 'Kode Pos',
                'wireModel' => 'addrPostalCode',
                'placeholder' => 'Contoh: 40171',
                'required' => true,
                ])
                @endcomponent
            </div>

            @component('components.form.textarea', [
            'label' => 'Alamat Lengkap',
            'wireModel' => 'addrAddress',
            'placeholder' => 'Nama jalan, nomor rumah, RT/RW, dll.',
            'required' => true,
            ])
            @endcomponent

            <div>
                <button type="submit" wire:loading.attr="disabled" wire:target="saveAddress"
                    class="bg-primary text-white px-6 py-2 rounded-sm text-sm font-semibold hover:opacity-90">
                    Simpan Alamat
                </button>
            </div>
        </form>
    </div>

    @endif

</div>
