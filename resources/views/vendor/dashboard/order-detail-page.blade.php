@php
    $orderVendor = $this->orderVendor;

    $statusColors = [
        'pending' => 'bg-yellow-100 text-yellow-800',
        'processed' => 'bg-blue-100 text-blue-800',
        'shipped' => 'bg-indigo-100 text-indigo-800',
        'completed' => 'bg-green-100 text-green-800',
        'cancelled' => 'bg-red-100 text-red-800',
        'waiting_confirmation' => 'bg-blue-100 text-blue-800',
        'paid' => 'bg-green-100 text-green-800',
        'failed' => 'bg-red-100 text-red-800',
        'success' => 'bg-green-100 text-green-800',
    ];
@endphp

<div>
    @if (!$orderVendor)
        <div class="p-6 text-center">
            <p class="text-gray-500">Pesanan tidak ditemukan.</p>
        </div>
    @else
        <div class="space-y-6">
            {{-- Flash Messages --}}
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

            {{-- Header --}}
            <div class="flex items-start justify-between">
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <a wire:navigate href="{{ route('vendor.dashboard', ['tab' => 'orders']) }}"
                            class="text-sm text-primary hover:underline">&larr; Kembali</a>
                    </div>
                    <h2 class="text-xl font-semibold">Detail Pesanan</h2>
                    <p class="text-sm text-gray-500 mt-1">No. Order: <strong>{{ $orderVendor->order?->order_number }}</strong></p>
                    <p class="text-sm text-gray-500">Tanggal: {{ $orderVendor->created_at?->format('d M Y, H:i') }}</p>
                </div>
                <div class="text-right space-y-2">
                    <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold {{ $statusColors[$orderVendor->status->value] ?? 'bg-gray-100 text-gray-800' }}">
                        {{ $orderVendor->status->getLabel() }}
                    </span>
                </div>
            </div>

            {{-- Customer Info --}}
            <div class="border rounded-lg p-5 space-y-3">
                <h3 class="text-lg font-semibold">Informasi Pembeli</h3>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-gray-500">Nama</p>
                        <p class="font-medium">{{ $orderVendor->order?->user?->name ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Email</p>
                        <p class="font-medium">{{ $orderVendor->order?->user?->email ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">No. HP</p>
                        <p class="font-medium">{{ $orderVendor->order?->user?->phone ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Status Pembayaran</p>
                        @if ($orderVendor->order?->payment)
                            <span class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold {{ $statusColors[$orderVendor->order->payment->status->value] ?? 'bg-gray-100' }}">
                                {{ $orderVendor->order->payment->status->getLabel() }}
                            </span>
                        @else
                            <span class="text-gray-400">-</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Order Items --}}
            <div class="border rounded-lg p-5 space-y-4">
                <h3 class="text-lg font-semibold">Item Pesanan</h3>

                <div class="space-y-4">
                    @foreach ($orderVendor->orderItems as $item)
                        @php
                            $variant = $item->productVariant;
                            $product = $variant?->product;
                            $image = $product?->productImages?->first();
                        @endphp
                        <div class="flex justify-between items-start gap-4 py-3 border-b last:border-b-0">
                            <div class="flex items-start gap-4">
                                <div class="h-16 w-16 bg-gray-100 rounded-lg overflow-hidden shrink-0">
                                    <img src="{{ Storage::url($image?->image ?? 'products/product_placeholder.jpg') }}"
                                        alt="{{ $product?->name }}" class="w-full h-full object-cover">
                                </div>
                                <div class="space-y-1">
                                    <p class="text-xs text-gray-500">{{ $product?->category?->name ?? '' }}</p>
                                    <h4 class="font-semibold text-sm uppercase">{{ $product?->name ?? 'Produk' }}</h4>
                                    @if ($variant?->variant_name)
                                        <span class="inline-block px-2 py-0.5 bg-gray-100 rounded text-xs">{{ $variant->variant_name }}</span>
                                    @endif
                                    <p class="text-xs text-gray-500">x{{ $item->quantity }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-sm">Rp {{ number_format((float) $item->total, 0, ',', '.') }}</p>
                                <p class="text-xs text-gray-500">@ Rp {{ number_format((float) $item->price, 0, ',', '.') }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="border-t pt-3 mt-2 flex justify-between text-sm">
                    <span class="text-gray-600">Subtotal</span>
                    <span class="font-semibold text-lg">Rp {{ number_format((float) $orderVendor->subtotal, 0, ',', '.') }}</span>
                </div>
            </div>

            {{-- Shipment Info --}}
            @if ($orderVendor->shipment)
                <div class="border rounded-lg p-5 space-y-3">
                    <h3 class="text-lg font-semibold">Informasi Pengiriman</h3>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <p class="text-gray-500">Status</p>
                            <span class="inline-block px-2 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$orderVendor->shipment->status->value] ?? 'bg-gray-100' }}">
                                {{ $orderVendor->shipment->status->getLabel() }}
                            </span>
                        </div>
                        @if ($orderVendor->shipment->shipmentCourier)
                        <div>
                            <p class="text-gray-500">Kurir</p>
                            <p class="font-medium">{{ $orderVendor->shipment->shipmentCourier->name }} - {{ $orderVendor->shipment->shipmentCourier->service ?? '' }}</p>
                        </div>
                        @endif
                        @if ($orderVendor->shipment->tracking_number)
                        <div>
                            <p class="text-gray-500">No. Resi</p>
                            <p class="font-medium">{{ $orderVendor->shipment->tracking_number }}</p>
                        </div>
                        @endif
                        @if ($orderVendor->shipment->shipping_cost)
                        <div>
                            <p class="text-gray-500">Ongkir</p>
                            <p class="font-medium">Rp {{ number_format((float) $orderVendor->shipment->shipping_cost, 0, ',', '.') }}</p>
                        </div>
                        @endif
                        @if ($orderVendor->shipment->shipmentAddress)
                        <div class="col-span-2">
                            <p class="text-gray-500">Alamat Tujuan</p>
                            <p class="font-medium">{{ $orderVendor->shipment->shipmentAddress->address }}</p>
                            <p class="text-sm text-gray-500">
                                {{ $orderVendor->shipment->shipmentAddress->district }},
                                {{ $orderVendor->shipment->shipmentAddress->city }},
                                {{ $orderVendor->shipment->shipmentAddress->province }}
                                {{ $orderVendor->shipment->shipmentAddress->postal_code }}
                            </p>
                        </div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Actions --}}
            <div class="border rounded-lg p-5 space-y-4">
                <h3 class="text-lg font-semibold">Aksi</h3>

                @if ($orderVendor->status->value === 'pending')
                    <div class="p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                        <p class="text-sm text-yellow-700 mb-3">Pesanan ini menunggu untuk diproses.</p>
                        <button type="button" wire:click="processOrder"
                            wire:confirm="Proses pesanan ini? Status akan berubah menjadi 'Diproses'."
                            class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:opacity-90">
                            Proses Pesanan
                        </button>
                    </div>
                @elseif ($orderVendor->status->value === 'processed')
                    <div class="p-3 bg-blue-50 border border-blue-200 rounded-lg space-y-3">
                        <p class="text-sm text-blue-700">Pesanan sedang diproses. Masukkan nomor resi untuk mengirim.</p>
                        <div class="flex items-end gap-3">
                            <div class="flex-1">
                                @component('components.form.input', [
                                    'label' => 'Nomor Resi',
                                    'type' => 'text',
                                    'wireModel' => 'tracking_number',
                                    'placeholder' => 'Masukkan nomor resi pengiriman',
                                    'required' => true,
                                ]) @endcomponent
                            </div>
                            <button type="button" wire:click="shipOrder"
                                wire:confirm="Kirim pesanan ini?"
                                class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:opacity-90 mb-1">
                                Kirim Pesanan
                            </button>
                        </div>
                    </div>
                @elseif ($orderVendor->status->value === 'shipped')
                    <div class="p-3 bg-indigo-50 border border-indigo-200 rounded-lg">
                        <p class="text-sm text-indigo-700 mb-3">Pesanan sudah dikirim. Tandai selesai jika sudah sampai.</p>
                        <button type="button" wire:click="completeOrder"
                            wire:confirm="Selesaikan pesanan ini? Dana akan masuk ke wallet Anda."
                            class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:opacity-90">
                            Tandai Selesai
                        </button>
                    </div>
                @elseif ($orderVendor->status->value === 'completed')
                    <div class="p-3 bg-green-50 border border-green-200 rounded-lg">
                        <p class="text-sm text-green-700">
                            <svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Pesanan telah selesai. Dana telah ditambahkan ke wallet Anda.
                        </p>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
