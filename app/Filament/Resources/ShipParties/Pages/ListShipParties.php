<?php

namespace App\Filament\Resources\ShipParties\Pages;

use App\Filament\Resources\ShipParties\ShipPartyResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListShipParties extends ListRecords
{
    protected static string $resource = ShipPartyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
