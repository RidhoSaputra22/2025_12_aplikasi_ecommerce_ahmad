<div class="p-6 space-y-6">
    <div class="flex items-end justify-between gap-4">
        <div>
            <h2 class="text-xl font-semibold">Pengiriman Ekspedisi</h2>
            <p class="text-sm text-gray-500">Semua permintaan pengiriman yang masuk ke pihak kapal.</p>
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

    <div class="border rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="px-4 py-3 text-left">Order</th>
                        <th class="px-4 py-3 text-left">Penerima</th>
                        <th class="px-4 py-3 text-left">Resi</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-left">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse ($shipments as $shipment)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <p class="font-semibold">{{ $shipment->orderVendor?->order?->order_number ?? '-' }}</p>
                                <p class="text-xs text-gray-500">{{ $shipment->created_at?->format('d M Y, H:i') }}</p>
                            </td>
                            <td class="px-4 py-3">
                                <p>{{ $shipment->orderVendor?->order?->user?->name ?? '-' }}</p>
                                <p class="text-xs text-gray-500">{{ $shipment->shipmentAddress?->city ?? '-' }}</p>
                            </td>
                            <td class="px-4 py-3">{{ $shipment->tracking_number ?? '-' }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-block px-2.5 py-1 rounded-full text-xs font-semibold
                                    {{ $shipment->status->value === 'pending' ? 'bg-yellow-100 text-yellow-800' : ($shipment->status->value === 'shipped' ? 'bg-indigo-100 text-indigo-800' : 'bg-green-100 text-green-800') }}">
                                    {{ $shipment->status->getLabel() }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <button type="button" wire:click="viewOrder({{ $shipment->order_vendor_id }})"
                                    class="text-primary hover:underline">
                                    Lihat detail
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-gray-500">
                                Belum ada data pengiriman.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($shipments instanceof \Illuminate\Contracts\Pagination\Paginator)
        {{ $shipments->links() }}
    @endif
</div>
