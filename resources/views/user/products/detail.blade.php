<div>
    @livewire('navbar')

    <div class="min-h-screen  p-12">
        <div class="">


            <!-- Product Detail Section -->
            <div class=" rounded-lg  ">
                <div class="flex gap-12 h-175 ">
                    <!-- Product Images -->
                    <div class="flex-1 space-y-4 relative">
                        <!-- Main Image -->
                        <img src="{{ Storage::url($productImages->first()?->image) }}" alt="Produk"
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

                        <a href="{{ route('produk.cari', ['category' => $product->category->slug]) }}"
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
                            {{ $product->description }}
                        </p>
                    </div>
                </div>
            </div>


        </div>
    </div>

    <div class=" px-12  flex gap-14" wire:ignore>
        <div class="flex-1 space-y-5">
            <h1 class="text-2xl/loose font-semibold">Deskripsi Toko</h1>
            <div class="flex gap-5 items-center">
                <img src="{{ Storage::url($product->vendor?->logo ?? 'vendor/user-1.png') }}" alt=""
                    class="size-14 rounded-full object-cover object-center">
                <div class="space-y-1">
                    <h1 class="text-2xl font-bold ">
                        {{ $product->vendor->store_name }}
                    </h1>
                    <p class="text-sm/normal font-light">
                        {{ $product->vendor->user->email }}
                    </p>
                </div>
            </div>
            <div class="text-xl  font-light">
                {{$product->vendor->description}}
            </div>
        </div>
        <div class="flex-1 w-1/2 space-y-5">
            <h1 class="text-2xl/loose font-semibold">Produk Lainnya dari toko ini</h1>

            <div class="swiper vendorProducts  " data-aos="fade-up" >
                <div class="swiper-wrapper ">
                    @foreach ($product->vendor->products as $key => $product)
                    <a href="{{ route('produk.detail', ['slug' => $product->slug]) }}" class="swiper-slide "
                        wire:key="product-{{ $product->id }}">
                        <div class="relative w-full aspect-4/3 overflow-hidden rounded-xl">
                            <img src="{{ asset('images/product-paceholder.jpg') }}"
                                class="w-full h-full object-cover">

                            <span
                                class="absolute top-2 left-2 bg-primary px-3 py-1 rounded-md text-xs font-medium text-white">
                                asd
                            </span>
                        </div>
                        <div class="mt-4 space-y-2">
                            <h1 class="text-xl font-light text-overflow-ellipsis truncate uppercase">
                                {{ $product->name }}
                            </h1>
                            <h1 class="text-lg font-semibold mt-2">Rp.
                                {{ number_format($product->price, 0, ',', ',') }}
                            </h1>
                            <div class="flex gap-2 text-primary items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="size-6">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M8.25 21v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21m0 0h4.5V3.545M12.75 21h7.5V10.75M2.25 21h1.5m18 0h-18M2.25 9l4.5-1.636M18.75 3l-1.5.545m0 6.205 3 1m1.5.5-1.5-.5M6.75 7.364V3h-3v18m3-13.636 10.5-3.819" />
                                </svg>
                                <p class="truncate text-md/loose w-1/2">Toko
                                    {{ $product->vendor->store_name  ?? 'Nama Toko' }}
                                </p>

                            </div>
                        </div>
                    </a>
                    @endforeach


                </div>
                <div class="swiper-pagination"></div>
            </div>
        </div>
    </div>

    <div class="px-12 py-24 space-y-14" wire:ignore>
        <h1 class="text-2xl/loose font-semibold">Review Produk</h1>
        <div class="swiper comentarSwiper h-96 w-full" data-aos="fade-up">
            <div class="swiper-wrapper ">
                @forelse ($reviews as $i => $review)
                <div class="relative swiper-slide h-96 w-full px-5
                ">
                    <div class="flex gap-5 ">
                        <img src="{{ Storage::url($review->user?->profile_photo_path ?? 'user-placeholder.png') }}"
                            alt="" class="size-14 rounded-full object-cover object-center ">
                        <div class="">
                            <h1 class="text-lg font-semibold ">{{ $review->user->name }}</h1>
                            <p class="text-sm/normal font-light">{{ $review->user->email }}</p>
                        </div>
                    </div>
                    <div>
                        <p class="mt-5 text-sm/loose font-light">
                            "{{ $review->comment}}"
                        </p>
                    </div>
                </div>
                @empty
                <h1>Belum ada review untuk produk ini.</h1>
                @endforelse


            </div>
            <div class="swiper-pagination"></div>
        </div>



    </div>

    @include('layouts.footter')
</div>
@push('scripts')
<script>
const vendorProducts = new Swiper(".vendorProducts", {
    slidesPerView: 3,
    spaceBetween: 16,
    loop: true,
    speed: 400,
    autoplay: {
        delay: 2500,
        disableOnInteraction: false,
    },
});
const comentarSwiper = new Swiper(".comentarSwiper", {
    slidesPerView: 4,
    spaceBetween: 16,
    loop: true,
    speed: 400,
    autoplay: {
        delay: 2500,
        disableOnInteraction: false,
    },
});
</script>
@endpush
