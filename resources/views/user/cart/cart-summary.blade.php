<div class="sticky top-20 ">
    <div class="  ">
        <h1 class="text-xl font-medium">Ringkasan Belanja</h1>
        @if (!auth()->check())
        <p class="mt-2 text-sm text-gray-600">Silakan login untuk melanjutkan.</p>
        <a href="{{ route('user.login') }}" class="inline-block mt-3 text-primary">Login</a>
        @elseif ($this->cartItems->isEmpty())
        <p class="mt-2 text-sm text-gray-600">Keranjang masih kosong.</p>
        @else
        @if (session()->has('success'))
        <div class="mt-3 p-3 border border-green-200 bg-green-50 rounded-lg text-sm text-green-700">
            {{ session('success') }}
        </div>
        @endif

        @error('selectedCouriers')
        <div class="mt-3 p-3 border border-red-200 bg-red-50 rounded-lg text-sm text-red-700">
            {{ $message }}
        </div>
        @enderror
        @error('cart')
        <div class="mt-3 p-3 border border-red-200 bg-red-50 rounded-lg text-sm text-red-700">
            {{ $message }}
        </div>
        @enderror

        <div class="mt-4 space-y-2 text-sm">
            <div class="flex justify-between">
                <span class="text-gray-600">Total item</span>
                <span class="font-medium">{{ $this->itemsCount }}</span>
            </div>

            <div class="flex justify-between">
                <span class="text-gray-600">Subtotal</span>
                <span class="font-medium">Rp {{ number_format((float) $this->subtotal, 0, ',', '.') }}</span>
            </div>

            <div class="flex justify-between">
                <span class="text-gray-600">Ongkir</span>
                @if ($this->shippingCost > 0)
                <span class="font-medium">Rp {{ number_format((float) $this->shippingCost, 0, ',', '.') }}</span>
                @else
                <span class="text-gray-400 text-xs">Pilih kurir</span>
                @endif
            </div>

            <div class="pt-3 mt-3 border-t border-gray-200 flex justify-between text-base font-semibold">
                <span>Total</span>
                <span>Rp {{ number_format((float) $this->grandTotal, 0, ',', '.') }}</span>
            </div>
        </div>

        <form wire:submit.prevent="checkout" class="mt-6 space-y-4">
            {{-- Pilih Kurir per Vendor --}}
            <div class="pt-4 border-t border-gray-200">
                <h3 class="text-sm font-semibold mb-3">Pilih Kurir Pengiriman</h3>

                @php
                $couriers = $this->couriers;
                @endphp

                @foreach ($this->vendorGroups as $vendorId => $items)
                @php
                $vendorName = $items->first()?->productVariant?->product?->vendor?->store_name ?? 'Vendor';
                $itemNames = $items->map(fn($i) => $i->productVariant?->product?->name ?? 'Produk')->implode(', ');
                @endphp
                <div class="mb-3 p-3 bg-gray-50 rounded-lg border">
                    <div class="flex items-center gap-2 mb-2">
                        <svg class="w-4 h-4 text-gray-500 shrink-0" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z" />
                        </svg>
                        <span class="text-xs font-semibold text-gray-700 truncate">{{ $vendorName }}</span>
                    </div>
                    <p class="text-xs text-gray-500 mb-2 truncate" title="{{ $itemNames }}">
                        {{ \Illuminate\Support\Str::limit($itemNames, 60) }}</p>
                    <select wire:model.live="selectedCouriers.{{ $vendorId }}"
                        class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                        <option value="">-- Pilih Kurir --</option>
                        @foreach ($couriers as $courier)
                        <option value="{{ $courier->id }}">
                            {{ $courier->name }} - {{ $courier->service }} (Rp
                            {{ number_format((float) $courier->price, 0, ',', '.') }})
                        </option>
                        @endforeach
                    </select>
                </div>
                @endforeach
            </div>

            <div class="pt-4 border-t border-gray-200">
                <h3 class="text-sm font-semibold">Detail Pembeli</h3>
                <div class="mt-3 space-y-3 ">
                    @component('components.form.input', [
                    'label' => 'Nama',
                    'wireModel' => 'name',
                    'placeholder' => 'Masukkan nama',
                    'required' => true,
                    'disabled' => true,
                    ])
                    @endcomponent

                    @component('components.form.input', [
                    'type' => 'email',
                    'label' => 'Email',
                    'wireModel' => 'email',
                    'placeholder' => 'Masukkan email',
                    'required' => true,
                    'disabled' => true,
                    ])
                    @endcomponent

                    @component('components.form.input', [
                    'label' => 'No. Telepon',
                    'wireModel' => 'phone',
                    'placeholder' => 'Masukkan nomor telepon',
                    'required' => true,
                    'disabled' => true,
                    ])
                    @endcomponent
                </div>
            </div>

            <button type="submit" wire:loading.attr="disabled" wire:target="checkout" @if (!$this->allCouriersSelected)
                disabled @endif
                class="w-full mt-2 bg-primary text-white py-3 px-4 rounded-lg text-sm font-semibold hover:opacity-90
                cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed transition">
                <span wire:loading.remove wire:target="checkout">Checkout</span>
                <span wire:loading wire:target="checkout">Memproses...</span>
            </button>
            @if (!$this->allCouriersSelected && !$this->cartItems->isEmpty())
            <p class="text-xs text-center text-red-500 mt-1">Pilih kurir untuk semua vendor sebelum checkout.</p>
            @endif
        </form>
        @endif
    </div>
</div>
