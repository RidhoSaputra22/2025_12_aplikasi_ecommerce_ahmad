<?php

namespace App\Filament\Resources\OrderVendors\Pages;

use App\Filament\Resources\OrderVendors\OrderVendorResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditOrderVendor extends EditRecord
{
    protected static string $resource = OrderVendorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
