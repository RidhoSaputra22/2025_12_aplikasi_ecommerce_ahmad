@extends('layouts.app')

@section('content')
    @component('layouts.navbar')
    @endcomponent

    <div class="min-h-screen">
        <div class="flex flex-col items-center justify-center mt-20 mb-20">
            <h1 class="text-4xl font-bold mb-8">Etalase Produk Kami</h1>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8 px-10">
                <a href="">
                    <div class="h-80 w-65 bg-center bg-cover rounded-md mb-4"
                        style="background-image: url({{ Storage::url('furniture-1.jpg') }});">
                        <div
                            class="h-full w-full flex items-center justify-center hover:backdrop-brightness-50 text-white text-lg font-semibold rounded-md">
                            Etalase 1
                        </div>
                    </div>
                </a>
                <a href="/etalase/detail/etalase-5">

                    <div class="h-80 w-65 bg-center bg-cover rounded-md mb-4"
                        style="background-image: url({{ Storage::url('model-1.jpg') }});">
                        <div
                            class="h-full w-full flex items-center justify-center hover:backdrop-brightness-50 text-white text-lg font-semibold rounded-md">
                            Etalase 2
                        </div>
                    </div>
                </a>
                <a href="/etalase/detail/etalase-3">

                    <div class="h-80 w-65 bg-center bg-cover rounded-md mb-4"
                        style="background-image: url({{ Storage::url('model-2.jpg') }});">
                        <div
                            class="h-full w-full flex items-center justify-center hover:backdrop-brightness-50 text-white text-lg font-semibold rounded-md">
                            Etalase 3
                        </div>
                    </div>
                </a>
                <a href="/etalase/detail/etalase-4">

                    <div class="h-80 w-65 bg-center bg-cover rounded-md mb-4"
                        style="background-image: url({{ Storage::url('aksesoris-1.jpg') }});">
                        <div
                            class="h-full w-full flex items-center justify-center hover:backdrop-brightness-50 text-white text-lg font-semibold rounded-md">
                            Etalase 4
                        </div>
                    </div>
                </a>
                <a href="/etalase/detail/etalase-5">
                    <div class="h-80 w-65 bg-center bg-cover rounded-md mb-4"
                        style="background-image: url({{ Storage::url('aksesoris-2.jpg') }});">
                        <div
                            class="h-full w-full flex items-center justify-center hover:backdrop-brightness-50 text-white text-lg font-semibold rounded-md">
                            Etalase 5
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>

    @component('layouts.footter')
    @endcomponent
@endsection
