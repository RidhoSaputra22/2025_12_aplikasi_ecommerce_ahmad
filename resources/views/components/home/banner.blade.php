<section class="">

    <!-- Swiper -->
    <div class="swiper bannerSwiper h-150">
        <div class="w-full h-full swiper-wrapper">
            <div class="relative w-full h-full swiper-slide">
                <img src="{{ asset('images/banner-1.jpg') }}" alt="" class="object-cover w-full h-full">
                <div class="absolute inset-0 w-full h-full bg-linear-to-br from-black/30 to-transparent">
                </div>
                <div class="absolute inset-0 w-full h-full px-6 md:px-10 lg:px-20">
                    <div class="grid h-full max-w-4xl mx-auto place-content-center">
                        <h1
                            class="text-6xl font-semibold tracking-tighter text-center text-white capitalize wrap-break-word md:text-8xl">
                            Selamat Datang di website TokoDesa
                        </h1>
                    </div>
                </div>
            </div>
            <div class="relative w-full h-full swiper-slide">
                <img src="{{ asset('images/banner-2.jpg') }}" alt="kantor " class="object-cover w-full h-full">
                <div class="absolute inset-0 w-full h-full bg-linear-to-r from-black/50 via-black/50 to-black/25">
                </div>
                <div class="absolute inset-0 w-full h-full px-6 md:px-10 lg:px-40">
                    <div class="flex items-center h-full max-w-7xl mx-auto">
                        <div class="space-y-8">
                            <h1
                                class="text-6xl font-semibold tracking-tighter text-center text-white capitalize wrap-break-word lg:text-left md:text-8xl">
                                Kerajinan Desa
                            </h1>
                            <div class="grid grid-cols-1 lg:grid-cols-2">
                                <div class="px-10 lg:px-0">
                                    <p
                                        class="text-xl font-light leading-snug text-center text-white md:text-2xl lg:text-left">
                                        Lebih mudah mendapatkan berbagai kerajinan tangan asli desa dengan kualitas
                                        terbaik
                                        dan harga yang terjangkau.
                                    </p>
                                </div>
                            </div>
                            <div class="flex justify-center lg:justify-start">
                                <a href="#"
                                    class="flex items-center justify-center px-4 py-2 space-x-2 text-lg text-white transition bg-primary border border-primary rounded-md w-fit hover:bg-primary/95 focus:ring-4 focus:ring-green-400">
                                    Pelajari lebih lanjut
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="relative w-full h-full swiper-slide">
                <img src="{{ asset('images/banner-3.jpg') }}" alt="salah satu bangunan di "
                    class="object-cover w-full h-full">
                <div class="absolute inset-0 w-full h-full bg-linear-to-l from-black/50 via-black/50 to-black/25">
                </div>
                <div class="absolute inset-0 w-full h-full px-6 md:px-10 lg:px-40">
                    <div class="flex items-center justify-center h-full max-w-7xl mx-auto">
                        <div class="space-y-8">
                            <h1
                                class="text-6xl font-semibold tracking-tighter text-center text-white capitalize wrap-break-word md:text-8xl">
                                Pasar Desa
                            </h1>
                            <div class="w-full px-10 mx-auto lg:w-1/2 lg:px-0">
                                <p class="text-xl font-light leading-snug text-center text-white md:text-2xl">
                                    Dapatkan berbagai kebutuhan sehari-hari langsung dari tangan petani dan nelayan kita
                                </p>
                            </div>
                            <div class="flex justify-center">
                                <a href="#"
                                    class="flex items-center justify-center px-4 py-2 space-x-2 text-lg text-white transition bg-primary border border-primary rounded-md w-fit hover:bg-primary/95 focus:ring-4 focus:ring-green-400">
                                    Pelajari lebih lanjut
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="swiper-pagination"></div>
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
