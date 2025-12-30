<div class="space-y-3 w-full  rounded-lg bg-white">
    @if (!auth()->check())
        <p class="text-sm text-gray-600">Silakan login untuk memilih alamat.</p>
    @elseif ($this->addresses->isEmpty())
        <p class="text-sm text-gray-600">Belum ada alamat pengiriman.</p>

        <button
            type="button"
            wire:click="openCreateAddressModal"
            class="cursor-pointer mt-2 inline-flex items-center justify-center rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white hover:opacity-90"
        >
            Tambah Alamat
        </button>
    @else
        <div class="space-y-3">

        <button
            type="button"
            wire:click="openCreateAddressModal"
            class="cursor-pointer  inline-flex items-center justify-center rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white hover:opacity-90"
        >
            Tambah Alamat Baru
        </button>
            @foreach ($this->addresses as $addr)
                <button
                    type="button"
                    wire:click="select({{ (int) $addr->id }})"
                    class="cursor-pointer w-full text-left rounded-lg border p-3 hover:bg-gray-50"
                >
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="text-sm font-semibold">
                                {{ $addr->district }}, {{ $addr->city }}
                            </div>
                            <div class="mt-1 text-sm text-gray-700">
                                {{ $addr->address }}
                            </div>
                            <div class="mt-1 text-xs text-gray-500">
                                {{ $addr->province }} • {{ $addr->postal_code }}
                            </div>
                        </div>

                        @if ($selectedId === (int) $addr->id)
                            <span class="text-xs font-semibold">Dipilih</span>
                        @endif
                    </div>
                </button>
            @endforeach
        </div>
    @endif
</div>
