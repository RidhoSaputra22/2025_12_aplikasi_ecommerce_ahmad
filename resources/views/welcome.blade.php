@extends('layouts.app')

@section('content')
    @component('layouts.navbar')
    @endcomponent

    <div class="h-screen flex justify-center items-center bg-no-repeat  bg-center bg-cover">
        <div class="text-start  h-full w-full flex  text-white px-4">
            <div class="flex-1 flex flex-col ml-20 my-70 ">
                <h1 class=" text-8xl font-bold mb-4 text-black">Selamat Datang di <p class="text-green-400 italic">
                        Ahmad Store</p>
                </h1>
                <p class="text-black text-lg mb-8">Temukan berbagai produk menarik dan berkualitas di sini!</p>
            </div>
            <div class="flex  mx-20 my-30 relative">
                <div class="space-y-3 space-x-3 flex flex-col">
                    <div class=" h-96 w-96 rounded-lg bg-no-repeat bg-center bg-cover"
                        style="background-image: url({{ Storage::url('furniture-1.jpg') }});">
                    </div>
                    <div class=" h-96 w-96 rounded-lg bg-no-repeat bg-center bg-cover"
                        style="background-image: url({{ Storage::url('model-1.jpg') }});">

                    </div>
                </div>
                <div class=" w-96 rounded-lg bg-no-repeat bg-center bg-cover "
                    style="background-image: url({{ Storage::url('aksesoris-1.jpg') }});">

                </div>
                <img src="{{ Storage::url('dot-1.png') }}" class="absolute -top-15 -right-15 -z-10 bg-cover" alt="">
            </div>
        </div>
        <img src="{{ Storage::url('wave-1.png') }}" class="absolute bottom-0 -z-10 bg-cover" alt="">
        <img src="{{ Storage::url('dot-1.png') }}" class="absolute top-0 -left-6 -z-10 bg-cover" alt="">

    </div>
    <div class="relative min-h-screen flex items-center bg-no-repeat  bg-center bg-cover">
        <div class="w-full mt-52">
            <div class="mx-40 ">
                <p class="text-5xl" style="font-family: 'Poppins', sans-serif;">Best Seller</p>
                <div class="flex flex-row space-x-8 mt-8 mx-5 relative ">
                    <div class="bg-white rounded-lg shadow-lg p-6 w-72 flex flex-col items-center">
                        <div class="h-40 w-40 bg-center bg-cover rounded-md mb-4"
                            style="background-image: url({{ Storage::url('furniture-1.jpg') }});"></div>
                        <h2 class="text-xl font-semibold text-gray-800 mb-2">Produk 1</h2>
                        <p class="text-gray-600 mb-4">Deskripsi singkat produk 1.</p>
                        <span class="text-green-500 font-bold mb-2">Rp 500.000</span>
                        <button class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">Beli Sekarang</button>
                    </div>
                    <div class="bg-white rounded-lg shadow-lg p-6 w-72 flex flex-col items-center">
                        <div class="h-40 w-40 bg-center bg-cover rounded-md mb-4"
                            style="background-image: url({{ Storage::url('model-1.jpg') }});"></div>
                        <h2 class="text-xl font-semibold text-gray-800 mb-2">Produk 2</h2>
                        <p class="text-gray-600 mb-4">Deskripsi singkat produk 2.</p>
                        <span class="text-green-500 font-bold mb-2">Rp 350.000</span>
                        <button class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">Beli Sekarang</button>
                    </div>
                    <div class="bg-white rounded-lg shadow-lg p-6 w-72 flex flex-col items-center">
                        <div class="h-40 w-40 bg-center bg-cover rounded-md mb-4"
                            style="background-image: url({{ Storage::url('aksesoris-1.jpg') }});"></div>
                        <h2 class="text-xl font-semibold text-gray-800 mb-2">Produk 3</h2>
                        <p class="text-gray-600 mb-4">Deskripsi singkat produk 3.</p>
                        <span class="text-green-500 font-bold mb-2">Rp 150.000</span>
                        <button class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">Beli Sekarang</button>
                    </div>
                    <img src="{{ Storage::url('dot-1.png') }}" class="absolute top-0 right-9 -z-10 bg-cover w-32"
                        alt="">
                </div>

            </div>
            <div class="mx-40 ">
                <p class="text-5xl text-end" style="font-family: 'Poppins', sans-serif;">Lagi Promo</p>
                <div class="flex flex-row justify-end-safe gap-8 mt-8 mx-8 relative">
                    <div class="bg-white rounded-lg shadow-lg p-6 w-72 flex flex-col items-center">
                        <div class="h-40 w-40 bg-center bg-cover rounded-md mb-4"
                            style="background-image: url({{ Storage::url('furniture-1.jpg') }});"></div>
                        <h2 class="text-xl font-semibold text-gray-800 mb-2">Produk 1</h2>
                        <p class="text-gray-600 mb-4">Deskripsi singkat produk 1.</p>
                        <span class="text-green-500 font-bold mb-2">Rp 500.000</span>
                        <button class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">Beli Sekarang</button>
                    </div>
                    <div class="bg-white rounded-lg shadow-lg p-6 w-72 flex flex-col items-center">
                        <div class="h-40 w-40 bg-center bg-cover rounded-md mb-4"
                            style="background-image: url({{ Storage::url('model-1.jpg') }});"></div>
                        <h2 class="text-xl font-semibold text-gray-800 mb-2">Produk 2</h2>
                        <p class="text-gray-600 mb-4">Deskripsi singkat produk 2.</p>
                        <span class="text-green-500 font-bold mb-2">Rp 350.000</span>
                        <button class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">Beli Sekarang</button>
                    </div>
                    <div class="bg-white rounded-lg shadow-lg p-6 w-72 flex flex-col items-center">
                        <div class="h-40 w-40 bg-center bg-cover rounded-md mb-4"
                            style="background-image: url({{ Storage::url('aksesoris-1.jpg') }});"></div>
                        <h2 class="text-xl font-semibold text-gray-800 mb-2">Produk 3</h2>
                        <p class="text-gray-600 mb-4">Deskripsi singkat produk 3.</p>
                        <span class="text-green-500 font-bold mb-2">Rp 150.000</span>
                        <button class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">Beli Sekarang</button>
                    </div>
                    <img src="{{ Storage::url('dot-1.png') }}" class="absolute bottom-0 left-9 -z-10 bg-cover w-32"
                        alt="">
                </div>
            </div>
        </div>
        <img src="{{ Storage::url('wave-2.png') }}" class="absolute top-0 -z-10 bg-cover" alt="">
    </div>
    <div class="h-screen bg-[#00C060] flex mt-24">
        <div class="h-full w-full bg-cover" style="background-image: url('{{ Storage::url('model-1.jpg') }}')">
        </div>
        <div class="w-full text-white  m-32">
            <h1 class="text-8xl font-semibold">Satu Langkah Nyaman, Sejuta Gaya</h1>
            <p class="text-lg mt-5">
                Di <strong>TokoKami</strong>, kamu bisa menemukan sepatu berkualitas tinggi tanpa perlu khawatir soal
                harga. Cocok untuk kamu yang ingin tampil stylish tanpa kompromi terhadap kenyamanan!
            </p>
            <div class="mt-5">
                <a href="#menu" class="border px-4 py-2 ">Beli Sekarang</a>
            </div>
        </div>
    </div>
    <div class="mt-24">
        <div class="mx-40 mb-40">
            <p class="text-5xl text-center" style="font-family: 'Poppins', sans-serif;">Etalasé</p>
            <div class="flex flex-row justify-center space-x-8 mt-8 mx-5">
                <div class="h-80 w-65 bg-center bg-cover rounded-md mb-4"
                    style="background-image: url({{ Storage::url('furniture-1.jpg') }});">
                    <div
                        class="h-full w-full flex items-center justify-center hover:backdrop-brightness-50 text-white text-lg font-semibold rounded-md">
                        Etalase 1
                    </div>
                </div>
                <div class="h-80 w-65 bg-center bg-cover rounded-md mb-4"
                    style="background-image: url({{ Storage::url('model-1.jpg') }});">
                    <div
                        class="h-full w-full flex items-center justify-center hover:backdrop-brightness-50 text-white text-lg font-semibold rounded-md">
                        Etalase 2
                    </div>
                </div>
                <div class="h-80 w-65 bg-center bg-cover rounded-md mb-4"
                    style="background-image: url({{ Storage::url('model-2.jpg') }});">
                    <div
                        class="h-full w-full flex items-center justify-center hover:backdrop-brightness-50 text-white text-lg font-semibold rounded-md">
                        Etalase 2
                    </div>
                </div>
                <div class="h-80 w-65 bg-center bg-cover rounded-md mb-4"
                    style="background-image: url({{ Storage::url('aksesoris-1.jpg') }});">
                    <div
                        class="h-full w-full flex items-center justify-center hover:backdrop-brightness-50 text-white text-lg font-semibold rounded-md">
                        Etalase 2
                    </div>
                </div>
                <div class="h-80 w-65 bg-center bg-cover rounded-md mb-4"
                    style="background-image: url({{ Storage::url('aksesoris-2.jpg') }});">
                    <div
                        class="h-full w-full flex items-center justify-center hover:backdrop-brightness-50 text-white text-lg font-semibold rounded-md">
                        Etalase 2
                    </div>
                </div>
            </div>
        </div>
    </div>
    @component('layouts.footter')
    @endcomponent
@endsection
