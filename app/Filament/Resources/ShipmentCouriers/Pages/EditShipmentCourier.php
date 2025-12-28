<?php

namespace App\Filament\Resources\ShipmentCouriers\Pages;

use App\Filament\Resources\ShipmentCouriers\ShipmentCourierResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditShipmentCourier extends EditRecord
{
    protected static string $resource = ShipmentCourierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
