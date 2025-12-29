@php
$data = [
[
'judul' => 'Produk Kerajinan Lokal',
'keterangan' => 'Temukan berbagai produk kerajinan tangan unik dan berkualitas dari pengrajin lokal kami.',
'image' => 'images/banner-1.jpg',
],
[
'judul' => 'Hasil Pertanian Segar',
'keterangan' => 'Dapatkan hasil pertanian segar langsung dari petani desa dengan harga terbaik.',
'image' => 'images/banner-2.jpg',
],
[
'judul' => 'Kebutuhan Sehari-hari',
'keterangan' => 'Lengkapi kebutuhan sehari-hari Anda dengan produk-produk berkualitas dari desa kami.',
'image' => 'images/banner-3.jpg',
]
]
@endphp

<section class="">
    <!-- Swiper -->
    <div class="p-12">
        <div class="swiper bannerSwiper h-100  rounded-2xl">
            <div class="w-full h-full swiper-wrapper">
                @foreach ($data as $item)
                <div class="relative w-full h-full swiper-slide text-white">
                    <img src="{{ asset($item['image']) }}" alt="" class="object-cover w-full h-full">
                    <div class="absolute inset-0 w-full h-full bg-linear-to-tr from-black to-transparent">
                    </div>
                    <div class="absolute left-0  bottom-0 p-12">
                        <div class="">
                            <h1 class="text-4xl/normal font-semibold">
                                {{ $item['judul'] }}
                            </h1>
                            <p class="text-lg font-light ">{{ $item['keterangan'] }}</p>
                        </div>
                    </div>
                </div>
                @endforeach

            </div>
            <div class="swiper-pagination"></div>
        </div>
    </div>
</section>

@push('scripts')
<script>
const swiper = new Swiper(".bannerSwiper", {
    slidesPerView: 1,
    centeredSlides: true,
    loop: true,
    speed: 400,
    // spaceBetween: 30,

    autoplay: {
        delay: 2500,
        disableOnInteraction: false,
    },



    pagination: {
        el: ".swiper-pagination",

    },
});
</script>
@endpush
