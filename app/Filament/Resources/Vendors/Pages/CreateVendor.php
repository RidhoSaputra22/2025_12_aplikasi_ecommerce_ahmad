<?php

namespace App\Filament\Resources\Vendors\Pages;

use App\Filament\Resources\Vendors\VendorResource;
use App\Models\Role;
use App\Models\UserRole;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;

class CreateVendor extends CreateRecord
{
    protected static string $resource = VendorResource::class;

    protected ?int $vendorRoleId = null;

    protected function beforeCreate(): void
    {
        $this->vendorRoleId = Role::query()->where('name', 'vendor')->value('id');

        if (! $this->vendorRoleId) {
            throw ValidationException::withMessages([
                'data.user_id' => 'Role vendor belum tersedia.',
            ]);
        }
    }

    protected function afterCreate(): void
    {
        UserRole::query()->updateOrCreate(
            ['user_id' => $this->record->user_id],
            ['role_id' => $this->vendorRoleId],
        );
    }
}
