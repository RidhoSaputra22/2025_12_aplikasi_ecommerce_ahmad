<?php

namespace App\Filament\Vendor\Resources\Shipments\Pages;

use App\Filament\Vendor\Resources\Shipments\ShipmentResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditShipment extends EditRecord
{
    protected static string $resource = ShipmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $shipment = $this->record;


        $data = array_merge($data, [
            'user_id' => $shipment->orderVendor->order->user_id,
            'shipment_address_id' => $shipment->shipment_address_id,
            'shipment_courier_id' => $shipment->shipment_courier_id,
            'shipping_cost' => $shipment->shipping_cost,
        ]);

        return parent::mutateFormDataBeforeFill($data);
    }
}
