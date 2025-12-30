<section class="  z-50 relative -mt-12 " wire:init="loadInitialData">
    @if (! $readyToLoad)
    <div class="h-12 flex">
        <div class="bg-white flex-5 rounded-tr-2xl flex justify-between">
            @for ($i = 0; $i < 5; $i++) <div
                class="flex-1 px-8 flex items-center justify-center {{ $i === 4 ? 'rounded-tr-2xl' : '' }}">
                <div class="h-5 w-28 bg-gray-200 rounded animate-pulse"></div>
        </div>
        @endfor
    </div>
    <div class="flex-6"></div>
    </div>

    <div class="py-24 px-12 space-y-10">
        <div class="space-y-5">
            <h1 class="h-10 w-96 bg-gray-200 rounded animate-pulse"></h1>
            <p class="h-5 w-32 bg-gray-200 rounded animate-pulse"></p>
        </div>

        <div class="grid grid-cols-5 gap-5 overflow-y-auto">
            @for ($i = 0; $i < 5; $i++) <div>
                <div class="rounded-xl bg-gray-200 aspect-square animate-pulse"></div>
                <div class="mt-4 space-y-2">
                    <div class="h-5 w-4/5 bg-gray-200 rounded animate-pulse"></div>
                    <div class="h-5 w-1/2 bg-gray-200 rounded animate-pulse"></div>
                    <div class="h-5 w-3/5 bg-gray-200 rounded animate-pulse"></div>
                </div>
        </div>
        @endfor
    </div>
    </div>
    @else
    <div class=" h-12 flex ">
        <div class="bg-white flex-5 rounded-tr-2xl flex justify-between ">
            @foreach ($categories as $key => $category)
            <button type="submit" wire:click="selectCategory('{{ $category->name }}')"
                class="truncate relative flex-1 px-8 flex items-center justify-center {{$key == 4 ? ' rounded-tr-2xl' : ''}} hover:bg-primary hover:text-white cursor-pointer {{ $selectedCategoryId == $category->id ? 'bg-primary text-white' : '' }}">
                {{$category->name}}
            </button>
            @endforeach
        </div>
        <div class="flex-6 "></div>
    </div>

    <div class="py-24 px-12  space-y-10 ">

        <div>
            <h1 class=" text-4xl/normal font-semibold">{{ $selectedCategoryName }}</h1>
            <p class="text-lg font-light">Cari {{ $selectedCategoryName }} disini</p>
        </div>

        <div class="grid grid-cols-5 gap-5 overflow-y-auto" wire:loading.class="opacity-60"
            wire:target="selectCategory">
            @foreach ($products as $key => $product)
            <a href="{{ route('produk.detail', ['slug' => $product->slug]) }}" class="" wire:key="product-{{ $product->id }}">
                <div class="relative">
                    <img src="{{ asset('images/product-paceholder.jpg') }}" alt="" class="rounded-xl">
                      <div
                            class="absolute top-2 left-2 bg-primary px-3 py-1 rounded-md text-sm font-medium text-white">
                            {{ $product->category->name }}</div>
                </div>
                <div class="mt-4 space-y-2">
                    <h1 class="text-xl font-light text-overflow-ellipsis truncate uppercase">
                            {{ $product->name }}
                        </h1>
                        <h1 class="text-lg font-semibold mt-2">Rp.
                            {{ number_format($product->price, 0, ',', ',') }}
                        </h1>
                    <div class="flex gap-2 text-primary items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="size-6">
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
        <div class="flex justify-end">
           <a href="{{ route('produk.cari') }}" class=" px-3 py-2  rounded-sm  outline-offset-1 bg-primary text-white hover:bg-secondary hover:outline hover:outline-2 outline-primary text-center   ">
            Lihat Produk Lainnya >>
        </a>
        </div>
    </div>
    @endif
    <div class=" px-12 min-h-screen space-y-10 ">

        <div class="">
            <h1 class="text-4xl/normal font-semibold">Produk Unggulan</h1>
            <p class="text-lg font-light">Cari produk unggulan disini</p>
        </div>

        @if (!$readyToLoad)
        <div class="grid grid-cols-5 gap-5 overflow-y-auto">
            @for ($i = 0; $i < 5; $i++) <div>
                <div class="rounded-xl bg-gray-200 aspect-square animate-pulse"></div>
                <div class="mt-4 space-y-2">
                    <div class="h-5 w-4/5 bg-gray-200 rounded animate-pulse"></div>
                    <div class="h-5 w-1/2 bg-gray-200 rounded animate-pulse"></div>
                    <div class="h-5 w-3/5 bg-gray-200 rounded animate-pulse"></div>
                </div>
        </div>
        @endfor
    </div>
    @else
    <div class="grid grid-cols-5 gap-5 overflow-y-auto">
        @foreach ($produkUnggulan as $key => $product)
          <a href="{{ route('produk.detail', ['slug' => $product->slug]) }}" class="" wire:key="product-{{ $product->id }}">
                <div class="relative">
                    <img src="{{ asset('images/product-paceholder.jpg') }}" alt="" class="rounded-xl">
                      <div
                            class="absolute top-2 left-2 bg-primary px-3 py-1 rounded-md text-sm font-medium text-white">
                            {{ $product->category->name }}</div>
                </div>
                <div class="mt-4 space-y-2">
                    <h1 class="text-xl font-light text-overflow-ellipsis truncate uppercase">
                            {{ $product->name }}
                        </h1>
                        <h1 class="text-lg font-semibold mt-2">Rp.
                            {{ number_format($product->price, 0, ',', ',') }}
                        </h1>
                    <div class="flex gap-2 text-primary items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="size-6">
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
    <div class="flex justify-end">
        <a href="{{ route('produk.cari') }}" class=" px-3 py-2  rounded-sm  outline-offset-1 bg-primary text-white hover:bg-secondary hover:outline hover:outline-2 outline-primary text-center   ">
            Lihat Produk Lainnya >>
        </a>
    </div>



    @endif
    </div>

</section>
