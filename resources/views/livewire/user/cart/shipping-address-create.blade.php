<div>
    <form wire:submit.prevent="save" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @component('components.form.input', [
                'label' => 'Provinsi',
                'wireModel' => 'province',
                'placeholder' => 'Masukkan provinsi',
                'required' => true,
            ])
            @endcomponent

            @component('components.form.input', [
                'label' => 'Kota',
                'wireModel' => 'city',
                'placeholder' => 'Masukkan kota',
                'required' => true,
            ])
            @endcomponent

            @component('components.form.input', [
                'label' => 'Kecamatan',
                'wireModel' => 'district',
                'placeholder' => 'Masukkan kecamatan',
                'required' => true,
            ])
            @endcomponent

            @component('components.form.input', [
                'label' => 'Kode Pos',
                'wireModel' => 'postal_code',
                'placeholder' => 'Masukkan kode pos',
                'required' => true,
            ])
            @endcomponent
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Alamat</label>
            <textarea
                rows="3"
                wire:model.defer="address"
                class="w-full rounded-lg border border-gray-200 p-3 text-sm"
                placeholder="Masukkan alamat lengkap"
            ></textarea>
            @error('address')
                <div class="mt-1 text-xs text-red-600">{{ $message }}</div>
            @enderror
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <button
                type="submit"
                wire:loading.attr="disabled"
                wire:target="save"
                class="w-full bg-primary text-white py-3 px-4 rounded-lg text-sm font-semibold hover:opacity-90 cursor-pointer"
            >
                <span wire:loading.remove wire:target="save">Simpan</span>
                <span wire:loading wire:target="save">Menyimpan...</span>
            </button>

            @if (!empty($shipmentAddressId))
                <button
                    type="button"
                    wire:click="delete"
                    wire:loading.attr="disabled"
                    wire:target="delete"
                    class="w-full bg-red-600 text-white py-3 px-4 rounded-lg text-sm font-semibold hover:opacity-90 cursor-pointer"
                >
                    <span wire:loading.remove wire:target="delete">Hapus</span>
                    <span wire:loading wire:target="delete">Menghapus...</span>
                </button>
            @else
                <div class="hidden md:block"></div>
            @endif
        </div>
    </form>
</div>
