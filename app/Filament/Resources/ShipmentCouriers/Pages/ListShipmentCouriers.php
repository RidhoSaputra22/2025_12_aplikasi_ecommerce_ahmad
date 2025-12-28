<?php

namespace App\Filament\Resources\ShipmentCouriers\Pages;

use App\Filament\Resources\ShipmentCouriers\ShipmentCourierResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListShipmentCouriers extends ListRecords
{
    protected static string $resource = ShipmentCourierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
