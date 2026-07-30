<?php

namespace App\Filament\Resources\Vendors\Pages;

use App\Filament\Resources\Vendors\VendorResource;
use App\Models\Role;
use App\Models\UserRole;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditVendor extends EditRecord
{
    protected static string $resource = VendorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $vendorRoleId = Role::query()->where('name', 'vendor')->value('id');
        if ($vendorRoleId) {
            UserRole::query()->updateOrCreate(
                ['user_id' => $this->record->user_id],
                ['role_id' => $vendorRoleId],
            );
        }
    }
}
