@php
$statusColors = [
'pending' => 'bg-yellow-100 text-yellow-800',
'waiting_confirmation' => 'bg-blue-100 text-blue-800',
'paid' => 'bg-green-100 text-green-800',
'shipped' => 'bg-indigo-100 text-indigo-800',
'completed' => 'bg-green-100 text-green-800',
'cancelled' => 'bg-red-100 text-red-800',
'failed' => 'bg-red-100 text-red-800',
'success' => 'bg-green-100 text-green-800',
];
@endphp

<div class="p-6 rounded-xl">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div class="mb-6">
            <h2 class="text-xl font-semibold">Riwayat Pesanan</h2>
            <p class="text-sm text-gray-500">Daftar pesanan yang pernah Anda buat. Klik pesanan untuk melihat detail.
            </p>
        </div>
        <div class="flex gap-3 flex-wrap">
            @component('components.form.select', [
            'label' => '',
            'wireModel' => 'selectedOrderStatus',
            'default' => [
            'label' => 'Semua Status Order',
            'value' => '',
            ],
            'options' => $orderStatusOptions,
            ]) @endcomponent
            @component('components.form.select', [
            'label' => '',
            'wireModel' => 'selectedPaymentStatus',
            'default' => [
            'label' => 'Semua Status Pembayaran',
            'value' => '',
            ],
            'options' => $paymentStatusOptions,
            ]) @endcomponent
        </div>
    </div>

    @if (session()->has('success'))
    <div class="p-3 rounded-lg border border-green-200 bg-green-50 text-green-700 text-sm mb-4">
        {{ session('success') }}
    </div>
    @endif

    <div class="overflow-x-auto" wire:loading.class="opacity-50 pointer-events-none">
        <div class="space-y-4">
            @forelse ($orders as $order)
            <div class="border rounded-lg overflow-hidden">
                {{-- Order Header --}}
                <div class="bg-gray-50 px-5 py-3 flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-4">
                        <div>
                            <span class="text-sm font-semibold">{{ $order->order_number }}</span>
                            <span
                                class="text-xs text-gray-500 ml-2">{{ optional($order->created_at)->format('d M Y, H:i') }}</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span
                            class="inline-block px-2.5 py-1 rounded-full text-xs font-semibold {{ $statusColors[$order->status->value] ?? 'bg-gray-100' }}">
                            {{ $order->status->getLabel() }}
                        </span>
                        <span
                            class="inline-block px-2.5 py-1 rounded-full text-xs font-semibold {{ $statusColors[$order->payment_status->value] ?? 'bg-gray-100' }}">
                            {{ $order->payment_status->getLabel() }}
                        </span>
                    </div>
                </div>

                {{-- Order Items --}}
                <div class="px-5 py-3">
                    @foreach ($order->orderVendors as $orderVendor)
                    @foreach ($orderVendor->orderItems as $item)
                    @php
                    $variant = $item->productVariant;
                    $product = $variant?->product;
                    $image = $product?->productImages?->first();
                    @endphp
                    <div class="flex justify-between items-center gap-4 py-2 {{ !$loop->last ? 'border-b' : '' }}">
                        <div class="flex items-center gap-3">
                            <div class="h-14 w-14 bg-gray-100 rounded-lg overflow-hidden shrink-0">
                                <img src="{{ Storage::url($image?->image ?? 'products/product_placeholder.jpg') }}"
                                    alt="Produk" class="w-full h-full object-cover">
                            </div>
                            <div class="space-y-0.5">
                                <p class="text-xs text-gray-500">{{ $product?->category?->name ?? '' }}</p>
                                <h4 class="text-sm font-semibold uppercase">{{ $product?->name ?? 'Produk' }}</h4>
                                <div class="flex items-center gap-2">
                                    @if ($variant?->variant_name)
                                    <span
                                        class="inline-block px-2 py-0.5 bg-primary/10 text-primary text-xs rounded">{{ $variant->variant_name }}</span>
                                    @endif
                                    <span class="text-xs text-gray-500">x{{ $item->quantity }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-semibold">Rp {{ number_format((float) $item->total, 0, ',', '.') }}
                            </p>
                        </div>
                    </div>
                    @endforeach
                    @endforeach
                </div>

                {{-- Order Footer --}}
                <div class="bg-gray-50 px-5 py-3 flex flex-wrap items-center justify-between gap-3 border-t">
                    <div class="flex items-center gap-3">
                        {{-- Upload proof button for pending payments --}}
                        @if ($order->payment && in_array($order->payment->status->value, ['pending', 'failed']))
                        <a href="{{ route('payment.page', ['orderId' => $order->id]) }}"
                            class="px-3 py-1.5 bg-blue-600 text-white rounded-lg text-xs font-semibold hover:bg-blue-700 transition-colors inline-flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                            </svg>
                            Bayar Sekarang
                        </a>
                        @elseif ($order->payment && $order->payment->status->value === 'waiting_confirmation')
                        <span class="text-xs text-blue-600 font-medium">
                            <svg class="inline w-4 h-4 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Menunggu konfirmasi admin
                        </span>
                        @endif
                        <button type="button" wire:click="viewOrder({{ $order->id }})"
                            class="px-3 py-1.5 border border-gray-300 rounded-lg text-xs font-medium hover:bg-gray-100">
                            Lihat Detail
                        </button>
                    </div>
                    <div class="text-right">
                        <span class="text-xs text-gray-500">Total Pesanan</span>
                        <p class="text-base font-semibold">Rp
                            {{ number_format((int) $order->total_amount, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>
            @empty
            <div class="p-8 text-center border rounded-lg">
                <svg class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <p class="text-gray-500">Belum ada riwayat pesanan.</p>
                <a href="{{ route('produk.cari') }}"
                    class="inline-block mt-3 text-primary text-sm hover:underline">Mulai Belanja</a>
            </div>
            @endforelse
        </div>
    </div>

    <div class="mt-6">
        {{ $orders->links() }}
    </div>
</div>
