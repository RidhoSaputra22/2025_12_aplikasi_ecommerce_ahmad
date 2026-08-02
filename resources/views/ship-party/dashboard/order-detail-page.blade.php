@php
$orderVendor = $this->orderVendor;

$statusColors = [
    'pending' => 'bg-yellow-100 text-yellow-800',
    'processed' => 'bg-blue-100 text-blue-800',
    'shipped' => 'bg-indigo-100 text-indigo-800',
    'delivered' => 'bg-teal-100 text-teal-800',
    'completed' => 'bg-green-100 text-green-800',
    'success' => 'bg-green-100 text-green-800',
];
@endphp

<div>
    @if (! $orderVendor)
        <div class="p-6 text-center">
            <p class="text-gray-500">Data pengiriman tidak ditemukan.</p>
        </div>
    @else
        <div class="space-y-6">
            @if (session()->has('success'))
                <div class="p-3 rounded-lg border border-green-200 bg-green-50 text-green-700 text-sm">
                    {{ session('success') }}
                </div>
            @endif
            @if (session()->has('error'))
                <div class="p-3 rounded-lg border border-red-200 bg-red-50 text-red-700 text-sm">
                    {{ session('error') }}
                </div>
            @endif

            <div class="flex items-start justify-between">
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <a wire:navigate href="{{ route('ship-party.dashboard', ['tab' => 'shipments']) }}"
                            class="text-sm text-primary hover:underline">&larr; Kembali</a>
                    </div>
                    <h2 class="text-xl font-semibold">Detail Pengiriman</h2>
                    <p class="text-sm text-gray-500 mt-1">
                        No. Order: <strong>{{ $orderVendor->order?->order_number }}</strong>
                    </p>
                    <p class="text-sm text-gray-500">Vendor: {{ $orderVendor->vendor?->store_name ?? '-' }}</p>
                </div>
                <div class="text-right space-y-2">
                    <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold {{ $statusColors[$orderVendor->status->value] ?? 'bg-gray-100 text-gray-800' }}">
                        {{ $orderVendor->status->getLabel() }}
                    </span>
                </div>
            </div>

            <div class="border rounded-lg p-5 space-y-3">
                <h3 class="text-lg font-semibold">Informasi Pengiriman</h3>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-gray-500">Penerima</p>
                        <p class="font-medium">{{ $orderVendor->order?->user?->name ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Ekspedisi</p>
                        <p class="font-medium">{{ $orderVendor->shipment?->shipmentCourier?->name ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">No. Resi</p>
                        <p class="font-medium">{{ $orderVendor->shipment?->tracking_number ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Status Shipment</p>
                        <span class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold {{ $statusColors[$orderVendor->shipment?->status?->value] ?? 'bg-gray-100 text-gray-800' }}">
                            {{ $orderVendor->shipment?->status?->getLabel() ?? '-' }}
                        </span>
                    </div>
                    <div class="col-span-2">
                        <p class="text-gray-500">Alamat Tujuan</p>
                        <p class="font-medium">{{ $orderVendor->shipment?->shipmentAddress?->address ?? '-' }}</p>
                        <p class="text-sm text-gray-500">
                            {{ $orderVendor->shipment?->shipmentAddress?->district ?? '-' }},
                            {{ $orderVendor->shipment?->shipmentAddress?->city ?? '-' }},
                            {{ $orderVendor->shipment?->shipmentAddress?->province ?? '-' }}
                            {{ $orderVendor->shipment?->shipmentAddress?->postal_code ?? '' }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="border rounded-lg p-5 space-y-4">
                <h3 class="text-lg font-semibold">Aksi Pihak Kapal</h3>

                @if ($orderVendor->status->value === 'processed')
                    <div class="p-3 bg-blue-50 border border-blue-200 rounded-lg space-y-3">
                        <p class="text-sm text-blue-700">Pengiriman siap diproses oleh pihak kapal.</p>
                        <div class="flex items-end gap-3">
                            <div class="flex-1">
                                @component('components.form.input', [
                                    'label' => 'Nomor Resi',
                                    'type' => 'text',
                                    'wireModel' => 'tracking_number',
                                    'placeholder' => 'Kosongkan untuk generate otomatis',
                                    'required' => false,
                                ]) @endcomponent
                            </div>
                            <button type="button" wire:click="shipOrder" wire:confirm="Kirim pesanan ini sekarang?"
                                class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:opacity-90 mb-1">
                                Proses & Kirim
                            </button>
                        </div>
                    </div>
                @elseif ($orderVendor->status->value === 'shipped')
                    <div class="p-3 bg-indigo-50 border border-indigo-200 rounded-lg">
                        <p class="text-sm text-indigo-700 mb-3">Paket sedang dalam perjalanan. Konfirmasi saat barang telah tiba.</p>
                        <button type="button" wire:click="confirmDelivery"
                            wire:confirm="Konfirmasi bahwa paket ini sudah tiba di tujuan?"
                            class="bg-teal-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:opacity-90">
                            Update Status Tiba
                        </button>
                    </div>
                @elseif ($orderVendor->status->value === 'delivered')
                    <div class="p-3 bg-teal-50 border border-teal-200 rounded-lg text-sm text-teal-700">
                        Paket telah ditandai tiba. Menunggu konfirmasi penerimaan dari pembeli.
                    </div>
                @elseif ($orderVendor->status->value === 'completed')
                    <div class="p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">
                        Pengiriman telah selesai sepenuhnya.
                    </div>
                @else
                    <div class="p-3 bg-yellow-50 border border-yellow-200 rounded-lg text-sm text-yellow-700">
                        Pengiriman ini masih menunggu vendor memproses pesanan.
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
