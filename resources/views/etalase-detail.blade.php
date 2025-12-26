@extends('layouts.app')

@section('content')
    @component('layouts.navbar')
    @endcomponent

    <div class="min-h-screen mt-32">
        <div class="flex flex-col items-center justify-center mt-20 mb-20">
            <h1 class="text-4xl font-bold mb-8">Nama Produk</h1>
            <div class="w-full max-w-3xl mb-8 border-b flex items-center-safe">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search"
                    viewBox="0 0 16 16">
                    <path
                        d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0" />
                </svg>
                <input type="text" name="" id=""
                    class="w-full  rounded-l-md px-4 py-2 focus:outline-none " placeholder="Cari produk...">
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8 px-10">
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
            </div>
        </div>
    </div>

    @component('layouts.footter')
    @endcomponent
@endsection
