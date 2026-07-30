<div class="space-y-3">
    <div class="p-6 ">
        <p class="text-sm text-gray-500">Dashboard</p>
        <p class="font-semibold text-lg">Akun Saya</p>
        @if (auth()->check() && auth()->user()->role)
        <span
            class="inline-block mt-1 px-2 py-0.5 rounded-full text-xs font-medium bg-primary/10 text-primary capitalize">
            {{ auth()->user()->role->name }}
        </span>
        @endif
    </div>

    <div class="p-4  space-y-2">
        <a wire:navigate href="{{ route('user.dashboard', ['tab' => 'profile']) }}"
            class="block px-3 py-2 rounded-sm {{ $tab === 'profile' ? 'bg-primary text-white' : 'hover:bg-gray-50' }}">
            Profil
        </a>

        <a wire:navigate href="{{ route('user.dashboard', ['tab' => 'history']) }}"
            class="block px-3 py-2 rounded-sm {{ $tab === 'history' || $tab === 'order-detail' ? 'bg-primary text-white' : 'hover:bg-gray-50' }}">
            Riwayat Pesanan
        </a>

        <a wire:navigate href="{{ route('user.dashboard', ['tab' => 'tracking']) }}"
            class="block px-3 py-2 rounded-sm {{ $tab === 'tracking' ? 'bg-primary text-white' : 'hover:bg-gray-50' }}">
            Tracking Pengiriman
        </a>

        <a wire:navigate href="{{ route('user.dashboard', ['tab' => 'notifications']) }}"
            class="block px-3 py-2 rounded-sm {{ $tab === 'notifications' ? 'bg-primary text-white' : 'hover:bg-gray-50' }}">
            Notifikasi
        </a>

        <a href="{{ route('cart.index') }}" class="block px-3 py-2 rounded-sm hover:bg-gray-50">
            Keranjang Belanja
        </a>

        <form method="POST" action="{{ route('user.logout') }}">
            @csrf
            <button type="submit"
                class="block w-full text-left px-3 py-2 rounded-sm hover:bg-gray-50 text-red-600 cursor-pointer">
                Logout
            </button>
        </form>
    </div>
</div>
