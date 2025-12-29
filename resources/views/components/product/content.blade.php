<section class="p-12 min-h-screen ">
    <form class="flex gap-24">
        <div class="flex-1 bg-white rounded-2xl ">
            <h1 class="text-xl font-semibold mb-6">Filter Produk</h1>
            <div class="space-y-6">
                @component('components.form.select', [
                'label' => 'Kategori',
                'name' => 'category',
                'selected' => $request->category ?? null,
                'class' => 'uppercase',
                'default' => [
                'label' => 'Semua Kategori',
                'value' => 'all',
                ],
                'options' => $categories->map(function ($category) {
                return [
                'label' => $category->name,
                'value' => $category->id,
                ];
                }),
                ])
                @endcomponent

                @component('components.form.select', [
                'label' => 'Harga',
                'name' => 'price',
                'selected' => $request->price ?? null,
                'options' => [
                [
                'label' => 'Semua Harga',
                'value' => 'all',
                ],
                [
                'label' => 'Rendah ke Tinggi',
                'value' => 'low_to_high',
                ],
                [
                'label' => 'Tinggi ke Rendah',
                'value' => 'high_to_low',
                ],
                ],
                ])
                @endcomponent

                @component('components.form.select', [
                'label' => 'Urutkan',
                'name' => 'sort',
                'selected' => $request->sort ?? null,
                'options' => [
                [
                'label' => 'Terbaru',
                'value' => 'newest',
                ],
                [
                'label' => 'Terlaris',
                'value' => 'bestseller',
                ],
                [
                'label' => 'Rating Tertinggi',
                'value' => 'top_rated',
                ],
                ],
                ])
                @endcomponent
                @component('components.form.button', [
                'label' => 'Submit',
                'class' => 'w-full bg-primary text-white'
                ])

                @endcomponent
            </div>

        </div>
        <div class="flex-5 space-y-14">
            <div>
                <input type="text" name="search" class="border rounded px-4 py-2 w-full"
                    placeholder="Masukkan nama produk..." value="{{ $request->search ?? '' }}">
            </div>
            <div>
                {{ $products->links() }}
            </div>
            <div class="grid grid-cols-4 gap-10">
                @forelse ($products as $product)
                <a href="{{ route('produk.detail', ['slug' => $product->slug]) }}" class="">
                    <img src="{{ Storage::url($product->productImages->first->image->image ?? 'products/product_placeholder.jpg') }}"
                        alt="" class="rounded-xl w-full h-60 object-cover">
                    <div class="mt-4 space-y-2">
                        <h1 class="text-xl font-light text-overflow-ellipsis truncate uppercase">SEPATU RAJUTAN
                            {{ $product->name }}
                        </h1>
                        <h1 class="text-lg font-semibold mt-2">Rp.
                            {{ number_format($product->price, 0, ',', '.') }}
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
                @empty
                <p class="text-center col-span-4 text-lg font-light">Produk tidak ditemukan</p>
                @endforelse

            </div>
            <div>
                {{ $products->links() }}
            </div>
        </div>
    </form>
</section>
