<?php

namespace App\Filament\Vendor\Resources\Carts\Pages;

use App\Filament\Vendor\Resources\Carts\CartResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCarts extends ListRecords
{
    protected static string $resource = CartResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
