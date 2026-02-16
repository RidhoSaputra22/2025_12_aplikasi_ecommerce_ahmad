<div class="p-6 space-y-6">
    <div class="flex items-start justify-between">
        <div>
            <h2 class="text-xl font-semibold">Rekening Bank</h2>
            <p class="text-sm text-gray-500">Kelola rekening bank untuk penarikan dana.</p>
        </div>
        @if (!$showForm)
            <button type="button" wire:click="toggleForm"
                class="bg-primary text-white px-4 py-2 rounded-sm text-sm font-semibold hover:opacity-90">
                + Tambah Rekening
            </button>
        @endif
    </div>

    @if (session()->has('success'))
        <div class="p-3 rounded-lg border border-green-200 bg-green-50 text-green-700 text-sm">
            {{ session('success') }}
        </div>
    @endif

    {{-- Form --}}
    @if ($showForm)
        <div class="border rounded-lg p-5 space-y-4">
            <h3 class="text-lg font-semibold">{{ $editingId ? 'Edit Rekening' : 'Tambah Rekening Baru' }}</h3>
            <form wire:submit.prevent="save" class="space-y-4">
                <div class="grid grid-cols-3 gap-4">
                    @component('components.form.input', [
                        'label' => 'Nama Bank',
                        'type' => 'text',
                        'wireModel' => 'bank_name',
                        'placeholder' => 'Contoh: BCA, BNI, Mandiri',
                        'required' => true,
                    ]) @endcomponent

                    @component('components.form.input', [
                        'label' => 'Nomor Rekening',
                        'type' => 'text',
                        'wireModel' => 'account_number',
                        'placeholder' => 'Masukkan nomor rekening',
                        'required' => true,
                    ]) @endcomponent

                    @component('components.form.input', [
                        'label' => 'Nama Pemilik',
                        'type' => 'text',
                        'wireModel' => 'account_holder',
                        'placeholder' => 'Nama sesuai rekening',
                        'required' => true,
                    ]) @endcomponent
                </div>

                <div class="flex items-center gap-3">
                    <button type="submit" wire:loading.attr="disabled"
                        class="bg-primary text-white px-4 py-2 rounded-sm text-sm font-semibold hover:opacity-90">
                        {{ $editingId ? 'Simpan Perubahan' : 'Tambah Rekening' }}
                    </button>
                    <button type="button" wire:click="toggleForm"
                        class="px-4 py-2 rounded-sm text-sm font-semibold border hover:bg-gray-50">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    @endif

    {{-- List --}}
    <div class="space-y-3">
        @forelse ($accounts as $account)
            <div class="border rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-blue-50 rounded-lg flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                            </svg>
                        </div>
                        <div>
                            <p class="font-semibold text-sm">{{ $account->bank_name }}</p>
                            <p class="text-sm text-gray-600">{{ $account->account_number }}</p>
                            <p class="text-xs text-gray-500">a.n. {{ $account->account_holder }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" wire:click="edit({{ $account->id }})"
                            class="px-3 py-1.5 border rounded-lg text-xs font-medium hover:bg-gray-100">
                            Edit
                        </button>
                        <button type="button" wire:click="delete({{ $account->id }})"
                            wire:confirm="Yakin ingin menghapus rekening ini?"
                            class="px-3 py-1.5 bg-red-500 text-white rounded-lg text-xs font-semibold hover:opacity-90">
                            Hapus
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="p-8 text-center border rounded-lg">
                <svg class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                </svg>
                <p class="text-gray-500">Belum ada rekening bank terdaftar.</p>
                @if (!$showForm)
                    <button type="button" wire:click="toggleForm" class="inline-block mt-3 text-primary text-sm hover:underline">
                        Tambah Rekening Pertama
                    </button>
                @endif
            </div>
        @endforelse
    </div>
</div>
