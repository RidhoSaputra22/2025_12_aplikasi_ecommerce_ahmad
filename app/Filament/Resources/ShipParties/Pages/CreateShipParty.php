<?php

namespace App\Filament\Resources\ShipParties\Pages;

use App\Filament\Resources\ShipParties\ShipPartyResource;
use App\Models\Role;
use App\Models\ShipmentCourier;
use App\Models\UserRole;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateShipParty extends CreateRecord
{
    protected static string $resource = ShipPartyResource::class;

    protected ?int $shipPartyRoleId = null;

    protected ?int $shipmentCourierId = null;

    protected function beforeCreate(): void
    {
        $this->shipPartyRoleId = Role::query()->where('name', 'pihak_kapal')->value('id');

        if (! $this->shipPartyRoleId) {
            throw ValidationException::withMessages([
                'data.name' => 'Role pihak kapal belum tersedia.',
            ]);
        }
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->shipmentCourierId = (int) $data['shipment_courier_id'];
        unset($data['shipment_courier_id']);

        return $data;
    }

    protected function afterCreate(): void
    {
        DB::transaction(function (): void {
            UserRole::query()->updateOrCreate(
                ['user_id' => $this->record->id],
                ['role_id' => $this->shipPartyRoleId],
            );

            ShipmentCourier::query()
                ->whereKey($this->shipmentCourierId)
                ->update(['user_id' => $this->record->id]);
        });
    }
}
