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



        <div class="mt-4 space-y-2 text-sm">
            <div class="flex justify-between">
                <span class="text-gray-600">Total item</span>
                <span class="font-medium">{{ $this->itemsCount }}</span>
            </div>

            <div class="flex justify-between">
                <span class="text-gray-600">Subtotal</span>
                <span class="font-medium">Rp {{ number_format((float) $this->subtotal, 0, ',', ',') }}</span>
            </div>

            <div class="pt-3 mt-3 border-t border-gray-200 flex justify-between text-base font-semibold">
                <span>Total</span>
                <span>Rp {{ number_format((float) $this->subtotal, 0, ',', ',') }}</span>
            </div>
        </div>

        <div class="mt-4 text-xs text-gray-500">
            Total belum termasuk ongkir.
        </div>

        <form wire:submit.prevent="checkout" class="mt-6 space-y-4">
            <div class="pt-4 border-t border-gray-200">
                <div class="flex items-center justify-between gap-3">
                    <h3 class="text-sm font-semibold">Alamat Pengiriman</h3>

                    <button
                        type="button"
                        wire:click="openShippingAddressModal"
                        class="cursor-pointer text-sm text-primary"
                    >
                        Pilih Alamat
                    </button>
                </div>

                <div class="mt-3 text-sm">
                    @if ($this->selectedShipmentAddress)
                        <div class="text-gray-800 font-medium">
                            {{ $this->selectedShipmentAddress->district }}, {{ $this->selectedShipmentAddress->city }}
                        </div>
                        <div class="mt-1 text-gray-700">
                            {{ $this->selectedShipmentAddress->address }}
                        </div>
                        <div class="mt-1 text-xs text-gray-500">
                            {{ $this->selectedShipmentAddress->province }} • {{ $this->selectedShipmentAddress->postal_code }}
                        </div>
                    @else
                        <p class="text-gray-600">Belum memilih alamat pengiriman.</p>
                    @endif
                    @error('shipmentAddressId')
                        <div class="mt-1 text-xs text-red-600">{{ $message }}</div>

                    @enderror
                </div>
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



            <button type="submit" wire:loading.attr="disabled" wire:target="checkout"
                class="w-full mt-2 bg-primary text-white py-3 px-4 rounded-lg text-sm font-semibold hover:opacity-90 cursor-pointer">
                <span wire:loading.remove wire:target="checkout">Checkout</span>
                <span wire:loading wire:target="checkout">Memproses...</span>
            </button>
        </form>
        @endif
    </div>
</div>
