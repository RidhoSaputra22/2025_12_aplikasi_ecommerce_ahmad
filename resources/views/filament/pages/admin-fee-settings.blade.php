<x-filament-panels::page>
    {{ $this->form }}

    @php
        $preview = $this->getPreviewBreakdown();
    @endphp

    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <x-filament::section>
            <div class="text-center">
                <div class="text-sm text-gray-500 dark:text-gray-400">Contoh Subtotal Vendor</div>
                <div class="text-2xl font-bold text-gray-900 dark:text-white">
                    Rp {{ number_format($preview['gross_amount'], 0, ',', '.') }}
                </div>
            </div>
        </x-filament::section>

        <x-filament::section>
            <div class="text-center">
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    Potongan Admin ({{ number_format($preview['admin_fee_percentage'], 2, ',', '.') }}%)
                </div>
                <div class="text-2xl font-bold text-warning-600">
                    Rp {{ number_format($preview['admin_fee_amount'], 0, ',', '.') }}
                </div>
            </div>
        </x-filament::section>

        <x-filament::section>
            <div class="text-center">
                <div class="text-sm text-gray-500 dark:text-gray-400">Diterima Vendor</div>
                <div class="text-2xl font-bold text-success-600">
                    Rp {{ number_format($preview['vendor_payout_amount'], 0, ',', '.') }}
                </div>
            </div>
        </x-filament::section>
    </div>

    <x-filament::section>
        <x-slot name="heading">
            Cara Kerja
        </x-slot>

        <div class="space-y-2 text-sm text-gray-600 dark:text-gray-300">
            <p>Setiap order vendor akan menyimpan snapshot persentase dan nominal potongan admin.</p>
            <p>Saat dana dicairkan, wallet vendor menerima nilai bersih setelah dikurangi potongan admin.</p>
            <p>Perubahan persentase tidak mengubah histori order yang potongannya sudah tersimpan sebelumnya.</p>
        </div>
    </x-filament::section>
</x-filament-panels::page>
