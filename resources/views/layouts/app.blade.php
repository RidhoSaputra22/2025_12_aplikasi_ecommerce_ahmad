<!doctype html>
<html>

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Swiper --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@12/swiper-bundle.min.css" />

    {{-- AOS --}}
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">


    <style>
    /* outline helper (sementara): tambahkan rule jika diperlukan */
    [x-cloak] {
        display: none !important;
    }
    </style>

    <!-- Livewire -->
    @livewireStyles

    <title>E-Commerce Desa</title>

</head>

<body class="relative">
    @isset($slot)
    {{ $slot }}
    @else
    @yield('content')
    @endisset

    <div class="fixed bottom-5 right-5 bg-amber-300 z-100 p-3 rounded-2xl font-semibold">
        Aplikasi Demo
    </div>


    @livewire('global-modal')

    {{-- Swiper --}}
    <script src="https://cdn.jsdelivr.net/npm/swiper@12/swiper-bundle.min.js"></script>

    {{-- AOS --}}
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

    <!-- Livewire -->
    @livewireScripts
    @livewireScriptConfig

    @stack('scripts')
    <script>
    AOS.init();
    </script>
</body>

</html>
