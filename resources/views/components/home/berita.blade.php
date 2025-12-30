<section class="w-full  gap-10 p-12 space-y-14" wire:ignore>
    <div class="  pt-8" data-aos="fade-up">
        <div class="max-w-4xl space-y-4">
            <h1 class="text-6xl/tight font-semibold">Berita Terbaru</h1>
            <p class="text-lg/loose font-light">Dapatkan informasi terkini seputar desa dan inovasi kami</p>
        </div>
    </div>
    <div class="swiper beritaSwiper h-96 w-full" data-aos="fade-up">
        <div class="swiper-wrapper ">
            @php
            $berita = [
            [
            'judul' => 'Pembukaan Pasar Desa Pertama di Indonesia',
            'subjudul' => 'Pemerintah meresmikan pasar desa yang menghubungkan petani lokal dengan konsumen.',
            'gambar' => 'banner-1.jpg'
            ],
            [
            'judul' => 'Inovasi Kerajinan Tangan dari Desa Terpencil',
            'subjudul' => 'Pengrajin desa menciptakan produk unik yang menarik perhatian pasar global.',
            'gambar' => 'banner-2.jpg'
            ],
            [
            'judul' => 'Peningkatan Ekonomi Melalui E-Commerce Desa',
            'subjudul' => 'Platform e-commerce membantu UMKM desa meningkatkan penjualan mereka secara signifikan.',
            'gambar' => 'banner-3.jpg'
            ],
            [
            'judul' => 'Pelatihan Digital untuk Pengusaha Desa',
            'subjudul' => 'Program pelatihan memberikan keterampilan digital kepada pengusaha desa untuk mengembangkan
            bisnis mereka.',
            'gambar' => 'about-1.jpg'
            ],
            ]


            @endphp
            @foreach ($berita as $i => $beritaItem)
            <a href="#" class="relative swiper-slide h-96 w-full px-5
                ">
                <img src="{{ asset('images/' . $beritaItem['gambar']) }}" alt=""
                    class=" object-cover object-center h-96 w-full rounded-xl ">

                <div class="absolute bottom-0 left-5 bg-white bg-opacity-75 p-4 rounded-tr-md max-w-md h-32 space-y-1">
                    <h1 class="text-lg font-semibold ">{{ $beritaItem['judul'] }}</h1>
                    <p class="text-sm/normal font-light">{{ $beritaItem['subjudul'] }}</p>
                </div>

            </a>
            @endforeach


        </div>
        <div class="swiper-pagination"></div>
    </div>

</section>

@push('scripts')
<script>
const beritaSwiper = new Swiper(".beritaSwiper", {
    slidesPerView: 3,
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
