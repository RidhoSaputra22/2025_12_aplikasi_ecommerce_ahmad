@php
$statusColors = [
'draft' => 'bg-gray-100 text-gray-800',
'active' => 'bg-green-100 text-green-800',
'archived' => 'bg-yellow-100 text-yellow-800',
];
@endphp

<div class="p-6 rounded-xl">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div class="mb-6">
            <h2 class="text-xl font-semibold">Daftar Produk</h2>
            <p class="text-sm text-gray-500">Kelola produk toko Anda.</p>
        </div>
        <div class="flex gap-3 flex-wrap items-end">
            @component('components.form.input', [
            'label' => '',
            'type' => 'text',
            'wireModel' => 'search',
            'placeholder' => 'Cari produk...',
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
            <button type="button" wire:click="createProduct"
                class="bg-primary text-white px-4 py-3 rounded-sm text-sm font-semibold hover:opacity-90 h-fit">
                + Tambah Produk
            </button>
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
            @forelse ($products as $product)
            @php
            $image = $product->productImages?->first();
            $variantCount = $product->productVariants?->count() ?? 0;
            $totalStock = $product->productVariants?->sum('stock') ?? 0;
            @endphp
            <div class="border rounded-lg overflow-hidden">
                <div class="px-5 py-4 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="h-16 w-16 bg-gray-100 rounded-lg overflow-hidden shrink-0">
                            <img src="{{ Storage::url($image?->image ?? 'products/product_placeholder.jpg') }}"
                                alt="{{ $product->name }}" class="w-full h-full object-cover">
                        </div>
                        <div class="space-y-1">
                            <h4 class="font-semibold text-sm uppercase">{{ $product->name }}</h4>
                            <p class="text-xs text-gray-500">{{ $product->category?->name ?? '-' }}</p>
                            <div class="flex items-center gap-3 text-xs text-gray-500">
                                <span>Rp {{ number_format((float) $product->price, 0, ',', '.') }}</span>
                                <span>&middot;</span>
                                <span>{{ $variantCount }} varian</span>
                                <span>&middot;</span>
                                <span>{{ $totalStock }} stok</span>
                                <span>&middot;</span>
                                <span>{{ $product->weight }}g</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <span
                            class="inline-block px-2.5 py-1 rounded-full text-xs font-semibold {{ $statusColors[$product->status->value] ?? 'bg-gray-100' }}">
                            {{ $product->status->getLabel() }}
                        </span>
                        <button type="button" wire:click="toggleStatus({{ $product->id }})"
                            class="px-3 py-1.5 border rounded-lg text-xs font-medium hover:bg-gray-100"
                            title="{{ $product->status->value === 'active' ? 'Nonaktifkan' : 'Aktifkan' }}">
                            {{ $product->status->value === 'active' ? 'Nonaktifkan' : 'Aktifkan' }}
                        </button>
                        <button type="button" wire:click="editProduct({{ $product->id }})"
                            class="px-3 py-1.5 bg-primary text-white rounded-lg text-xs font-semibold hover:opacity-90">
                            Edit
                        </button>
                        <button type="button" wire:click="deleteProduct({{ $product->id }})"
                            wire:confirm="Yakin ingin menghapus produk ini? Tindakan ini tidak bisa dibatalkan."
                            class="px-3 py-1.5 bg-red-500 text-white rounded-lg text-xs font-semibold hover:opacity-90">
                            Hapus
                        </button>
                    </div>
                </div>
            </div>
            @empty
            <div class="p-8 text-center border rounded-lg">
                <svg class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
                </svg>
                <p class="text-gray-500">Belum ada produk.</p>
                <button type="button" wire:click="createProduct"
                    class="inline-block mt-3 text-primary text-sm hover:underline">
                    Tambah Produk Pertama
                </button>
            </div>
            @endforelse
        </div>
    </div>

    <div class="mt-6">
        {{ $products->links() }}
    </div>
</div>
