<div class="p-6 space-y-6">
    <div>
        <h2 class="text-xl font-semibold">Dashboard Pihak Kapal</h2>
        <p class="text-sm text-gray-500">Ringkasan logistik untuk ekspedisi {{ $courier?->name ?? 'kapal' }}.</p>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="border rounded-lg p-4 space-y-1">
            <p class="text-sm text-gray-500">Total Pengiriman</p>
            <p class="text-2xl font-bold">{{ $stats['totalShipments'] ?? 0 }}</p>
            <p class="text-xs text-gray-500">{{ $courier?->service ?? '-' }}</p>
        </div>
        <div class="border rounded-lg p-4 space-y-1">
            <p class="text-sm text-gray-500">Permintaan Masuk</p>
            <p class="text-2xl font-bold text-yellow-600">{{ $stats['pendingShipments'] ?? 0 }}</p>
            <p class="text-xs text-yellow-600">Menunggu proses</p>
        </div>
        <div class="border rounded-lg p-4 space-y-1">
            <p class="text-sm text-gray-500">Sedang Dikirim</p>
            <p class="text-2xl font-bold text-indigo-600">{{ $stats['shippedShipments'] ?? 0 }}</p>
            <p class="text-xs text-indigo-600">Dalam perjalanan</p>
        </div>
        <div class="border rounded-lg p-4 space-y-1">
            <p class="text-sm text-gray-500">Tiba di Tujuan</p>
            <p class="text-2xl font-bold text-green-600">{{ $stats['deliveredShipments'] ?? 0 }}</p>
            <p class="text-xs text-green-600">Selesai diantar</p>
        </div>
    </div>

    <div class="grid grid-cols-3 gap-4">
        <div class="border rounded-lg p-3 text-center">
            <p class="text-2xl font-bold text-yellow-600">{{ $stats['pendingShipments'] ?? 0 }}</p>
            <p class="text-xs text-gray-500 mt-1">Menunggu</p>
        </div>
        <div class="border rounded-lg p-3 text-center">
            <p class="text-2xl font-bold text-blue-600">{{ $stats['shippedShipments'] ?? 0 }}</p>
            <p class="text-xs text-gray-500 mt-1">Dikirim</p>
        </div>
        <div class="border rounded-lg p-3 text-center">
            <p class="text-2xl font-bold text-green-600">{{ $stats['deliveredShipments'] ?? 0 }}</p>
            <p class="text-xs text-gray-500 mt-1">Tiba</p>
        </div>
    </div>

    <div class="space-y-3">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold">Logistik Terbaru</h3>
            <a wire:navigate href="{{ route('ship-party.dashboard', ['tab' => 'shipments']) }}" class="text-sm text-primary hover:underline">
                Lihat Semua →
            </a>
        </div>

        @forelse ($recentShipments as $shipment)
            <a wire:navigate href="{{ route('ship-party.dashboard', ['tab' => 'order-detail', 'order_id' => $shipment->order_vendor_id]) }}"
                class="block border rounded-lg p-4 hover:bg-gray-50">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold">{{ $shipment->orderVendor?->order?->order_number ?? '-' }}</p>
                        <p class="text-xs text-gray-500">
                            {{ $shipment->orderVendor?->order?->user?->name ?? 'Customer' }} ·
                            {{ $shipment->created_at?->format('d M Y, H:i') }}
                        </p>
                    </div>
                    <div class="text-right">
                        <span class="inline-block px-2.5 py-1 rounded-full text-xs font-semibold
                            {{ $shipment->status->value === 'pending' ? 'bg-yellow-100 text-yellow-800' : ($shipment->status->value === 'shipped' ? 'bg-indigo-100 text-indigo-800' : 'bg-green-100 text-green-800') }}">
                            {{ $shipment->status->getLabel() }}
                        </span>
                        <p class="text-sm font-semibold mt-1">{{ $shipment->tracking_number ?? 'Resi otomatis saat dikirim' }}</p>
                    </div>
                </div>
            </a>
        @empty
            <div class="p-8 text-center border rounded-lg">
                <p class="text-gray-500">Belum ada logistik pengiriman untuk ekspedisi ini.</p>
            </div>
        @endforelse
    </div>
</div>
