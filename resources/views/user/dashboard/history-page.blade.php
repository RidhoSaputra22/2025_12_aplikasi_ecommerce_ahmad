<div class="p-6 rounded-xl border bg-white">
    <div class="mb-6">
        <h2 class="text-xl font-semibold">Riwayat Pesanan</h2>
        <p class="text-sm text-gray-500">Daftar pesanan yang pernah Anda buat.</p>
    </div>

    <div class="overflow-x-auto">
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
