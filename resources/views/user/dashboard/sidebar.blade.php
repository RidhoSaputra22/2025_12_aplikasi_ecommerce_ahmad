<div class="space-y-3">
    <div class="p-6 rounded-xl border bg-white">
        <p class="text-sm text-gray-500">Dashboard</p>
        <p class="font-semibold text-lg">Akun Saya</p>
    </div>

    <div class="p-4 rounded-xl border bg-white space-y-2">
        <a wire:navigate href="{{ route('user.dashboard', ['tab' => 'profile']) }}"
            class="block px-3 py-2 rounded-lg {{ $tab === 'profile' ? 'bg-primary text-white' : 'hover:bg-gray-50' }}">
            Profil
        </a>

        <a wire:navigate href="{{ route('user.dashboard', ['tab' => 'history']) }}"
            class="block px-3 py-2 rounded-lg {{ $tab === 'history' ? 'bg-primary text-white' : 'hover:bg-gray-50' }}">
            Riwayat
        </a>

        <a href="{{ route('user.logout') }}" class="block px-3 py-2 rounded-lg hover:bg-gray-50 text-red-600">
            Logout
        </a>
    </div>
</div>
