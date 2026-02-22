@php
    $order = $this->order;

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

<div>
    @if (!$order)
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
                    <h2 class="text-xl font-semibold">Detail Pesanan</h2>
                    <p class="text-sm text-gray-500 mt-1">No. Order: <strong>{{ $order->order_number }}</strong></p>
                    <p class="text-sm text-gray-500">Tanggal: {{ $order->created_at->format('d M Y, H:i') }}</p>
                </div>
                <div class="text-right space-y-2">
                    <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold {{ $statusColors[$order->status->value] ?? 'bg-gray-100 text-gray-800' }}">
                        {{ $order->status->getLabel() }}
                    </span>
                    <br>
                    <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold {{ $statusColors[$order->payment_status->value] ?? 'bg-gray-100 text-gray-800' }}">
                        {{ $order->payment_status->getLabel() }}
                    </span>
                </div>
            </div>

            {{-- Payment Section --}}
            <div class="border rounded-lg p-5 space-y-4">
                <h3 class="text-lg font-semibold">Informasi Pembayaran</h3>

                @if ($order->payment)
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <p class="text-gray-500">Metode Pembayaran</p>
                            <p class="font-medium capitalize">{{ $order->payment->payment_method ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500">Total Pembayaran</p>
                            <p class="font-semibold text-lg">Rp {{ number_format((float) $order->payment->amount, 0, ',', '.') }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500">Status Pembayaran</p>
                            <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold {{ $statusColors[$order->payment->status->value] ?? 'bg-gray-100' }}">
                                {{ $order->payment->status->getLabel() }}
                            </span>
                        </div>
                        @if ($order->payment->paid_at)
                        <div>
                            <p class="text-gray-500">Dibayar Pada</p>
                            <p class="font-medium">{{ $order->payment->paid_at->format('d M Y, H:i') }}</p>
                        </div>
                        @endif
                        @if ($order->payment->confirmed_at)
                        <div>
                            <p class="text-gray-500">Dikonfirmasi Pada</p>
                            <p class="font-medium">{{ $order->payment->confirmed_at->format('d M Y, H:i') }}</p>
                        </div>
                        @endif
                    </div>

                    {{-- Admin Bank Account Info - Show when payment is pending or failed --}}
                    @if (in_array($order->payment->status->value, ['pending', 'failed']))
                        @php
                            $adminBankAccounts = \App\Models\AdminBankAccount::active()->get();
                        @endphp
                        @if ($adminBankAccounts->isNotEmpty())
                            <div class="border-t pt-4 mt-4">
                                <h4 class="text-sm font-semibold mb-3">
                                    <svg class="inline w-4 h-4 mr-1 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                    </svg>
                                    Rekening Tujuan Pembayaran
                                </h4>
                                <p class="text-xs text-gray-500 mb-3">Transfer ke salah satu rekening berikut, lalu upload bukti pembayaran.</p>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    @foreach ($adminBankAccounts as $bankAccount)
                                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                            <p class="text-xs text-blue-600 font-semibold uppercase">{{ $bankAccount->bank_name }}</p>
                                            <p class="text-lg font-bold text-gray-800 mt-1 tracking-wider">{{ $bankAccount->account_number }}</p>
                                            <p class="text-sm text-gray-600">a.n. {{ $bankAccount->account_holder }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endif

                    {{-- Payment Proof --}}
                    <div class="border-t pt-4 mt-4">
                        <h4 class="text-sm font-semibold mb-3">Bukti Pembayaran</h4>

                        @if ($order->payment->payment_proof)
                            <div class="space-y-3">
                                <img src="{{ Storage::url($order->payment->payment_proof) }}" alt="Bukti Pembayaran"
                                    class="max-h-64 rounded-lg border cursor-pointer"
                                    onclick="window.open('{{ Storage::url($order->payment->payment_proof) }}', '_blank')">
                                <p class="text-xs text-gray-500">Klik gambar untuk memperbesar</p>

                                @if ($order->payment->status->value === 'waiting_confirmation')
                                    <div class="p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                        <p class="text-sm text-blue-700">
                                            <svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            Bukti pembayaran telah diunggah. Menunggu konfirmasi dari admin.
                                        </p>
                                    </div>
                                @endif
                            </div>
                        @else
                            <p class="text-sm text-gray-500 mb-3">Belum ada bukti pembayaran.</p>
                        @endif

                        {{-- Upload Button - only show for pending or failed --}}
                        @if (in_array($order->payment->status->value, ['pending', 'failed']))
                            <div class="mt-4 space-y-3">
                                {{-- Tombol Bayar dengan Midtrans --}}
                                <a href="{{ route('payment.page', ['orderId' => $order->id]) }}"
                                    class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-colors">
                                    <svg class="inline w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                    </svg>
                                    Bayar Sekarang
                                </a>

                                <div class="relative">
                                    <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-gray-200"></div></div>
                                    <div class="relative flex justify-center text-xs"><span class="bg-white px-3 text-gray-400">atau</span></div>
                                </div>

                                {{-- Tombol Upload Bukti Manual --}}
                                <button type="button" wire:click="openPaymentProofModal"
                                    class="inline-flex items-center bg-white border-2 border-gray-200 hover:border-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm font-semibold transition-colors">
                                    <svg class="inline w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    Upload Bukti Transfer Manual
                                </button>
                                <p class="text-xs text-gray-500">Transfer ke rekening admin, lalu upload bukti pembayaran.</p>
                            </div>
                        @endif
                    </div>

                    {{-- Payment Transaction Reference --}}
                    @if ($order->payment->transaction_reference)
                        <div class="border-t pt-4 mt-4">
                            <h4 class="text-sm font-semibold mb-2">Referensi Transaksi</h4>
                            <p class="text-sm font-mono bg-gray-50 px-3 py-2 rounded">{{ $order->payment->transaction_reference }}</p>
                        </div>
                    @endif

                    {{-- Midtrans Payment Details --}}
                    @if ($order->payment->isMidtrans())
                        <div class="border-t pt-4 mt-4">
                            <h4 class="text-sm font-semibold mb-3">Detail Pembayaran Midtrans</h4>
                            <div class="grid grid-cols-2 gap-3 text-sm">
                                @if ($order->payment->midtrans_payment_type)
                                    <div>
                                        <p class="text-gray-500">Tipe Pembayaran</p>
                                        <p class="font-medium capitalize">{{ str_replace('_', ' ', $order->payment->midtrans_payment_type) }}</p>
                                    </div>
                                @endif

                                @if ($order->payment->midtrans_bank)
                                    <div>
                                        <p class="text-gray-500">Bank</p>
                                        <p class="font-medium uppercase">{{ $order->payment->midtrans_bank }}</p>
                                    </div>
                                @endif
                            </div>

                            @if ($order->payment->midtrans_va_number)
                                <div class="mt-3 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                    <p class="text-xs text-blue-600 font-semibold uppercase">{{ $order->payment->midtrans_bank ?? 'Virtual Account' }}</p>
                                    <p class="text-lg font-bold text-gray-800 mt-1 tracking-wider font-mono">{{ $order->payment->midtrans_va_number }}</p>
                                    <p class="text-xs text-gray-500 mt-1">Gunakan nomor ini untuk transfer via ATM/Mobile Banking.</p>
                                </div>
                            @endif

                            @if ($order->payment->midtrans_store && $order->payment->midtrans_payment_code)
                                <div class="mt-3 p-3 bg-orange-50 border border-orange-200 rounded-lg">
                                    <p class="text-xs text-orange-600 font-semibold uppercase">{{ $order->payment->midtrans_store }}</p>
                                    <p class="text-lg font-bold text-gray-800 mt-1 tracking-wider font-mono">{{ $order->payment->midtrans_payment_code }}</p>
                                    <p class="text-xs text-gray-500 mt-1">Tunjukkan kode ini ke kasir.</p>
                                </div>
                            @endif

                            @if ($order->payment->expired_at && $order->payment->status->value === 'pending')
                                <div class="mt-3 p-3 bg-yellow-50 border border-yellow-200 rounded-lg text-sm text-yellow-700">
                                    <svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Batas pembayaran: <strong>{{ $order->payment->expired_at->format('d M Y, H:i') }} WIB</strong>
                                </div>
                            @endif
                        </div>
                    @endif
                @else
                    <p class="text-gray-500 text-sm">Data pembayaran belum tersedia.</p>
                @endif
            </div>

            {{-- Order Items --}}
            @foreach ($order->orderVendors as $orderVendor)
                <div class="border rounded-lg p-5 space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold">
                            {{ $orderVendor->vendor?->store_name ?? 'Vendor' }}
                        </h3>
                        <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold {{ $statusColors[$orderVendor->status->value] ?? 'bg-gray-100' }}">
                            {{ $orderVendor->status->getLabel() }}
                        </span>
                    </div>

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

                    {{-- Shipment info --}}
                    @if ($orderVendor->shipment)
                        <div class="border-t pt-4 mt-2">
                            <h4 class="text-sm font-semibold mb-2">Pengiriman</h4>
                            <div class="grid grid-cols-2 gap-3 text-sm">
                                <div>
                                    <p class="text-gray-500">Status</p>
                                    <span class="inline-block px-2 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$orderVendor->shipment->status->value] ?? 'bg-gray-100' }}">
                                        {{ $orderVendor->shipment->status->getLabel() }}
                                    </span>
                                </div>
                                @if ($orderVendor->shipment->shipmentCourier)
                                <div>
                                    <p class="text-gray-500">Kurir</p>
                                    <p class="font-medium">{{ $orderVendor->shipment->shipmentCourier->name }} - {{ $orderVendor->shipment->shipmentCourier->service }}</p>
                                </div>
                                @endif
                                @if ($orderVendor->shipment->tracking_number)
                                <div>
                                    <p class="text-gray-500">No. Resi</p>
                                    <p class="font-medium">{{ $orderVendor->shipment->tracking_number }}</p>
                                </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    <div class="border-t pt-3 mt-2 flex justify-between text-sm">
                        <span class="text-gray-600">Subtotal Vendor</span>
                        <span class="font-semibold">Rp {{ number_format((float) $orderVendor->subtotal, 0, ',', '.') }}</span>
                    </div>
                </div>
            @endforeach

            {{-- Total --}}
            <div class="border rounded-lg p-5">
                <div class="flex justify-between text-lg font-semibold">
                    <span>Total Pesanan</span>
                    <span>Rp {{ number_format((float) $order->total_amount, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    @endif
</div>
