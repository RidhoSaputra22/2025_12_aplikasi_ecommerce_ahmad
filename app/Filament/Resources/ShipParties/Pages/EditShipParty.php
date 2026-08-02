<?php

namespace App\Filament\Resources\ShipParties\Pages;

use App\Filament\Resources\ShipParties\ShipPartyResource;
use App\Models\Role;
use App\Models\ShipmentCourier;
use App\Models\UserRole;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;

class EditShipParty extends EditRecord
{
    protected static string $resource = ShipPartyResource::class;

    protected ?int $shipmentCourierId = null;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['shipment_courier_id'] = $this->record->managedShipmentCourier?->id;

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->shipmentCourierId = (int) $data['shipment_courier_id'];
        unset($data['shipment_courier_id']);

        return $data;
    }

    protected function afterSave(): void
    {
        $shipPartyRoleId = Role::query()->where('name', 'pihak_kapal')->value('id');

        DB::transaction(function () use ($shipPartyRoleId): void {
            if ($shipPartyRoleId) {
                UserRole::query()->updateOrCreate(
                    ['user_id' => $this->record->id],
                    ['role_id' => $shipPartyRoleId],
                );
            }

            ShipmentCourier::query()
                ->where('user_id', $this->record->id)
                ->whereKeyNot($this->shipmentCourierId)
                ->update(['user_id' => null]);

            ShipmentCourier::query()
                ->whereKey($this->shipmentCourierId)
                ->update(['user_id' => $this->record->id]);
        });
    }
}
