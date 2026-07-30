<div class="p-6 space-y-6">

    {{-- Header --}}
    <div>
        <div class="flex items-center gap-2 mb-2">
            <a wire:navigate href="{{ route('vendor.dashboard', ['tab' => 'products']) }}"
                class="text-sm text-primary hover:underline">&larr; Kembali</a>
        </div>
        <h2 class="text-xl font-semibold">{{ $productId ? 'Edit Produk' : 'Tambah Produk Baru' }}</h2>
        <p class="text-sm text-gray-500">{{ $productId ? 'Perbarui informasi produk Anda.' : 'Isi informasi produk untuk mulai berjualan.' }}</p>
    </div>

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

    <form wire:submit.prevent="save" class="space-y-6" wire:loading.class="opacity-50 pointer-events-none">

        {{-- Basic Info --}}
        <div class="border rounded-lg p-5 space-y-4">
            <h3 class="text-lg font-semibold">Informasi Dasar</h3>
            <div class="grid grid-cols-2 gap-6">
                @component('components.form.input', [
                    'label' => 'Nama Produk',
                    'type' => 'text',
                    'wireModel' => 'name',
                    'placeholder' => 'Masukkan nama produk',
                    'required' => true,
                ]) @endcomponent

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kategori <span class="text-red-500">*</span></label>
                    <select wire:model="category_id" class="w-full border border-gray-300 rounded-sm px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary">
                        <option value="">Pilih Kategori</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                    @error('category_id') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
                </div>

                @component('components.form.input', [
                    'label' => 'Harga (Rp)',
                    'type' => 'number',
                    'wireModel' => 'price',
                    'placeholder' => '0',
                    'required' => true,
                ]) @endcomponent

                @component('components.form.input', [
                    'label' => 'Berat (gram)',
                    'type' => 'number',
                    'wireModel' => 'weight',
                    'placeholder' => '0',
                    'required' => true,
                ]) @endcomponent

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select wire:model="status" class="w-full border border-gray-300 rounded-sm px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary">
                        <option value="draft">Draf</option>
                        <option value="active">Aktif</option>
                        <option value="archived">Diarsipkan</option>
                    </select>
                </div>
            </div>

            <div>
                @component('components.form.textarea', [
                    'label' => 'Deskripsi',
                    'wireModel' => 'description',
                    'placeholder' => 'Deskripsi produk...',
                    'required' => false,
                ]) @endcomponent
            </div>
        </div>

        {{-- Images --}}
        <div class="border rounded-lg p-5 space-y-4">
            <h3 class="text-lg font-semibold">Gambar Produk</h3>

            {{-- Existing images --}}
            @if (!empty($existingImages))
                <div class="flex flex-wrap gap-3">
                    @foreach ($existingImages as $index => $img)
                        <div class="relative group">
                            <img src="{{ Storage::url($img['image']) }}" alt="Produk"
                                class="h-24 w-24 object-cover rounded-lg border">
                            <button type="button" wire:click="removeExistingImage({{ $index }})"
                                class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs hover:bg-red-600">
                                &times;
                            </button>
                        </div>
                    @endforeach
                </div>
            @endif

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Upload Gambar Baru</label>
                <input type="file" wire:model="newImages" multiple accept="image/*"
                    class="text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20">
                @error('newImages.*') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                <div wire:loading wire:target="newImages" class="text-xs text-gray-500 mt-1">Mengupload...</div>
            </div>
        </div>

        {{-- Variants --}}
        <div class="border rounded-lg p-5 space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold">Varian Produk</h3>
                <button type="button" wire:click="addVariant"
                    class="px-3 py-1.5 border rounded-lg text-xs font-medium hover:bg-gray-100">
                    + Tambah Varian
                </button>
            </div>

            @if (empty($variants))
                <p class="text-sm text-gray-500">Belum ada varian. Klik "Tambah Varian" untuk menambahkan.</p>
                @error('variants') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
            @else
                <div class="space-y-3">
                    @foreach ($variants as $index => $variant)
                        <div class="p-4 border rounded-lg bg-gray-50">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex-1 grid grid-cols-4 gap-3">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-1">Nama Varian *</label>
                                        <input type="text" wire:model="variants.{{ $index }}.variant_name"
                                            class="w-full border border-gray-300 rounded-sm px-2 py-1.5 text-sm"
                                            placeholder="Contoh: Merah - XL">
                                        @error("variants.{$index}.variant_name") <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-1">SKU</label>
                                        <input type="text" wire:model="variants.{{ $index }}.sku"
                                            class="w-full border border-gray-300 rounded-sm px-2 py-1.5 text-sm"
                                            placeholder="SKU-001">
                                        @error("variants.{$index}.sku") <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-1">Harga (Rp) *</label>
                                        <input type="number" wire:model="variants.{{ $index }}.price"
                                            class="w-full border border-gray-300 rounded-sm px-2 py-1.5 text-sm"
                                            placeholder="0">
                                        @error("variants.{$index}.price") <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-1">Stok *</label>
                                        <input type="number" wire:model="variants.{{ $index }}.stock"
                                            class="w-full border border-gray-300 rounded-sm px-2 py-1.5 text-sm"
                                            placeholder="0">
                                        @error("variants.{$index}.stock") <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <button type="button" wire:click="removeVariant({{ $index }})"
                                    wire:confirm="Hapus varian ini?"
                                    class="text-red-500 hover:text-red-700 mt-5">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Submit --}}
        <div class="flex items-center gap-3">
            <button type="submit" wire:loading.attr="disabled" wire:target="save"
                class="bg-primary text-white px-6 py-2 rounded-sm text-sm font-semibold hover:opacity-90">
                {{ $productId ? 'Simpan Perubahan' : 'Tambah Produk' }}
            </button>
            <button type="button" wire:click="cancel"
                class="px-6 py-2 rounded-sm text-sm font-semibold border hover:bg-gray-50">
                Batal
            </button>
        </div>
    </form>
</div>
