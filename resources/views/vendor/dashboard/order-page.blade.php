@php
    $statusColors = [
        'pending' => 'bg-yellow-100 text-yellow-800',
        'processed' => 'bg-blue-100 text-blue-800',
        'shipped' => 'bg-indigo-100 text-indigo-800',
        'delivered' => 'bg-violet-100 text-violet-800',
        'completed' => 'bg-green-100 text-green-800',
        'cancelled' => 'bg-red-100 text-red-800',
    ];
@endphp

<div class="p-6 rounded-xl">
    @php
        $reportParams = [];

        if (filled($selectedStatus)) {
            $reportParams['status'] = $selectedStatus;
        }

        if (filled($search)) {
            $reportParams['search'] = $search;
        }
    @endphp

    <div class="flex flex-wrap items-start justify-between gap-4">
        <div class="mb-6">
            <h2 class="text-xl font-semibold">Daftar Pesanan</h2>
            <p class="text-sm text-gray-500">Pesanan yang masuk ke toko Anda. Klik untuk melihat detail.</p>
        </div>
        <div class="flex gap-3 flex-wrap">
            <a href="{{ route('vendor.orders.report.pdf', $reportParams) }}" target="_blank" rel="noopener noreferrer"
                class="inline-flex items-center rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700">
                Laporan PDF
            </a>
            @component('components.form.input', [
                'label' => '',
                'type' => 'text',
                'wireModel' => 'search',
                'placeholder' => 'Cari no. order...',
                'live' => true,
            ]) @endcomponent
            @component('components.form.select', [
                'label' => '',
                'wireModel' => 'selectedStatus',
                'default' => [
                    'label' => 'Semua Status',
                    'value' => '',
                ],
                'options' => $statusOptions,
            ]) @endcomponent
        </div>
    </div>

    @if (session()->has('success'))
        <div class="p-3 rounded-lg border border-green-200 bg-green-50 text-green-700 text-sm mb-4">
            {{ session('success') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div class="p-3 rounded-lg border border-red-200 bg-red-50 text-red-700 text-sm mb-4">
            {{ session('error') }}
        </div>
    @endif

    <div class="overflow-x-auto" wire:loading.class="opacity-50 pointer-events-none">
        <div class="space-y-4">
            @forelse ($orderVendors as $orderVendor)
                <div class="border rounded-lg overflow-hidden">
                    {{-- Order Header --}}
                    <div class="bg-gray-50 px-5 py-3 flex flex-wrap items-center justify-between gap-3">
                        <div class="flex items-center gap-4">
                            <div>
                                <span class="text-sm font-semibold">{{ $orderVendor->order?->order_number ?? '-' }}</span>
                                <span class="text-xs text-gray-500 ml-2">{{ $orderVendor->created_at?->format('d M Y, H:i') }}</span>
                            </div>
                            <div class="text-xs text-gray-500">
                                <span class="font-medium text-gray-700">{{ $orderVendor->order?->user?->name ?? 'Customer' }}</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="inline-block px-2.5 py-1 rounded-full text-xs font-semibold {{ $statusColors[$orderVendor->status->value] ?? 'bg-gray-100' }}">
                                {{ $orderVendor->status->getLabel() }}
                            </span>
                        </div>
                    </div>

                    {{-- Order Items --}}
                    <div class="px-5 py-3">
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
                                                <span class="inline-block px-2 py-0.5 bg-primary/10 text-primary text-xs rounded">{{ $variant->variant_name }}</span>
                                            @endif
                                            <span class="text-xs text-gray-500">x{{ $item->quantity }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-semibold">Rp {{ number_format((float) $item->total, 0, ',', '.') }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Order Footer --}}
                    <div class="bg-gray-50 px-5 py-3 flex flex-wrap items-center justify-between gap-3 border-t">
                        <div class="flex items-center gap-3">
                            @if ($orderVendor->status->value === 'pending')
                                <button type="button" wire:click="processOrder({{ $orderVendor->id }})"
                                    wire:confirm="Proses pesanan ini?"
                                    class="px-3 py-1.5 bg-blue-600 text-white rounded-lg text-xs font-semibold hover:opacity-90">
                                    Proses Pesanan
                                </button>
                            @endif
                            <button type="button" wire:click="viewOrder({{ $orderVendor->id }})"
                                class="px-3 py-1.5 border border-gray-300 rounded-lg text-xs font-medium hover:bg-gray-100">
                                Lihat Detail
                            </button>
                        </div>
                        <div class="text-right">
                            <span class="text-xs text-gray-500">Subtotal</span>
                            <p class="text-base font-semibold">Rp {{ number_format((float) $orderVendor->subtotal, 0, ',', '.') }}</p>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center border rounded-lg">
                    <svg class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <p class="text-gray-500">Belum ada pesanan masuk.</p>
                </div>
            @endforelse
        </div>
    </div>

    <div class="mt-6">
        {{ $orderVendors->links() }}
    </div>
</div>
