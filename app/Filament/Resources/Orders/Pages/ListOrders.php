<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Wizard;

/**
 * ListOrders Page
 *
 * Halaman untuk menampilkan daftar orders dan membuat order baru melalui wizard
 * dengan langkah: pilih produk, pengiriman, dan pembayaran
 */
class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    /**
     * Mendapatkan aksi header yang tersedia di halaman list orders
     */
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Buat Order Baru'),
        ];
    }
}
