<section class="w-full  gap-10 p-12 space-y-14" wire:ignore>
    <div class="  pt-8" data-aos="fade-up">
        <div class="max-w-4xl space-y-4">
            <h1 class="text-6xl/tight font-semibold">Lebih dari 100 UMKM sudah bergabung dengan kami</h1>
            <p class="text-lg/loose font-light">Dengarkan kisah mereka disini</p>
        </div>
    </div>
    <div class="swiper gallerySwiper h-96 w-full" data-aos="fade-up">
        <div class="swiper-wrapper ">
            @php
            $reviews = [
            [
            'name' => 'Budi Santoso',
            'store' => 'Kerajinan Bambu Asli',
            'review' => 'Sudah lima tahun bergabung dengan Toko Desa, penjualan saya meningkat pesat berkat
            platform ini.'
            ],
            [
            'name' => 'Siti Aminah',
            'store' => 'Tenun Tradisional Lombok',
            'review' => 'Saya ingat pertama kali bergabung dengan Toko Desa, saya merasa ragu. Namun, setelah
            melihat peningkatan penjualan dan dukungan yang saya terima, saya yakin ini adalah keputusan
            terbaik yang pernah saya buat.'
            ],
            [
            'name' => 'Agus Wijaya',
            'store' => 'Kuliner Khas Jogja',
            'review' => 'Toko Desa telah membantu saya menjangkau pelanggan di seluruh Indonesia. Saya
            sangat berterima kasih atas kesempatan ini.'
            ],
            [
            'name' => 'Dewi Lestari',
            'store' => 'Aksesoris Handmade Bali',
            'review' => 'Bergabung dengan Toko Desa adalah salah satu keputusan terbaik yang saya buat untuk
            bisnis saya. Platform ini sangat mudah digunakan dan tim support-nya sangat responsif.'
            ],
            [
            'name' => 'Rina Marlina',
            'store' => 'Produk Herbal Nusantara',
            'review' => 'Mengenalkan produk herbal saya melalui Toko Desa telah membuka banyak pintu peluang baru. Saya
            sangat senang dengan hasilnya.'
            ]
            ]


            @endphp
            @foreach ($reviews as $i => $review)
            <div class="relative swiper-slide h-96 w-full px-5
                ">
                <div class="flex gap-5 ">
                    <img src="{{ asset('images/user-' . ($i + 1) . '.png') }}" alt=""
                        class="size-14 rounded-full object-cover object-center ">
                    <div class="">
                        <h1 class="text-lg font-semibold ">{{ $review['name'] }}</h1>
                        <p class="text-sm/normal font-light">Pemilik {{ $review['store'] }}</p>
                    </div>
                </div>
                <div>
                    <p class="mt-5 text-sm/loose font-light">
                        "{{ $review['review'] }}"
                    </p>
                </div>
            </div>
            @endforeach


        </div>
        <div class="swiper-pagination"></div>
    </div>

</section>

@push('scripts')
<script>
const gallerySwiper = new Swiper(".gallerySwiper", {
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
