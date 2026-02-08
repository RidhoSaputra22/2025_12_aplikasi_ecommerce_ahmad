<div class="relative" wire:loading.class="opacity-60">

    <div class="h-14">
        <div wire:loading.delay wire:target="increment,decrement,setQuantity,removeItem,clearCart"
            class="flex items-center justify-center">
            <div class="mb-3 bg-white/70 border border-gray-200 rounded-lg px-4 py-3 flex items-center gap-3">
                <svg class="animate-spin h-5 w-5 text-primary" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                </svg>
                <span class="text-sm text-gray-700">Memproses...</span>
            </div>
        </div>
        @if ($errors->has('quantity'))
        <div class="mb-3 p-3 border border-red-200 bg-red-50 rounded-lg text-sm text-red-700" x-data="{ show: true }"
            x-show="show" x-init="setTimeout(() => show = false, 2000)" x-transition>
            {{ $errors->first('quantity') }}
        </div>
        @endif
    </div>

    @if (!auth()->check())
    <div class="p-4 border border-gray-200 rounded-lg">
        <p class="text-sm text-gray-600">Silakan login untuk melihat keranjang.</p>
        <a href="{{ route('user.login') }}" class="inline-block mt-2 text-primary">Login</a>
    </div>
    @elseif ($this->cartItems->isEmpty())
    <div class="p-4 border border-gray-200 rounded-lg">
        <p class="text-sm text-gray-600">Keranjang masih kosong.</p>
        <a href="{{ route('produk.cari') }}" class="inline-block mt-2 text-primary">Cari produk</a>
    </div>
    @else


    <div class="space-y-10 min-h-100" wire:loading.attr="aria-busy"
        wire:target="increment,decrement,setQuantity,removeItem,clearCart">
        @foreach ($this->cartItems as $item)
        @php
        $variant = $item->productVariant;
        $product = $variant?->product;
        $image = $product?->productImages?->first();
        @endphp

        <div class="flex  justify-between   rounded-lg">
            <div class="flex items-start gap-4">
                <div class="h-40 aspect-square bg-gray-100 rounded-lg overflow-hidden shrink-0">

                    <img src="{{ Storage::url($image?->image ?? 'products/product_placeholder.jpg') }}" alt="Produk"
                        class="w-full h-full object-cover">
                </div>

                <div class="flex-1 space-y-1 ">
                    <h1 class="text-sm/tight font-light">{{ $product?->category->name ?? 'Produk' }}</h1>
                    <h3 class="text-2xl/normal font-semibold uppercase ">{{ $product?->name ?? 'Produk' }}</h3>
                    <p class="uppercase text-xl font-medium">Rp {{ number_format((float) $item->price, 0, ',', ',') }}
                    </p>
                    @if ($variant?->variant_name)
                    <p class="px-3 py-2 bg-primary text-white text-sm inline-block rounded-md ">
                        {{ $variant->variant_name }}</p>
                    @endif
                </div>
            </div>

            <div class="flex items-start gap-3 ">
                <div class="flex items-center gap-2">
                    <button type="button" wire:click="decrement({{ $item->id }})" wire:loading.attr="disabled"
                        wire:target="decrement"
                        class="w-8 h-8 border cursor-pointer border-gray-300 rounded-lg flex items-center justify-center hover:bg-gray-50">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                        </svg>
                    </button>

                    <input type="text" inputmode="numeric" pattern="[0-9]*" value="{{ (int) $item->quantity }}"
                        class="w-16 h-8 text-center border border-gray-300 rounded text-sm focus:outline-none cursor-default"
                        readonly>

                    <button type="button" wire:click="increment({{ $item->id }})" wire:loading.attr="disabled"
                        wire:target="increment"
                        class="w-8 h-8 border cursor-pointer border-gray-300 rounded-lg flex items-center justify-center hover:bg-gray-50">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4">
                            </path>
                        </svg>
                    </button>
                </div>

                <button type="button" wire:click="removeItem({{ $item->id }})" wire:loading.attr="disabled"
                    wire:target="removeItem"
                    class="w-8 h-8 bg-red-100 cursor-pointer text-red-600 rounded-lg flex items-center justify-center hover:bg-red-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>
        </div>
        @endforeach
    </div>
    <div class="h-14">

    </div>

    <div class="mt-6 pt-6 border-t border-gray-200">
        <div class="flex justify-between items-center text-lg font-semibold">
            <span>Total</span>
            <span>Rp {{ number_format((float) $this->subtotal, 0, ',', '.') }}</span>
        </div>

        <div class="mt-3 flex justify-end">
            <button type="button" wire:click="clearCart" wire:loading.attr="disabled" wire:target="clearCart"
                class="text-sm text-red-600 hover:underline">
                Kosongkan keranjang
            </button>
        </div>
    </div>
    @endif
</div>
