@php
$shipmentStatusColors = [
    'pending' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
    'shipped' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
    'delivered' => 'bg-green-100 text-green-800 border-green-200',
];

$orderStatusColors = [
    'pending' => 'bg-yellow-100 text-yellow-800',
    'processed' => 'bg-blue-100 text-blue-800',
    'shipped' => 'bg-indigo-100 text-indigo-800',
    'completed' => 'bg-green-100 text-green-800',
];

$trackingSteps = [
    ['key' => 'pending', 'label' => 'Pesanan Masuk', 'icon' => 'M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z'],
    ['key' => 'processed', 'label' => 'Dikemas', 'icon' => 'M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z'],
    ['key' => 'shipped', 'label' => 'Dikirim', 'icon' => 'M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12'],
    ['key' => 'delivered', 'label' => 'Sampai Tujuan', 'icon' => 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
];

/**
 * Determine current step based on order vendor status and shipment status.
 */
function getVendorTrackingStep($orderVendor, $shipment) {
    if (!$orderVendor) return 0;

    return match ($orderVendor->status->value) {
        'pending' => 0,
        'processed' => 1,
        'shipped' => 2,
        'completed' => 3,
        default => 0,
    };
}
@endphp

<div class="p-6 rounded-xl">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div class="mb-6">
            <h2 class="text-xl font-semibold">Tracking Pengiriman</h2>
            <p class="text-sm text-gray-500">Pantau dan kelola status pengiriman pesanan secara visual.</p>
        </div>
        <div class="flex gap-3 flex-wrap">
            @component('components.form.input', [
                'label' => '',
                'type' => 'text',
                'wireModel' => 'search',
                'placeholder' => 'Cari no. order / resi...',
                'live' => true,
            ]) @endcomponent
            @component('components.form.select', [
                'label' => '',
                'wireModel' => 'selectedStatus',
                'default' => [
                    'label' => 'Semua Status Order',
                    'value' => '',
                ],
                'options' => $orderStatusOptions,
            ]) @endcomponent
            @component('components.form.select', [
                'label' => '',
                'wireModel' => 'selectedShipmentStatus',
                'default' => [
                    'label' => 'Semua Status Kirim',
                    'value' => '',
                ],
                'options' => $shipmentStatusOptions,
            ]) @endcomponent
        </div>
    </div>

    {{-- Stats summary --}}
    @php
        $allShipments = $shipments->getCollection();
        $pendingCount = $allShipments->where(fn($s) => $s->status === \App\Enums\ShipmentStatus::Pending)->count();
        $shippedCount = $allShipments->where(fn($s) => $s->status === \App\Enums\ShipmentStatus::Shipped)->count();
        $deliveredCount = $allShipments->where(fn($s) => $s->status === \App\Enums\ShipmentStatus::Delivered)->count();
    @endphp
    <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 text-center">
            <div class="text-2xl font-bold text-yellow-700">{{ $pendingCount }}</div>
            <div class="text-xs text-yellow-600 mt-1">Menunggu Kirim</div>
        </div>
        <div class="bg-indigo-50 border border-indigo-200 rounded-xl p-4 text-center">
            <div class="text-2xl font-bold text-indigo-700">{{ $shippedCount }}</div>
            <div class="text-xs text-indigo-600 mt-1">Dalam Perjalanan</div>
        </div>
        <div class="bg-green-50 border border-green-200 rounded-xl p-4 text-center">
            <div class="text-2xl font-bold text-green-700">{{ $deliveredCount }}</div>
            <div class="text-xs text-green-600 mt-1">Sampai Tujuan</div>
        </div>
    </div>

    <div class="overflow-x-auto" wire:loading.class="opacity-50 pointer-events-none">
        <div class="space-y-6">
            @forelse ($shipments as $shipment)
                @php
                    $orderVendor = $shipment->orderVendor;
                    $order = $orderVendor?->order;
                    $currentStep = getVendorTrackingStep($orderVendor, $shipment);
                    $firstItem = $orderVendor?->orderItems?->first();
                    $product = $firstItem?->productVariant?->product;
                    $image = $product?->productImages?->first();
                    $totalItems = $orderVendor?->orderItems?->sum('quantity') ?? 0;
                @endphp
                <div class="border rounded-xl overflow-hidden hover:shadow-md transition-shadow">
                    {{-- Header --}}
                    <div class="bg-gray-50 px-5 py-3 flex flex-wrap items-center justify-between gap-3 border-b">
                        <div class="flex items-center gap-3">
                            <span class="text-sm font-semibold">{{ $order?->order_number ?? '-' }}</span>
                            <span class="text-xs text-gray-500">Pembeli: {{ $order?->user?->name ?? '-' }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="inline-block px-2.5 py-1 rounded-full text-xs font-semibold {{ $orderStatusColors[$orderVendor?->status->value] ?? 'bg-gray-100' }}">
                                {{ $orderVendor?->status->getLabel() }}
                            </span>
                            <span class="inline-block px-2.5 py-1 rounded-full text-xs font-semibold {{ $shipmentStatusColors[$shipment->status->value] ?? 'bg-gray-100 text-gray-800' }}">
                                {{ $shipment->status->getLabel() }}
                            </span>
                        </div>
                    </div>

                    {{-- Tracking Timeline --}}
                    <div class="px-5 py-5">
                        <div class="flex items-center justify-between relative">
                            {{-- Progress line background --}}
                            <div class="absolute top-5 left-0 right-0 h-0.5 bg-gray-200 mx-10"></div>
                            {{-- Progress line active --}}
                            @if ($currentStep > 0)
                                <div class="absolute top-5 left-0 h-0.5 bg-primary mx-10 transition-all duration-500"
                                     style="width: {{ min(($currentStep / (count($trackingSteps) - 1)) * 100, 100) }}%; max-width: calc(100% - 5rem);"></div>
                            @endif

                            @foreach ($trackingSteps as $index => $step)
                                @php
                                    $isCompleted = $index <= $currentStep;
                                    $isCurrent = $index === $currentStep;
                                @endphp
                                <div class="flex flex-col items-center relative z-10 flex-1">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center transition-all duration-300
                                        {{ $isCompleted ? ($isCurrent ? 'bg-primary text-white ring-4 ring-primary/20' : 'bg-primary text-white') : 'bg-gray-200 text-gray-400' }}">
                                        @if ($isCompleted && !$isCurrent)
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                        @else
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $step['icon'] }}" />
                                            </svg>
                                        @endif
                                    </div>
                                    <span class="text-xs mt-2 font-medium {{ $isCompleted ? 'text-primary' : 'text-gray-400' }}">
                                        {{ $step['label'] }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Shipment Details --}}
                    <div class="px-5 pb-4">
                        <div class="flex flex-wrap items-center justify-between gap-4">
                            {{-- Product preview --}}
                            <div class="flex items-center gap-3">
                                <div class="h-12 w-12 bg-gray-100 rounded-lg overflow-hidden shrink-0">
                                    <img src="{{ Storage::url($image?->image ?? 'products/product_placeholder.jpg') }}"
                                         alt="Produk" class="w-full h-full object-cover">
                                </div>
                                <div>
                                    <p class="text-sm font-medium">{{ $product?->name ?? 'Produk' }}</p>
                                    <p class="text-xs text-gray-500">
                                        {{ $totalItems }} barang
                                        @if ($orderVendor?->orderItems && $orderVendor->orderItems->count() > 1)
                                            ({{ $orderVendor->orderItems->count() }} produk)
                                        @endif
                                    </p>
                                </div>
                            </div>

                            {{-- Shipping info --}}
                            <div class="flex flex-wrap items-center gap-6 text-xs text-gray-500">
                                @if ($shipment->shipmentCourier)
                                    <div class="flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                  d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                                        </svg>
                                        <span>{{ $shipment->shipmentCourier->name }}
                                            {{ $shipment->shipmentCourier->service ? '- ' . $shipment->shipmentCourier->service : '' }}
                                        </span>
                                    </div>
                                @endif
                                @if ($shipment->tracking_number)
                                    <div class="flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                  d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z" />
                                        </svg>
                                        <span class="font-mono">{{ $shipment->tracking_number }}</span>
                                    </div>
                                @endif
                                @if ($shipment->shipmentAddress)
                                    <div class="flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                  d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                  d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                                        </svg>
                                        <span>{{ $shipment->shipmentAddress->city }}, {{ $shipment->shipmentAddress->province }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Timestamps & Action --}}
                        <div class="flex flex-wrap items-center justify-between gap-3 mt-4 pt-3 border-t">
                            <div class="flex flex-wrap items-center gap-4 text-xs text-gray-500">
                                @if ($shipment->shipped_at)
                                    <div class="flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Dikirim: {{ $shipment->shipped_at->format('d M Y, H:i') }}
                                    </div>
                                @endif
                                @if ($shipment->delivered_at)
                                    <div class="flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        Sampai: {{ $shipment->delivered_at->format('d M Y, H:i') }}
                                    </div>
                                @endif
                                @if ($shipment->shipping_cost)
                                    <div>
                                        Ongkir: <span class="font-medium text-gray-700">Rp {{ number_format((float) $shipment->shipping_cost, 0, ',', '.') }}</span>
                                    </div>
                                @endif
                            </div>
                            <button type="button" wire:click="viewOrder({{ $orderVendor?->id }})"
                                class="px-4 py-1.5 border border-gray-300 rounded-lg text-xs font-medium hover:bg-gray-100 transition-colors">
                                Detail Pesanan
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-12 text-center border rounded-xl">
                    <svg class="mx-auto h-16 w-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                    </svg>
                    <p class="text-gray-500 text-lg font-medium mb-1">Belum ada pengiriman untuk dilacak</p>
                    <p class="text-gray-400 text-sm">Pengiriman baru akan muncul setelah pesanan diproses.</p>
                </div>
            @endforelse
        </div>
    </div>

    <div class="mt-6">
        {{ $shipments->links() }}
    </div>
</div>
