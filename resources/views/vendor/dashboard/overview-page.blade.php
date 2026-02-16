<div class="p-6 space-y-6">
    {{-- Header --}}
    <div>
        <h2 class="text-xl font-semibold">Dashboard Vendor</h2>
        <p class="text-sm text-gray-500">Ringkasan aktivitas toko Anda.</p>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Total Produk --}}
        <div class="border rounded-lg p-4 space-y-1">
            <div class="flex items-center justify-between">
                <p class="text-sm text-gray-500">Total Produk</p>
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" /></svg>
            </div>
            <p class="text-2xl font-bold">{{ $stats['totalProducts'] ?? 0 }}</p>
            <p class="text-xs text-green-600">{{ $stats['activeProducts'] ?? 0 }} aktif</p>
        </div>

        {{-- Total Pesanan --}}
        <div class="border rounded-lg p-4 space-y-1">
            <div class="flex items-center justify-between">
                <p class="text-sm text-gray-500">Total Pesanan</p>
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" /></svg>
            </div>
            <p class="text-2xl font-bold">{{ $stats['totalOrders'] ?? 0 }}</p>
            <p class="text-xs text-yellow-600">{{ $stats['pendingOrders'] ?? 0 }} menunggu</p>
        </div>

        {{-- Saldo Wallet --}}
        <div class="border rounded-lg p-4 space-y-1">
            <div class="flex items-center justify-between">
                <p class="text-sm text-gray-500">Saldo Wallet</p>
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 12a2.25 2.25 0 0 0-2.25-2.25H15a3 3 0 1 1-6 0H5.25A2.25 2.25 0 0 0 3 12m18 0v6a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 18v-6m18 0V9M3 12V9m18 0a2.25 2.25 0 0 0-2.25-2.25H5.25A2.25 2.25 0 0 0 3 9m18 0V6a2.25 2.25 0 0 0-2.25-2.25H5.25A2.25 2.25 0 0 0 3 6v3" /></svg>
            </div>
            <p class="text-2xl font-bold">Rp {{ number_format($stats['balance'] ?? 0, 0, ',', '.') }}</p>
            <p class="text-xs text-gray-500">Saldo tersedia</p>
        </div>

        {{-- Total Pendapatan --}}
        <div class="border rounded-lg p-4 space-y-1">
            <div class="flex items-center justify-between">
                <p class="text-sm text-gray-500">Total Pendapatan</p>
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941" /></svg>
            </div>
            <p class="text-2xl font-bold">Rp {{ number_format($stats['totalRevenue'] ?? 0, 0, ',', '.') }}</p>
            <p class="text-xs text-green-600">{{ $stats['completedOrders'] ?? 0 }} order selesai</p>
        </div>
    </div>

    {{-- Order Summary --}}
    <div class="grid grid-cols-4 gap-4">
        <div class="border rounded-lg p-3 text-center">
            <p class="text-2xl font-bold text-yellow-600">{{ $stats['pendingOrders'] ?? 0 }}</p>
            <p class="text-xs text-gray-500 mt-1">Menunggu</p>
        </div>
        <div class="border rounded-lg p-3 text-center">
            <p class="text-2xl font-bold text-blue-600">{{ $stats['processedOrders'] ?? 0 }}</p>
            <p class="text-xs text-gray-500 mt-1">Diproses</p>
        </div>
        <div class="border rounded-lg p-3 text-center">
            <p class="text-2xl font-bold text-indigo-600">{{ $stats['shippedOrders'] ?? 0 }}</p>
            <p class="text-xs text-gray-500 mt-1">Dikirim</p>
        </div>
        <div class="border rounded-lg p-3 text-center">
            <p class="text-2xl font-bold text-green-600">{{ $stats['completedOrders'] ?? 0 }}</p>
            <p class="text-xs text-gray-500 mt-1">Selesai</p>
        </div>
    </div>

    {{-- Recent Orders --}}
    <div class="space-y-3">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold">Pesanan Terbaru</h3>
            <a wire:navigate href="{{ route('vendor.dashboard', ['tab' => 'orders']) }}"
                class="text-sm text-primary hover:underline">Lihat Semua →</a>
        </div>

        @forelse ($recentOrders as $orderVendor)
            @php
                $statusColors = [
                    'pending' => 'bg-yellow-100 text-yellow-800',
                    'processed' => 'bg-blue-100 text-blue-800',
                    'shipped' => 'bg-indigo-100 text-indigo-800',
                    'completed' => 'bg-green-100 text-green-800',
                ];
            @endphp
            <div class="border rounded-lg p-4 hover:bg-gray-50 cursor-pointer"
                wire:click="$dispatch('vendor-view-order', { id: {{ $orderVendor->id }} })">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold">{{ $orderVendor->order?->order_number ?? '-' }}</p>
                        <p class="text-xs text-gray-500">{{ $orderVendor->order?->user?->name ?? 'Customer' }} &middot; {{ $orderVendor->created_at?->format('d M Y, H:i') }}</p>
                    </div>
                    <div class="text-right">
                        <span class="inline-block px-2.5 py-1 rounded-full text-xs font-semibold {{ $statusColors[$orderVendor->status->value] ?? 'bg-gray-100' }}">
                            {{ $orderVendor->status->getLabel() }}
                        </span>
                        <p class="text-sm font-semibold mt-1">Rp {{ number_format((float) $orderVendor->subtotal, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>
        @empty
            <div class="p-8 text-center border rounded-lg">
                <svg class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <p class="text-gray-500">Belum ada pesanan masuk.</p>
            </div>
        @endforelse
    </div>
</div>
