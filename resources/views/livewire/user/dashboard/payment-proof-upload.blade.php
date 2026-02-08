<div class="space-y-4">
    <form wire:submit.prevent="save" class="space-y-4">
        <div>
            <p class="text-sm text-gray-600 mb-3">Upload bukti pembayaran (transfer bank, e-wallet, dll). Format: JPG, PNG. Maksimal 2MB.</p>

            <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center">
                <input type="file" wire:model="paymentProof" accept="image/*" class="hidden" id="payment-proof-input">
                <label for="payment-proof-input" class="cursor-pointer">
                    <div class="space-y-2">
                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <p class="text-sm text-gray-600">Klik untuk memilih file bukti pembayaran</p>
                    </div>
                </label>

                <div wire:loading wire:target="paymentProof" class="mt-3">
                    <div class="flex items-center justify-center gap-2 text-sm text-gray-600">
                        @include('components.spinner')
                        <span>Mengupload...</span>
                    </div>
                </div>
            </div>

            @if ($paymentProof)
            <div class="mt-4">
                <p class="text-sm font-medium text-gray-700 mb-2">Preview:</p>
                <img src="{{ $paymentProof->temporaryUrl() }}" alt="Preview" class="max-h-64 rounded-lg border">
            </div>
            @endif

            @error('paymentProof')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex justify-end gap-3">
            <button type="button" wire:click="$dispatch('forceCloseModal')"
                class="px-4 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-50">
                Batal
            </button>
            <button type="submit" wire:loading.attr="disabled" wire:target="save"
                class="px-4 py-2 bg-primary text-white rounded-lg text-sm font-semibold hover:opacity-90">
                <span wire:loading.remove wire:target="save">Upload Bukti Pembayaran</span>
                <span wire:loading wire:target="save">Memproses...</span>
            </button>
        </div>
    </form>
</div>
