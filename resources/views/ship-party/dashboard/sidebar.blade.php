<div class="space-y-3">
    <div class="p-6">
        <p class="text-sm text-gray-500">Portal Pihak Kapal</p>
        <p class="font-semibold text-lg">{{ auth()->user()->managedShipmentCourier?->name ?? 'Ekspedisi Kapal' }}</p>
        <span class="inline-block mt-1 px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
            Aktif
        </span>
    </div>

    <div class="p-4 space-y-2">
        <a wire:navigate href="{{ route('ship-party.dashboard', ['tab' => 'overview']) }}"
            class="block px-3 py-2 rounded-sm {{ $tab === 'overview' ? 'bg-primary text-white' : 'hover:bg-gray-50' }}">
            <span class="flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                </svg>
                Dashboard
            </span>
        </a>

        <a wire:navigate href="{{ route('ship-party.dashboard', ['tab' => 'shipments']) }}"
            class="block px-3 py-2 rounded-sm {{ in_array($tab, ['shipments', 'order-detail']) ? 'bg-primary text-white' : 'hover:bg-gray-50' }}">
            <span class="flex items-center justify-between">
                <span class="flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                    </svg>
                    Pengiriman
                </span>
                @if ($this->pendingRequestCount > 0)
                    <span class="bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">
                        {{ $this->pendingRequestCount > 9 ? '9+' : $this->pendingRequestCount }}
                    </span>
                @endif
            </span>
        </a>

        <a wire:navigate href="{{ route('ship-party.dashboard', ['tab' => 'tracking']) }}"
            class="block px-3 py-2 rounded-sm {{ $tab === 'tracking' ? 'bg-primary text-white' : 'hover:bg-gray-50' }}">
            <span class="flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                </svg>
                Tracking
            </span>
        </a>

        <form method="POST" action="{{ route('user.logout') }}">
            @csrf
            <button type="submit" class="block w-full text-left px-3 py-2 rounded-sm hover:bg-gray-50 text-red-600 cursor-pointer">
                <span class="flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5.636 5.636a9 9 0 1 0 12.728 0M12 3v9" />
                    </svg>
                    Logout
                </span>
            </button>
        </form>
    </div>
</div>
