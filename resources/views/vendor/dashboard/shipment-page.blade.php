@php
$statusColors = [
'pending' => 'bg-yellow-100 text-yellow-800',
'shipped' => 'bg-indigo-100 text-indigo-800',
'delivered' => 'bg-green-100 text-green-800',
];
@endphp

<div class="p-6 rounded-xl">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div class="mb-6">
            <h2 class="text-xl font-semibold">Daftar Pengiriman</h2>
            <p class="text-sm text-gray-500">Monitor status pengiriman pesanan Anda.</p>
        </div>
        <div class="flex gap-3 flex-wrap">
            @component('components.form.input', [
            'label' => '',
            'type' => 'text',
            'wireModel' => 'search',
            'placeholder' => 'Cari no. resi...',
            'live' => true,
            ]) @endcomponent
            @component('components.form.select', [
            'label' => '',
            'wireModel' => 'selectedStatus',
            'default' => [
            'label' => 'Semua Status',
            'value' => '',
            ],
            'options' => $statusOptions,
            ]) @endcomponent
        </div>
    </div>

    <div class="overflow-x-auto" wire:loading.class="opacity-50 pointer-events-none">
        <div class="space-y-4">
            @forelse ($shipments as $shipment)
            @php
            $orderVendor = $shipment->orderVendor;
            $order = $orderVendor?->order;
            @endphp
            <div class="border rounded-lg overflow-hidden">
                <div class="px-5 py-4 flex flex-wrap items-center justify-between gap-4">
                    <div class="space-y-1">
                        <div class="flex items-center gap-3">
                            <span class="text-sm font-semibold">{{ $order?->order_number ?? '-' }}</span>
                            <span
                                class="inline-block px-2.5 py-1 rounded-full text-xs font-semibold {{ $statusColors[$shipment->status->value] ?? 'bg-gray-100' }}">
                                {{ $shipment->status->getLabel() }}
                            </span>
                        </div>
                        <p class="text-xs text-gray-500">Pembeli: {{ $order?->user?->name ?? '-' }}</p>
                        @if ($shipment->tracking_number)
                        <p class="text-xs text-gray-600">
                            <span class="font-medium">Resi:</span> {{ $shipment->tracking_number }}
                        </p>
                        @endif
                        @if ($shipment->shipmentCourier)
                        <p class="text-xs text-gray-500">
                            Kurir: {{ $shipment->shipmentCourier->name }}
                            {{ $shipment->shipmentCourier->service ? '- ' . $shipment->shipmentCourier->service : '' }}
                        </p>
                        @endif
                        @if ($shipment->shipmentAddress)
                        <p class="text-xs text-gray-500">
                            Tujuan: {{ $shipment->shipmentAddress->city }}, {{ $shipment->shipmentAddress->province }}
                        </p>
                        @endif
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="text-right text-xs text-gray-500 space-y-0.5">
                            @if ($shipment->shipped_at)
                            <p>Dikirim: {{ $shipment->shipped_at->format('d M Y') }}</p>
                            @endif
                            @if ($shipment->delivered_at)
                            <p>Diterima: {{ $shipment->delivered_at->format('d M Y') }}</p>
                            @endif
                            @if ($shipment->shipping_cost)
                            <p>Ongkir: Rp {{ number_format((float) $shipment->shipping_cost, 0, ',', '.') }}</p>
                            @endif
                        </div>
                        <button type="button" wire:click="viewOrder({{ $orderVendor?->id }})"
                            class="px-3 py-1.5 border border-gray-300 rounded-lg text-xs font-medium hover:bg-gray-100">
                            Detail
                        </button>
                    </div>
                </div>
            </div>
            @empty
            <div class="p-8 text-center border rounded-lg">
                <svg class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                </svg>
                <p class="text-gray-500">Belum ada pengiriman.</p>
            </div>
            @endforelse
        </div>
    </div>

    <div class="mt-6">
        {{ $shipments->links() }}
    </div>
</div>
