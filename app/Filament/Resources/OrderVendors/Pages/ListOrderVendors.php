<?php

namespace App\Filament\Resources\OrderVendors\Pages;

use App\Filament\Resources\OrderVendors\OrderVendorResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOrderVendors extends ListRecords
{
    protected static string $resource = OrderVendorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
