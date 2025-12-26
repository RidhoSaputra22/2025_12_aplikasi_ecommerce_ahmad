@extends('layouts.app')

@section('content')
    @component('layouts.navbar')
    @endcomponent

    <!-- About Us Hero Section -->
    <div class="min-h-screen bg-gray-50 py-16">
        <div class="max-w-7xl mx-auto px-6">
            <!-- Breadcrumb -->


            <!-- Main Content -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center mt-24">
                <!-- Image Section -->
                <div class="order-2 lg:order-1">
                    <div class="relative rounded-2xl overflow-hidden shadow-2xl">
                        <img src="{{ Storage::url('about-us.jpg') }}" alt="Armada Store Interior"
                            class="w-full h-96 lg:h-[500px] object-cover">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent"></div>
                    </div>
                </div>

                <!-- Content Section -->
                <div class="order-1 lg:order-2 space-y-6">
                    <div>
                        <h1 class="text-4xl lg:text-5xl font-bold  mb-4 text-green-800"
                            style="font-family: 'Poppins', sans-serif;">
                            TokoKami.com
                        </h1>
                        <div class="w-20 h-1 bg-green-800 mb-6"></div>
                    </div>

                    <div class="prose prose-lg text-gray-600 leading-relaxed">
                        <p class="mb-6">
                            TokoKami.com adalah toko swalayan yang menyediakan beragam peralatan rumah tangga
                            berkualitas dengan harga terjangkau. Berlokasi strategis di depan Pasar Sentral Takalala,
                            tepatnya di Jl. Andi Wana, Tettikengrarae, Kec. Mario Riwawo, Kabupaten Soppeng,
                            Sulawesi Selatan 90862, kami menjadi tujuan utama masyarakat sekitar untuk memenuhi
                            kebutuhan rumah tangga mereka.
                        </p>

                        <p class="mb-6">
                            Produk-produk yang kami tawarkan meliputi alat dapur, peralatan kebersihan,
                            perlengkapan mandi, hingga berbagai aksesori rumah yang modern dan fungsional.
                            Dengan pelayanan ramah dan stok barang yang lengkap, kami memastikan kenyamanan
                            berbelanja bagi setiap pelanggan.
                        </p>
                    </div>

                    <!-- Key Features -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-8">
                        <div class="flex items-center space-x-3">
                            <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-green-800  " fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                                    </path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-800">Lokasi Strategis</h3>
                                <p class="text-sm text-gray-600">Dekat Pasar Sentral Takalala</p>
                            </div>
                        </div>

                        <div class="flex items-center space-x-3">
                            <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-green-800  " fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1">
                                    </path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-800">Harga Terjangkau</h3>
                                <p class="text-sm text-gray-600">Produk berkualitas dengan harga bersaing</p>
                            </div>
                        </div>

                        <div class="flex items-center space-x-3">
                            <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-green-800  " fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-800">Stok Lengkap</h3>
                                <p class="text-sm text-gray-600">Berbagai pilihan produk rumah tangga</p>
                            </div>
                        </div>

                        <div class="flex items-center space-x-3">
                            <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-green-800  " fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1m4 0h1m-6 4h.01M19 10a9 9 0 11-18 0 9 9 0 0118 0z">
                                    </path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-800">Pelayanan Ramah</h3>
                                <p class="text-sm text-gray-600">Tim yang siap membantu pelanggan</p>
                            </div>
                        </div>
                    </div>

                    <!-- CTA Button -->
                    <div class="pt-6">
                        <a href="/etalase"
                            class="inline-flex items-center px-6 py-3 bg-green-800   text-white font-semibold rounded-lg hover:shadow-lg transition-colors duration-300">
                            Lihat Produk Kami
                            <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                                </path>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Statistics Section -->
            <div class="mt-20 bg-white rounded-2xl shadow-lg p-8">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                    <div class="text-center">
                        <div class="text-3xl font-bold text-green-800    mb-2">5+</div>
                        <p class="text-gray-600">Tahun Berpengalaman</p>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl font-bold text-green-800    mb-2">1000+</div>
                        <p class="text-gray-600">Produk Tersedia</p>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl font-bold text-green-800    mb-2">5000+</div>
                        <p class="text-gray-600">Pelanggan Puas</p>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl font-bold text-green-800    mb-2">24/7</div>
                        <p class="text-gray-600">Layanan Konsultasi</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @component('layouts.footter')
    @endcomponent
@endsection
