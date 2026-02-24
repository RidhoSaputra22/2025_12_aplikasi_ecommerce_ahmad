@php
$order = $order ?? null;
$payment = $order?->payment;

$statusColors = [
'pending' => 'bg-yellow-100 text-yellow-800 border-yellow-300',
'waiting_confirmation' => 'bg-blue-100 text-blue-800 border-blue-300',
'paid' => 'bg-green-100 text-green-800 border-green-300',
'success' => 'bg-green-100 text-green-800 border-green-300',
'failed' => 'bg-red-100 text-red-800 border-red-300',
'cancelled' => 'bg-red-100 text-red-800 border-red-300',
];
@endphp

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="mb-6">
            <a href="{{ route('user.dashboard', ['tab' => 'history']) }}"
                class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Kembali ke Riwayat Pesanan
            </a>
            <h1 class="text-2xl font-bold text-gray-900 mt-2">Pembayaran</h1>
            @if ($order)
            <p class="text-sm text-gray-500 mt-1">Order: <strong>{{ $order->order_number }}</strong></p>
            @endif
        </div>

        @if (!$order)
        <div class="bg-white rounded-xl shadow-sm border p-8 text-center">
            <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p class="text-gray-500">Pesanan tidak ditemukan.</p>
            <a href="{{ route('user.dashboard', ['tab' => 'history']) }}"
                class="mt-4 inline-block text-primary hover:underline">
                Lihat riwayat pesanan
            </a>
        </div>
        @else
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Order Summary --}}
            <div class="lg:col-span-2 space-y-4">
                {{-- Flash Messages --}}
                @if (session()->has('success'))
                <div class="p-4 rounded-xl border border-green-200 bg-green-50 text-green-700 text-sm">
                    {{ session('success') }}
                </div>
                @endif
                @if (session()->has('error'))
                <div class="p-4 rounded-xl border border-red-200 bg-red-50 text-red-700 text-sm">
                    {{ session('error') }}
                </div>
                @endif
                @if ($errorMessage)
                <div class="p-4 rounded-xl border border-red-200 bg-red-50 text-red-700 text-sm">
                    {{ $errorMessage }}
                </div>
                @endif

                {{-- Status Banner --}}
                @if ($payment && $payment->status->value === 'success')
                <div class="p-4 rounded-xl border border-green-200 bg-green-50 flex items-center gap-3">
                    <svg class="w-6 h-6 text-green-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div>
                        <p class="font-semibold text-green-800">Pembayaran Berhasil!</p>
                        <p class="text-sm text-green-600">Pesanan Anda sedang diproses oleh vendor.</p>
                    </div>
                </div>
                @endif

                {{-- Items --}}
                @foreach ($order->orderVendors as $orderVendor)
                <div class="bg-white rounded-xl shadow-sm border p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-semibold text-gray-900">{{ $orderVendor->vendor?->store_name ?? 'Vendor' }}</h3>
                        <span
                            class="inline-block px-2.5 py-1 rounded-full text-xs font-medium border {{ $statusColors[$orderVendor->status->value] ?? 'bg-gray-100 text-gray-800' }}">
                            {{ $orderVendor->status->getLabel() }}
                        </span>
                    </div>

                    <div class="divide-y">
                        @foreach ($orderVendor->orderItems as $item)
                        @php
                        $variant = $item->productVariant;
                        $product = $variant?->product;
                        $image = $product?->productImages?->first();
                        @endphp
                        <div class="flex gap-4 py-3">
                            <div class="h-14 w-14 bg-gray-100 rounded-lg overflow-hidden shrink-0">
                                <img src="{{ Storage::url($image?->image ?? 'products/product_placeholder.jpg') }}"
                                    alt="{{ $product?->name }}" class="w-full h-full object-cover">
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-sm text-gray-900 truncate">{{ $product?->name ?? 'Produk' }}
                                </p>
                                @if ($variant?->variant_name)
                                <span
                                    class="inline-block mt-0.5 px-2 py-0.5 bg-gray-100 rounded text-xs text-gray-600">{{ $variant->variant_name }}</span>
                                @endif
                                <p class="text-xs text-gray-500 mt-1">{{ $item->quantity }}x @ Rp
                                    {{ number_format((float) $item->price, 0, ',', '.') }}</p>
                            </div>
                            <div class="text-right shrink-0">
                                <p class="font-semibold text-sm">Rp
                                    {{ number_format((float) $item->total, 0, ',', '.') }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <div class="mt-3 pt-3 border-t flex justify-between text-sm">
                        <span class="text-gray-600">Subtotal</span>
                        <span class="font-semibold">Rp
                            {{ number_format((float) $orderVendor->subtotal, 0, ',', '.') }}</span>
                    </div>

                    @if ($orderVendor->shipment && (float) $orderVendor->shipment->shipping_cost > 0)
                    <div class="flex justify-between text-sm mt-1">
                        <span class="text-gray-600">
                            Ongkir
                            @if ($orderVendor->shipment->shipmentCourier)
                            <span class="text-xs text-gray-400">({{ $orderVendor->shipment->shipmentCourier->name }}
                                {{ $orderVendor->shipment->shipmentCourier->service }})</span>
                            @endif
                        </span>
                        <span class="font-semibold">Rp
                            {{ number_format((float) $orderVendor->shipment->shipping_cost, 0, ',', '.') }}</span>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>

            {{-- Payment Sidebar --}}
            <div class="lg:col-span-1">
                <div class="sticky top-20 space-y-4">

                    {{-- Payment Summary --}}
                    <div class="bg-white rounded-xl shadow-sm border p-5">
                        <h3 class="font-semibold text-gray-900 mb-4">Ringkasan Pembayaran</h3>

                        @php
                        $productSubtotal = $order->orderVendors->sum(fn($ov) => (float) $ov->subtotal);
                        $totalOngkir = $order->orderVendors->sum(fn($ov) => (float) ($ov->shipment?->shipping_cost ??
                        0));
                        @endphp

                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Subtotal Produk</span>
                                <span>Rp {{ number_format($productSubtotal, 0, ',', '.') }}</span>
                            </div>

                            @if ($totalOngkir > 0)
                            <div class="flex justify-between">
                                <span class="text-gray-600">Ongkir</span>
                                <span>Rp {{ number_format($totalOngkir, 0, ',', '.') }}</span>
                            </div>

                            {{-- Detail ongkir per vendor --}}
                            @foreach ($order->orderVendors as $ov)
                            @if ($ov->shipment && (float) $ov->shipment->shipping_cost > 0)
                            <div class="flex justify-between text-xs text-gray-400 pl-3">
                                <span>{{ $ov->vendor?->store_name ?? 'Vendor' }}
                                    @if ($ov->shipment->shipmentCourier)
                                    ({{ $ov->shipment->shipmentCourier->name }}
                                    {{ $ov->shipment->shipmentCourier->service }})
                                    @endif
                                </span>
                                <span>Rp {{ number_format((float) $ov->shipment->shipping_cost, 0, ',', '.') }}</span>
                            </div>
                            @endif
                            @endforeach
                            @endif

                            <div class="pt-3 mt-3 border-t flex justify-between text-base font-bold">
                                <span>Total</span>
                                <span class="text-primary">Rp
                                    {{ number_format((float) $order->total_amount, 0, ',', '.') }}</span>
                            </div>
                        </div>

                        @if ($payment)
                        <div class="mt-4 pt-4 border-t">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-600">Status</span>
                                <span
                                    class="inline-block px-2.5 py-1 rounded-full text-xs font-medium border {{ $statusColors[$payment->status->value] ?? 'bg-gray-100' }}">
                                    {{ $payment->status->getLabel() }}
                                </span>
                            </div>

                            @if ($payment->midtrans_payment_type)
                            <div class="flex items-center justify-between text-sm mt-2">
                                <span class="text-gray-600">Metode</span>
                                <span
                                    class="font-medium capitalize">{{ str_replace('_', ' ', $payment->midtrans_payment_type) }}</span>
                            </div>
                            @endif

                            @if ($payment->midtrans_va_number)
                            <div class="mt-3 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                <p class="text-xs text-blue-600 font-semibold uppercase">
                                    {{ $payment->midtrans_bank ?? 'Virtual Account' }}</p>
                                <p class="text-lg font-bold text-gray-800 mt-1 tracking-wider font-mono">
                                    {{ $payment->midtrans_va_number }}</p>
                            </div>
                            @endif

                            @if ($payment->expired_at && $payment->status->value === 'pending')
                            <div class="mt-3 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                                <p class="text-xs text-yellow-700">
                                    <svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Batas pembayaran: <strong>{{ $payment->expired_at->format('d M Y, H:i') }}
                                        WIB</strong>
                                </p>
                            </div>
                            @endif
                        </div>
                        @endif
                    </div>

                    {{-- Payment Actions --}}
                    @php
                    $hasPendingMidtrans = $payment
                    && $payment->snap_token
                    && $payment->payment_gateway === 'midtrans'
                    && $payment->status->value === 'pending'
                    && $payment->expired_at
                    && $payment->expired_at->isFuture();
                    @endphp

                    @if ($hasPendingMidtrans)
                    {{-- Midtrans payment sudah di-initiate, menunggu pembayaran --}}
                    <div class="bg-white rounded-xl shadow-sm border p-5 space-y-4">
                        <div class="text-center">
                            <div
                                class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-yellow-100 mb-3">
                                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <h3 class="font-semibold text-gray-900">Menunggu Pembayaran</h3>
                            <p class="text-xs text-gray-500 mt-1">Pembayaran Anda sedang diproses. Silakan selesaikan
                                pembayaran sesuai instruksi yang diberikan.</p>
                        </div>

                        {{-- Detail pembayaran Midtrans --}}
                        @if ($payment->midtrans_payment_type)
                        <div class="text-sm text-center">
                            <span class="text-gray-500">Metode:</span>
                            <span
                                class="font-medium capitalize">{{ str_replace('_', ' ', $payment->midtrans_payment_type) }}</span>
                        </div>
                        @endif

                        @if ($payment->midtrans_va_number)
                        <div class="p-3 bg-blue-50 border border-blue-200 rounded-lg text-center">
                            <p class="text-xs text-blue-600 font-semibold uppercase">
                                {{ $payment->midtrans_bank ?? 'Virtual Account' }}</p>
                            <p class="text-xl font-bold text-gray-800 mt-1 tracking-wider font-mono">
                                {{ $payment->midtrans_va_number }}</p>
                            <p class="text-xs text-gray-500 mt-1">Transfer sesuai nominal ke nomor VA di atas.</p>
                        </div>
                        @endif

                        @if ($payment->midtrans_store && $payment->midtrans_payment_code)
                        <div class="p-3 bg-orange-50 border border-orange-200 rounded-lg text-center">
                            <p class="text-xs text-orange-600 font-semibold uppercase">{{ $payment->midtrans_store }}
                            </p>
                            <p class="text-xl font-bold text-gray-800 mt-1 tracking-wider font-mono">
                                {{ $payment->midtrans_payment_code }}</p>
                            <p class="text-xs text-gray-500 mt-1">Tunjukkan kode ini ke kasir.</p>
                        </div>
                        @endif

                        @if ($payment->midtrans_qr_url)
                        <div class="text-center">
                            <img src="{{ $payment->midtrans_qr_url }}" alt="QR Code"
                                class="mx-auto max-w-[200px] rounded-lg border">
                            <p class="text-xs text-gray-500 mt-2">Scan QR code untuk membayar.</p>
                        </div>
                        @endif

                        @if ($payment->expired_at)
                        <div class="p-3 bg-yellow-50 border border-yellow-200 rounded-lg text-center">
                            <p class="text-xs text-yellow-700">
                                <svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Selesaikan sebelum: <strong>{{ $payment->expired_at->format('d M Y, H:i') }}
                                    WIB</strong>
                            </p>
                        </div>
                        @endif

                        {{-- Tombol lanjutkan pembayaran --}}
                        <button type="button" wire:click="payWithMidtrans" wire:loading.attr="disabled"
                            wire:target="payWithMidtrans"
                            class="block w-full bg-blue-600 hover:bg-blue-700 text-white py-2.5 px-4 rounded-lg text-sm font-semibold text-center transition-colors disabled:opacity-50">
                            <span wire:loading.remove wire:target="payWithMidtrans">Lanjutkan Pembayaran</span>
                            <span wire:loading wire:target="payWithMidtrans">Memuat...</span>
                        </button>

                        {{-- Link ke Snap redirect sebagai alternatif --}}
                        @if ($payment->snap_redirect_url)
                        <a href="{{ $payment->snap_redirect_url }}" target="_blank"
                            class="block w-full text-center bg-gray-100 hover:bg-gray-200 text-gray-700 py-2.5 px-4 rounded-lg text-sm font-medium transition-colors">
                            Lihat Instruksi Pembayaran
                        </a>
                        @endif

                        <a href="{{ route('user.dashboard', ['tab' => 'order-detail', 'order_id' => $order->id]) }}"
                            class="block w-full text-center text-sm text-gray-500 hover:text-gray-700 py-2">
                            Lihat Detail Pesanan
                        </a>
                    </div>

                    @elseif ($payment && in_array($payment->status->value, ['pending', 'failed']))
                    {{-- Belum ada pembayaran Midtrans atau sudah expired/failed - tampilkan tombol bayar --}}
                    <div class="bg-white rounded-xl shadow-sm border p-5 space-y-3">
                        <h3 class="font-semibold text-gray-900 mb-2">Pilih Metode Pembayaran</h3>

                        {{-- Midtrans Button --}}
                        <button type="button" wire:click="payWithMidtrans" wire:loading.attr="disabled"
                            wire:target="payWithMidtrans"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white py-3 px-4 rounded-lg text-sm font-semibold transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                            <span wire:loading.remove wire:target="payWithMidtrans">
                                <svg class="inline w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                </svg>
                                Bayar dengan Midtrans
                            </span>
                            <span wire:loading wire:target="payWithMidtrans" class="flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                Memproses...
                            </span>
                        </button>

                        <p class="text-xs text-gray-500 text-center">Transfer Bank, E-Wallet, QRIS, Kartu Kredit</p>
                    </div>
                    @elseif ($payment && $payment->status->value === 'waiting_confirmation')
                    <div class="bg-blue-50 rounded-xl border border-blue-200 p-5 text-center">
                        <svg class="w-10 h-10 text-blue-500 mx-auto mb-2" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-sm font-semibold text-blue-800">Menunggu Konfirmasi Admin</p>
                        <p class="text-xs text-blue-600 mt-1">Bukti pembayaran Anda sedang direview.</p>
                    </div>
                    @elseif ($payment && $payment->status->value === 'success')
                    <a href="{{ route('user.dashboard', ['tab' => 'order-detail', 'order_id' => $order->id]) }}"
                        class="block w-full bg-primary hover:opacity-90 text-white py-3 px-4 rounded-lg text-sm font-semibold text-center transition-opacity">
                        Lihat Detail Pesanan
                    </a>
                    @endif

                </div>
            </div>
        </div>
        @endif
    </div>

    {{-- Midtrans Snap JS: always loaded so popup can be opened at any time --}}
    @push('scripts')
    <script src="{{ config('midtrans.snap_url') }}" data-client-key="{{ config('midtrans.client_key') }}"></script>
    @endpush

    <script>
    function openSnapPopup(token) {
        if (typeof snap === 'undefined') {
            // Snap belum siap, coba ulang sebentar
            setTimeout(function() {
                openSnapPopup(token);
            }, 300);
            return;
        }
        snap.pay(token, {
            onSuccess: function(result) {
                window.location.href = '{{ route("payment.finish") }}?order_id=' + result.order_id +
                    '&transaction_status=' + result.transaction_status;
            },
            onPending: function(result) {
                window.location.href = '{{ route("payment.finish") }}?order_id=' + result.order_id +
                    '&transaction_status=pending';
            },
            onError: function(result) {
                // Treat error sebagai pending — tetap di halaman
                @this.syncPaymentStatus();
            },
            onClose: function() {
                // User menutup popup — tetap di halaman, sync status
                @this.syncPaymentStatus();
            }
        });
    }

    document.addEventListener('livewire:init', () => {
        Livewire.on('open-snap', (data) => {
            if (data && data.token) {
                openSnapPopup(data.token);
            }
        });
    });
    </script>
</div>