<div class="p-6 space-y-6">
    <div>
        <h2 class="text-xl font-semibold">Wallet</h2>
        <p class="text-sm text-gray-500">Saldo dan riwayat transaksi wallet Anda.</p>
    </div>

    {{-- Balance Card --}}
    <div class="border rounded-lg p-6 bg-gradient-to-r from-primary/5 to-primary/10">
        <p class="text-sm text-gray-600 mb-1">Saldo Tersedia</p>
        <p class="text-3xl font-bold">Rp {{ number_format((float) ($wallet?->balance ?? 0), 0, ',', '.') }}</p>
    </div>

    {{-- Transaction History --}}
    <div class="space-y-3">
        <h3 class="text-lg font-semibold">Riwayat Transaksi</h3>

        @forelse ($transactions as $trx)
            @php
                $isCredit = $trx->type->value === 'credit';
            @endphp
            <div class="border rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center {{ $isCredit ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600' }}">
                            @if ($isCredit)
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19.5v-15m0 0l-6.75 6.75M12 4.5l6.75 6.75" />
                                </svg>
                            @else
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m0 0l6.75-6.75M12 19.5l-6.75-6.75" />
                                </svg>
                            @endif
                        </div>
                        <div>
                            <p class="text-sm font-semibold {{ $isCredit ? 'text-green-700' : 'text-red-700' }}">
                                {{ $trx->type->getLabel() }}
                            </p>
                            <p class="text-xs text-gray-500">{{ $trx->description ?? '-' }}</p>
                            <p class="text-xs text-gray-400">{{ $trx->created_at?->format('d M Y, H:i') }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-semibold {{ $isCredit ? 'text-green-700' : 'text-red-700' }}">
                            {{ $isCredit ? '+' : '-' }} Rp {{ number_format((float) $trx->amount, 0, ',', '.') }}
                        </p>
                    </div>
                </div>
            </div>
        @empty
            <div class="p-8 text-center border rounded-lg">
                <svg class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a2.25 2.25 0 0 0-2.25-2.25H15a3 3 0 1 1-6 0H5.25A2.25 2.25 0 0 0 3 12m18 0v6a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 18v-6m18 0V9M3 12V9m18 0a2.25 2.25 0 0 0-2.25-2.25H5.25A2.25 2.25 0 0 0 3 9m18 0V6a2.25 2.25 0 0 0-2.25-2.25H5.25A2.25 2.25 0 0 0 3 6v3"/>
                </svg>
                <p class="text-gray-500">Belum ada transaksi.</p>
            </div>
        @endforelse
    </div>

    @if ($transactions->hasPages())
        <div class="mt-6">
            {{ $transactions->links() }}
        </div>
    @endif
</div>
