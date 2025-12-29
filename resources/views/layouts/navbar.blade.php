@php
$navActive = function (string|array $patterns, string $active = 'text-primary ', string $inactive =
'hover:text-primary') {
return request()->routeIs($patterns) ? $active : $inactive;
};

$isActive = function (string|array $patterns): bool {
return request()->routeIs($patterns);
};
@endphp

<div class="h-20 flex justify-between py-5 px-12 ">
    <div class="flex gap-15">
        <div class="flex items-center gap-2">
            <!-- <img src="{{ asset('images/logo.jpg') }}" alt="" class="w-14 aspect-square"> -->
            <div class="">
                <h1 class="text-xl font-semibold ">Toko Desa</h1>
                <h1 class="text-sm/tight font-light">Melayani sejak 2010</h1>
            </div>
        </div>
        <ul class="flex gap-10 items-center">
            <li>
                <a href="{{ route('welcome') }}" class=" hover:text-primary {{ $navActive('welcome') }}">Beranda</a>
            </li>
            <li>
                <a href="{{ route('produk') }}" class=" hover:text-primary {{ $navActive('produk*') }}">Cari
                    Produk</a>
            </li>
            <li>
                <a href="{{ route('tentang') }}" class=" hover:text-primary {{ $navActive('tentang') }}">Tentang
                    Kami</a>
            </li>
        </ul>
    </div>
    <div class="flex">
        <ul class="flex gap-5 items-center">
            <li>
                <a href="{{ route('user.login') }}" class="hover:text-primary">Masuk</a>
            </li>
            <li>
                <a href="{{ route('user.register') }}" class="hover:text-primary">Daftar</a>
            </li>
        </ul>
    </div>
</div>
