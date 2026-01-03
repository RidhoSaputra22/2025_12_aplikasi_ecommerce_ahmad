<div class="p-6 rounded-xl ">
    <div class="flex">
        <div class="mb-6 flex-1">
            <h2 class="text-xl font-semibold">Riwayat Pesanan</h2>
            <p class="text-sm text-gray-500">Daftar pesanan yang pernah Anda buat.</p>
        </div>
        <div class="flex gap-5">
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

    <div class="overflow-x-auto" wire:loading.class="opacity-50 pointer-events-none">
        {{ $orders->links() }}
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left border-b">
                    <th class="py-3 pr-4">No. Order</th>
                    <th class="py-3 pr-4">Total</th>
                    <th class="py-3 pr-4">Status</th>
                    <th class="py-3 pr-4">Pembayaran</th>
                    <th class="py-3">Tanggal</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($orders as $order)
                <tr class="border-b">
                    <td class="py-3 pr-4 font-medium">{{ $order->order_number }}</td>
                    <td class="py-3 pr-4">Rp {{ number_format((int) $order->total_amount, 0, ',', '.') }}</td>
                    <td class="py-3 pr-4">{{ $order->status?->value ?? '-' }}</td>
                    <td class="py-3 pr-4">{{ $order->payment_status?->value ?? '-' }}</td>
                    <td class="py-3">{{ optional($order->created_at)->format('d M Y H:i') }}</td>
                </tr>
                @foreach ($order->orderVendors->first()->orderItems as $item)

                <tr class="border-b">

                    <td colspan="5" class="py-3 pr-4 font-medium">

                        @php
                        $variant = $item->productVariant;
                        $product = $variant?->product;
                        $image = $product?->productImages?->first();
                        @endphp

                        <div class="flex  justify-between   rounded-lg">
                            <div class="flex items-start gap-4">
                                <div class="h-14 aspect-square bg-gray-100 rounded-lg overflow-hidden shrink-0">
                                    <img src="{{ Storage::url($image?->image ?? 'products/product_placeholder.jpg') }}"
                                        alt="Produk" class="w-full h-full object-cover">
                                </div>

                                <div class="flex-1 space-y-1 ">
                                    <h1 class="text-xs/tight font-light">{{ $product?->category->name ?? 'Produk' }}
                                    </h1>
                                    <h3 class="text-xs/normal font-semibold uppercase ">
                                        {{ $product?->name ?? 'Produk' }}</h3>
                                    <p class="uppercase text-xs font-medium">Rp
                                        {{ number_format((float) $item->price, 0, ',', ',') }}
                                    </p>

                                </div>
                            </div>

                            <div class="flex items-start gap-3 ">
                                @if ($variant?->variant_name)
                                <p class="px-3 py-2  text-sm inline-block ">
                                    {{ $variant->variant_name }}</p>
                                @endif
                            </div>
                        </div>

                    </td>

                </tr>
                @endforeach
                @empty
                <tr>
                    <td colspan="5" class="py-6 text-center text-gray-500">Belum ada riwayat pesanan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $orders->links() }}
    </div>
</div>
