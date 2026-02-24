<x-filament-panels::page>
    {{ $this->form }}

    @php
        $orders = $this->getOrders();
        $totalAmount = $orders->sum('total_amount');
        $totalPaid = $orders->where('payment_status', \App\Enums\OrderPaymentStatus::Paid)->sum('total_amount');
    @endphp

    {{-- Ringkasan --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
        <x-filament::section>
            <div class="text-center">
                <div class="text-sm text-gray-500 dark:text-gray-400">Total Order</div>
                <div class="text-2xl font-bold text-primary-600">{{ number_format($orders->count()) }}</div>
            </div>
        </x-filament::section>
        <x-filament::section>
            <div class="text-center">
                <div class="text-sm text-gray-500 dark:text-gray-400">Total Nilai Order</div>
                <div class="text-2xl font-bold text-gray-900 dark:text-white">Rp {{ number_format($totalAmount, 0, ',', '.') }}</div>
            </div>
        </x-filament::section>
        <x-filament::section>
            <div class="text-center">
                <div class="text-sm text-gray-500 dark:text-gray-400">Total Terbayar</div>
                <div class="text-2xl font-bold text-success-600">Rp {{ number_format($totalPaid, 0, ',', '.') }}</div>
            </div>
        </x-filament::section>
        <x-filament::section>
            <div class="text-center">
                <div class="text-sm text-gray-500 dark:text-gray-400">Belum Terbayar</div>
                <div class="text-2xl font-bold text-danger-600">Rp {{ number_format($totalAmount - $totalPaid, 0, ',', '.') }}</div>
            </div>
        </x-filament::section>
    </div>

    {{-- Preview Tabel --}}
    <x-filament::section>
        <x-slot name="heading">
            Preview Data Order ({{ $orders->count() }} data)
        </x-slot>

        @if($orders->isEmpty())
            <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                <x-heroicon-o-document-magnifying-glass class="mx-auto h-12 w-12 mb-3 text-gray-400" />
                <p class="text-lg font-medium">Tidak ada data order</p>
                <p class="text-sm">Ubah filter untuk menampilkan data.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs uppercase bg-gray-50 dark:bg-white/5 text-gray-700 dark:text-gray-300">
                        <tr>
                            <th class="px-4 py-3">No</th>
                            <th class="px-4 py-3">No. Order</th>
                            <th class="px-4 py-3">Tanggal</th>
                            <th class="px-4 py-3">Customer</th>
                            <th class="px-4 py-3">Vendor</th>
                            <th class="px-4 py-3">Item</th>
                            <th class="px-4 py-3 text-right">Total</th>
                            <th class="px-4 py-3 text-center">Status</th>
                            <th class="px-4 py-3 text-center">Pembayaran</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                        @foreach($orders as $index => $order)
                            <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                                <td class="px-4 py-3 text-gray-500">{{ $index + 1 }}</td>
                                <td class="px-4 py-3 font-mono text-xs font-semibold">{{ $order->order_number }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                <td class="px-4 py-3">{{ $order->user?->name ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    @foreach($order->orderVendors as $ov)
                                        <div class="text-xs">{{ $ov->vendor?->store_name ?? '-' }}</div>
                                    @endforeach
                                </td>
                                <td class="px-4 py-3">
                                    @foreach($order->orderVendors as $ov)
                                        @foreach($ov->orderItems as $item)
                                            <div class="text-xs">
                                                {{ $item->productVariant?->product?->name ?? '-' }}
                                                ({{ $item->productVariant?->variant_name ?? '-' }})
                                                x{{ $item->quantity }}
                                            </div>
                                        @endforeach
                                    @endforeach
                                </td>
                                <td class="px-4 py-3 text-right font-semibold whitespace-nowrap">
                                    Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <x-filament::badge :color="$order->status->getColor()">
                                        {{ $order->status->getLabel() }}
                                    </x-filament::badge>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <x-filament::badge :color="$order->payment_status->getColor()">
                                        {{ $order->payment_status->getLabel() }}
                                    </x-filament::badge>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50 dark:bg-white/5 font-semibold">
                        <tr>
                            <td class="px-4 py-3" colspan="6">Total</td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">Rp {{ number_format($totalAmount, 0, ',', '.') }}</td>
                            <td class="px-4 py-3" colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif
    </x-filament::section>
</x-filament-panels::page>
