<div>
    @include('layouts.navbar')
    <div class="min-h-screen  p-12">
        <div class="">


            <!-- Product Detail Section -->
            <div class=" rounded-lg  ">
                <div class="flex gap-12 h-175 ">
                    <!-- Product Images -->
                    <div class="flex-1 space-y-4 relative">
                        <!-- Main Image -->
                        <img src="{{ Storage::url($productImages->first()->image) }}" alt="Produk"
                            class="w-full h-full object-cover object-center rounded-2xl">

                        <!-- Thumbnail Images -->
                        <div class="grid grid-cols-4 gap-2 absolute bottom-4 right-4 left-4">
                            @forelse ($productImages as $mages )
                            <img src="{{ Storage::url($mages->image) }}"
                                class=" w-full aspect-square object-cover cursor-pointer rounded-2xl border-2 border-white hover:border-primary ">

                            @empty

                            @endforelse

                        </div>
                    </div>

                    <!-- Product Info -->
                    <div class="flex-1 space-y-6" wire:loading.class="opacity-50 pointer-events-none"
                        wire:target.except="addToCart">

                        @if (session('success'))
                        <div class="p-3 rounded-lg border border-green-200 bg-green-50 text-green-700">
                            {{ session('success') }}
                        </div>
                        @endif

                        <a href="{{ route('produk', ['category' => $product->category->id]) }}"
                            class="flex justify-between items-center ">
                            <p class="uppercase px-3 py-2 border hover:border-primary  rounded-lg">
                                {{ $product->category->name }}
                            </p>
                        </a>
                        <h1 class="text-4xl font-bold  uppercase">{{ $product->name }}</h1>

                        <!-- Price -->
                        <div class="space-y-2">
                            <div class="flex items-baseline space-x-2">
                                <span class="text-3xl font-bold ">
                                    Rp {{ number_format((float) ($selectedVariant?->price ?? 0), 0, ',', '.') }}
                                </span>
                                <span class="text-lg ">/Unit</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-6 gap-4">
                            @foreach ($productVariants as $variant)

                            <button type="button" wire:click="selectVariant({{ $variant->id }})"
                                class="px-2 py-3 border rounded-lg text-center cursor-pointer hover:border-primary {{ (int) $selectedVariantId === (int) $variant->id ? 'bg-primary text-white' : '' }}">
                                {{ $variant->variant_name }}
                            </button>

                            @endforeach

                        </div>


                        <!-- Stock Info -->
                        <div class="flex justify-between items-center">
                            <span class="text-lg font-medium ">Stok</span>
                            <span class="text-lg ">{{ $selectedVariant?->stock ?? 0 }} Unit</span>
                        </div>

                        <!-- Quantity Selector -->
                        <div class="space-y-2">
                            <p class="text-lg font-medium ">Kuantitas</p>
                            <div class="flex items-center space-x-3">
                                <button type="button" wire:click="decrementQuantity" wire:loading.attr="disabled"
                                    class="w-10 h-10 border  rounded-lg flex items-center justify-center cursor-pointer hover:border-primary">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M20 12H4">
                                        </path>
                                    </svg>
                                </button>
                                <input type="text" value="{{ $quantity }}"
                                    class="w-16 h-10 text-center border  rounded-lg  appearance-none " readonly />
                                <button type="button" wire:click="incrementQuantity" wire:loading.attr="disabled"
                                    class="w-10 h-10 border  rounded-lg flex items-center justify-center   cursor-pointer hover:border-primary">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 4v16m8-8H4"></path>
                                    </svg>
                                </button>
                            </div>

                            @error('selectedVariantId')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            @error('quantity')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>



                        <!-- Add to Cart Button -->
                        @component('components.form.button', [
                        'label' => 'Masukan Ke Keranjang',
                        'class' => 'w-full bg-primary text-white',
                        'wireClick' => 'addToCart',
                        'wireLoadingTarget' => 'addToCart',
                        'wireLoadingClass' => 'opacity-70 cursor-not-allowed',


                        ])
                        @endcomponent

                        <h1 class="text-2xl/normal font-semibold">Deskripsi Produk</h1>
                        <p class=" leading-relaxed">
                            Produk adalah sepeda listrik modern dengan desain yang elegan dan
                            performa yang handal.
                            Dilengkapi dengan motor listrik yang powerful dan baterai tahan lama, sepeda ini cocok
                            untuk
                            mobilitas
                            sehari-hari yang ramah lingkungan.
                        </p>
                    </div>
                </div>
            </div>


        </div>
    </div>

    @include('layouts.footter')
</div>
