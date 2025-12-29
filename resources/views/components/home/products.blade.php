<section class="  z-50 relative -mt-12 ">
    <div class=" h-12 flex ">
        <div class="bg-white flex-5 rounded-tr-2xl flex justify-between ">
            @foreach ([1,2,3,4 ,5] as $key => $category)
            <div
                class="relative flex-1 px-8 flex items-center justify-center {{$category == 5 ? ' rounded-tr-2xl' : ''}} hover:bg-primary hover:text-white cursor-pointer ">
                Kategori
                {{$category}}


            </div>

            @endforeach

        </div>
        <div class="flex-6 ">



        </div>

    </div>

    <div class="py-24 px-12  space-y-10 " data-aos="fade-up">

        <div>
            <h1 class="text-4xl/normal font-semibold">Produk Kategori 1</h1>
            <p class="text-lg font-light">Lorem ipsum dolor sit amet.</p>
        </div>

        <div class="grid grid-cols-5 gap-5 overflow-y-auto">
            @foreach ([1,2,3,4,5] as $key => $product)
            <div class="">
                <img src="{{ asset('images/product-paceholder.jpg') }}" alt="" class="rounded-xl">
                <div class="mt-4 space-y-2">
                    <h1 class="text-xl font-light text-overflow-ellipsis truncate uppercase">SEPATU RAJUTAN
                        {{ $product }}
                    </h1>
                    <h1 class="text-lg font-semibold mt-2">Rp. {{ number_format($product * 150000, 0, ',', '.') }}</h1>
                    <div class="flex gap-2 text-primary items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M8.25 21v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21m0 0h4.5V3.545M12.75 21h7.5V10.75M2.25 21h1.5m18 0h-18M2.25 9l4.5-1.636M18.75 3l-1.5.545m0 6.205 3 1m1.5.5-1.5-.5M6.75 7.364V3h-3v18m3-13.636 10.5-3.819" />
                        </svg>
                        <p class="truncate text-md/loose">Toko {{ $product }}</p>

                    </div>
                </div>
            </div>
            @endforeach
        </div>
        <div class="flex justify-end">
            @component('components.form.button', [
            'label' => 'Lihat Semua Produk >>',
            'class' => 'bg-primary text-white'
            ])

            @endcomponent
        </div>
    </div>
    <div class=" px-12 min-h-screen space-y-10 " data-aos="fade-up">

        <div class="">
            <h1 class="text-4xl/normal font-semibold">Produk Unggulan</h1>
            <p class="text-lg font-light">Lorem ipsum dolor sit amet.</p>
        </div>

        <div class="grid grid-cols-5 gap-5 overflow-y-auto">
            @foreach ([1,2,3,4,5] as $key => $product)
            <div class="">
                <img src="{{ asset('images/product-paceholder.jpg') }}" alt="" class="rounded-xl">
                <div class="mt-4 space-y-2">
                    <h1 class="text-xl font-light text-overflow-ellipsis truncate uppercase">SEPATU RAJUTAN
                        {{ $product }}
                    </h1>
                    <h1 class="text-lg font-semibold mt-2">Rp. {{ number_format($product * 150000, 0, ',', '.') }}</h1>
                    <div class="flex gap-2 text-primary items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M8.25 21v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21m0 0h4.5V3.545M12.75 21h7.5V10.75M2.25 21h1.5m18 0h-18M2.25 9l4.5-1.636M18.75 3l-1.5.545m0 6.205 3 1m1.5.5-1.5-.5M6.75 7.364V3h-3v18m3-13.636 10.5-3.819" />
                        </svg>
                        <p class="truncate text-md/loose">Toko {{ $product }}</p>

                    </div>
                </div>
            </div>
            @endforeach
        </div>
        <div class="flex justify-end">
            @component('components.form.button', [
            'label' => 'Lihat Semua Produk >>',
            'class' => 'bg-primary text-white'
            ])

            @endcomponent
        </div>
    </div>

</section>
