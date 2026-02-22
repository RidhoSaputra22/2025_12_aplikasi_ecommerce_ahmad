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
            <a href="{{ route('welcome') }}" class="">
                <h1 class="text-xl font-semibold ">Toko Desa</h1>
                <h1 class="text-sm/tight font-light">Melayani sejak 2010</h1>
            </a>
        </div>
        <ul class="flex gap-10 items-center">
            <li>
                <a href="{{ route('welcome') }}" class=" hover:text-primary {{ $navActive('') }}">Beranda</a>
            </li>
            <li>
                <a href="{{ route('produk.cari') }}" class=" hover:text-primary {{ $navActive('produk*') }}">Cari
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
            @if (auth()->check())
            <li class="relative">
                <a href="{{ auth()->user()->role?->name === 'vendor' ? route('vendor.dashboard') : route('user.dashboard', ['tab' => 'notifications']) }}"
                    class="block hover:text-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                    </svg>
                </a>
                @if ($this->unreadNotificationCount > 0)
                <span
                    class="absolute -top-2 -right-2.5 text-sm/normal h-5 w-5 bg-red-500 text-white flex items-center justify-center rounded-full">
                    {{ $this->unreadNotificationCount > 9 ? '9+' : $this->unreadNotificationCount }}
                </span>
                @endif
            </li>
            @if (auth()->user()->role?->name === 'customer')
            <li class="relative ">
                <a href="{{ route('cart.index') }}" class="block hover:text-primary {{ $navActive('cart*') }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" />
                    </svg>

                </a>
                <span
                    class="absolute -top-2 -right-2.5  text-sm/normal h-5 w-5 bg-primary text-white flex items-center justify-center rounded-full">
                    {{ $this->cartCount }}
                </span>
                <div class="absolute -bottom-12 -right-1.5 w-52" x-data="{ show: false }"
                    x-on:cart-updated-nav.window="show = true; setTimeout(() => show = false, 2000)" x-cloak
                    x-show="show" x-transition.duration.500ms>
                    <div class="relative">
                        <span
                            class="absolute -top-3 z-20 right-2 inline-block w-0 h-0 border-solid border-t-0 border-r-[9px] border-l-[9px] border-b-[17.3px] border-l-transparent border-r-transparent border-t-transparent border-b-white">
                        </span>
                        <span
                            class="absolute -top-3 -z-10 right-2  inline-block w-0 h-0 border-solid border-t-0 border-r-[9px] border-l-[9px] border-b-[17.3px] border-l-transparent border-r-transparent border-t-transparent border-b-gray-600">
                        </span>
                        <div
                            class=" text-xs text-center px-3 py-2 bg-white border border-gray-300 rounded-md shadow-md">
                            Keranjang Bertambah
                        </div>
                    </div>

                </div>
            </li>

            @endif

            <li class="ml-3">
                @component('components.dropdown', [
                'align' => 'right',
                'width' => 'min-w-sm',


                ])
                @slot('trigger')
                Hello, {{ auth()->user()->name }}
                @endslot
                @slot('content')
                <div>
                    <div class="border-b border-gray-400">
                        <div class="flex gap-3 items-center mb-3  mx-4 my-3">

                            <img src="{{ Storage::url(auth()->user()->vendor?->logo ?? 'user-placeholder.png') }}"
                                alt="" class="size-13 aspect-square object-cover rounded-full ">
                            <div>
                                <h1 class="text-lg/tight font-semibold">{{ auth()->user()->name }}</h1>
                                <span class="text-sm/tight font-light text-gray-500">{{ auth()->user()->email }}</span>
                            </div>
                        </div>
                    </div>
                    <div class=" ">
                        <a href="{{ auth()->user()->role?->name === 'vendor' ? route('vendor.dashboard') : route('user.dashboard') }}"
                            class="text-sm font-medium block px-4 py-4 hover:bg-gray-100">
                            {{ auth()->user()->role?->name === 'vendor' ? 'Dashboard Vendor' : 'Lihat Profil' }}
                        </a>
                        @if (auth()->user()->vendor)

                        @endif
                        <a href="{{ route('user.logout') }}"
                            class="text-sm font-medium block px-4 py-4 hover:bg-gray-100">
                            Logout
                        </a>
                    </div>
                </div>

                @endslot


                @endcomponent
            </li>
            @endif
            @if(!auth()->check())
            <li>
                <a href="{{ route('user.login') }}" class="hover:text-primary">Masuk</a>
            </li>
            <li>
                <a href="{{ route('user.register') }}" class="hover:text-primary">Daftar</a>
            </li>
            @endif
        </ul>
    </div>
</div>
