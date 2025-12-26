@extends('layouts.app')

@section('content')
    @component('layouts.navbar')
    @endcomponent

    <div class="min-h-screen bg-gray-50 py-8">
        <div class="max-w-7xl mx-auto px-4">
            <!-- Breadcrumb -->
            <nav class="flex mb-8" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="/"
                            class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600">
                            Home
                        </a>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            <a href="/produk"
                                class="ml-1 text-sm font-medium text-gray-700 hover:text-blue-600 md:ml-2">Produk</a>
                        </div>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Produk</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <!-- Product Detail Section -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 p-8">
                    <!-- Product Images -->
                    <div class="space-y-4">
                        <!-- Main Image -->
                        <div class="aspect-square bg-gray-100 rounded-lg overflow-hidden">
                            <img src="{{ Storage::url('furniture-1.jpg') }}" alt="Produk"
                                class="w-full h-full object-cover">
                        </div>

                        <!-- Thumbnail Images -->
                        <div class="grid grid-cols-4 gap-2">
                            <div class="aspect-square bg-gray-100 rounded-lg overflow-hidden border-2 border-blue-500">
                                <img src="{{ Storage::url('furniture-1.jpg') }}" alt="D75 Winfly View 1"
                                    class="w-full h-full object-cover cursor-pointer">
                            </div>
                            <div class="aspect-square bg-gray-100 rounded-lg overflow-hidden">
                                <img src="{{ Storage::url('furniture-1.jpg') }}" alt="D75 Winfly View 2"
                                    class="w-full h-full object-cover cursor-pointer">
                            </div>
                            <div class="aspect-square bg-gray-100 rounded-lg overflow-hidden">
                                <img src="{{ Storage::url('furniture-1.jpg') }}" alt="D75 Winfly View 3"
                                    class="w-full h-full object-cover cursor-pointer">
                            </div>
                            <div class="aspect-square bg-gray-100 rounded-lg overflow-hidden">
                                <img src="{{ Storage::url('furniture-1.jpg') }}" alt="D75 Winfly View 4"
                                    class="w-full h-full object-cover cursor-pointer">
                            </div>
                        </div>
                    </div>

                    <!-- Product Info -->
                    <div class="space-y-6">
                        <!-- Product Title -->
                        <h1 class="text-4xl font-bold text-gray-800">Produk</h1>

                        <!-- Stock Info -->
                        <div class="flex justify-between items-center">
                            <span class="text-lg font-medium text-gray-600">Stok</span>
                            <span class="text-lg text-gray-800">0 Unit</span>
                        </div>

                        <!-- Quantity Selector -->
                        <div class="space-y-2">
                            <label class="text-lg font-medium text-gray-600">Kuantitas</label>
                            <div class="flex items-center space-x-3">
                                <button
                                    class="w-10 h-10 border border-gray-300 rounded-lg flex items-center justify-center text-gray-600 hover:bg-gray-100 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4">
                                        </path>
                                    </svg>
                                </button>
                                <input type="number" value="1" min="0" max="0"
                                    class="w-16 h-10 text-center border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <button
                                    class="w-10 h-10 border border-gray-300 rounded-lg flex items-center justify-center text-gray-600 hover:bg-gray-100 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 4v16m8-8H4"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Price -->
                        <div class="space-y-2">
                            <div class="flex items-baseline space-x-2">
                                <span class="text-3xl font-bold text-gray-800">Rp 4.900.000</span>
                                <span class="text-lg text-gray-500">Unit</span>
                            </div>
                        </div>

                        <!-- Add to Cart Button -->
                        <button
                            class="w-full bg-green-600 text-white py-3 px-6 rounded-lg text-lg font-semibold hover:bg-green-700 transition-colors disabled:bg-gray-400 disabled:cursor-not-allowed">
                            Masukkan keranjang
                        </button>

                        <!-- Product Description -->
                        <div class="pt-6 border-t border-gray-200">
                            <h2 class="text-xl font-semibold text-gray-800 mb-3">Deskripsi Produk</h2>
                            <p class="text-gray-600 leading-relaxed">
                                Produk adalah sepeda listrik modern dengan desain yang elegan dan
                                performa yang handal.
                                Dilengkapi dengan motor listrik yang powerful dan baterai tahan lama, sepeda ini cocok untuk
                                mobilitas
                                sehari-hari yang ramah lingkungan.
                            </p>
                        </div>

                        <!-- Specifications -->
                        <div class="pt-6 border-t border-gray-200">
                            <h2 class="text-xl font-semibold text-gray-800 mb-3">Spesifikasi</h2>
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Motor</span>
                                    <span class="text-gray-800">350W Brushless</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Baterai</span>
                                    <span class="text-gray-800">48V 10Ah Lithium</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Jarak Tempuh</span>
                                    <span class="text-gray-800">50-70 km</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Kecepatan Maksimal</span>
                                    <span class="text-gray-800">25 km/h</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Berat</span>
                                    <span class="text-gray-800">22 kg</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Related Products Section -->
            <div class="mt-16">
                <h2 class="text-3xl font-bold text-gray-800 mb-8">Produk Terkait</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <a href="/produk/detail/1">
                        <div class="bg-white rounded-lg shadow-lg p-6 w-72 flex flex-col items-center">
                            <div class="h-40 w-40 bg-center bg-cover rounded-md mb-4"
                                style="background-image: url({{ Storage::url('furniture-1.jpg') }});"></div>
                            <h2 class="text-xl font-semibold text-gray-800 mb-2">Produk 1</h2>
                            <p class="text-gray-600 mb-4">Deskripsi singkat produk 1.</p>
                            <span class="text-green-500 font-bold mb-2">Rp 500.000</span>
                            <button class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">Beli
                                Sekarang</button>
                        </div>
                    </a>
                    <a href="/produk/detail/2">
                        <div class="bg-white rounded-lg shadow-lg p-6 w-72 flex flex-col items-center">
                            <div class="h-40 w-40 bg-center bg-cover rounded-md mb-4"
                                style="background-image: url({{ Storage::url('model-1.jpg') }});"></div>
                            <h2 class="text-xl font-semibold text-gray-800 mb-2">Produk 2</h2>
                            <p class="text-gray-600 mb-4">Deskripsi singkat produk 2.</p>
                            <span class="text-green-500 font-bold mb-2">Rp 350.000</span>
                            <button class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">Beli
                                Sekarang</button>
                        </div>
                    </a>
                    <a href="/produk/detail/2">
                        <div class="bg-white rounded-lg shadow-lg p-6 w-72 flex flex-col items-center">
                            <div class="h-40 w-40 bg-center bg-cover rounded-md mb-4"
                                style="background-image: url({{ Storage::url('aksesoris-1.jpg') }});"></div>
                            <h2 class="text-xl font-semibold text-gray-800 mb-2">Produk 3</h2>
                            <p class="text-gray-600 mb-4">Deskripsi singkat produk 3.</p>
                            <span class="text-green-500 font-bold mb-2">Rp 150.000</span>
                            <button class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">Beli
                                Sekarang</button>
                        </div>
                    </a>
                    <a href="/produk/detail/2">
                        <div class="bg-white rounded-lg shadow-lg p-6 w-72 flex flex-col items-center">
                            <div class="h-40 w-40 bg-center bg-cover rounded-md mb-4"
                                style="background-image: url({{ Storage::url('aksesoris-1.jpg') }});"></div>
                            <h2 class="text-xl font-semibold text-gray-800 mb-2">Produk 3</h2>
                            <p class="text-gray-600 mb-4">Deskripsi singkat produk 3.</p>
                            <span class="text-green-500 font-bold mb-2">Rp 150.000</span>
                            <button class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">Beli
                                Sekarang</button>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    @component('layouts.footter')
    @endcomponent
@endsection
