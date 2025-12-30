<div>
    <form wire:submit.prevent="save" class="space-y-4">
        <div>
            <label class="block text-sm font-medium mb-1">Foto Profil</label>
            <input type="file" wire:model="photo" accept="image/*" class="w-full rounded-lg border border-gray-200 p-3 text-sm" />
            @error('photo')
                <div class="mt-1 text-xs text-red-600">{{ $message }}</div>
            @enderror
        </div>

        @if ($photo)
            <div class="p-3 rounded-lg border bg-white">
                <div class="text-sm font-medium mb-2">Preview</div>
                <img src="{{ $photo->temporaryUrl() }}" alt="Preview" class="w-32 h-32 rounded-full object-cover border" />
            </div>
        @endif

        <button
            type="submit"
            wire:loading.attr="disabled"
            wire:target="save,photo"
            class="w-full bg-primary text-white py-3 px-4 rounded-lg text-sm font-semibold hover:opacity-90 cursor-pointer"
        >
            <span wire:loading.remove wire:target="save">Simpan Foto</span>
            <span wire:loading wire:target="save">Menyimpan...</span>
        </button>

        <div wire:loading wire:target="photo" class="text-sm text-gray-500">
            Mengunggah file...
        </div>
    </form>
</div>
