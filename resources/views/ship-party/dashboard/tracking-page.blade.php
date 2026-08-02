<div class="p-6 space-y-6">
    <div class="flex items-end justify-between gap-4">
        <div>
            <h2 class="text-xl font-semibold">Tracking Pengiriman</h2>
            <p class="text-sm text-gray-500">Pantau status kiriman yang dikelola pihak kapal.</p>
        </div>

        <div class="flex gap-3">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari order / resi"
                class="border rounded-lg px-3 py-2 text-sm w-56">
            <select wire:model.live="selectedStatus" class="border rounded-lg px-3 py-2 text-sm">
                <option value="">Semua status</option>
                @foreach ($statusOptions as $option)
                    <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="space-y-4">
        @forelse ($shipments as $shipment)
            @php
                $firstItem = $shipment->orderVendor?->orderItems?->first();
                $product = $firstItem?->productVariant?->product;
                $image = $product?->productImages?->first();
            @endphp
            <div class="border rounded-xl p-5">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-start gap-4">
                        <div class="h-16 w-16 bg-gray-100 rounded-lg overflow-hidden shrink-0">
                            <img src="{{ Storage::url($image?->image ?? 'products/product_placeholder.jpg') }}"
                                alt="{{ $product?->name ?? 'Produk' }}" class="w-full h-full object-cover">
                        </div>
                        <div>
                            <p class="font-semibold">{{ $shipment->orderVendor?->order?->order_number ?? '-' }}</p>
                            <p class="text-sm text-gray-500">{{ $shipment->orderVendor?->order?->user?->name ?? '-' }}</p>
                            <p class="text-xs text-gray-500 mt-1">{{ $shipment->tracking_number ?? 'Resi akan dibuat otomatis saat kirim' }}</p>
                        </div>
                    </div>
                    <span class="inline-block px-2.5 py-1 rounded-full text-xs font-semibold
                        {{ $shipment->status->value === 'pending' ? 'bg-yellow-100 text-yellow-800' : ($shipment->status->value === 'shipped' ? 'bg-indigo-100 text-indigo-800' : 'bg-green-100 text-green-800') }}">
                        {{ $shipment->status->getLabel() }}
                    </span>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-3 mt-4 text-sm">
                    <div>
                        <p class="text-gray-500">Alamat Tujuan</p>
                        <p class="font-medium">{{ $shipment->shipmentAddress?->address ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Kota</p>
                        <p class="font-medium">{{ $shipment->shipmentAddress?->city ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Status Vendor</p>
                        <p class="font-medium">{{ $shipment->orderVendor?->status?->getLabel() ?? '-' }}</p>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="button" wire:click="viewOrder({{ $shipment->order_vendor_id }})"
                        class="text-primary hover:underline text-sm">
                        Kelola detail pengiriman
                    </button>
                </div>
            </div>
        @empty
            <div class="p-8 text-center border rounded-lg text-gray-500">
                Belum ada data tracking.
            </div>
        @endforelse
    </div>

    @if ($shipments instanceof \Illuminate\Contracts\Pagination\Paginator)
        {{ $shipments->links() }}
    @endif
</div>
