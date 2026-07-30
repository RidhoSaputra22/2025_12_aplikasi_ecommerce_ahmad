<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\UserRole;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected ?int $roleId = null;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->roleId = (int) $data['role_id'];
        unset($data['role_id']);

        return $data;
    }

    protected function afterCreate(): void
    {
        UserRole::query()->updateOrCreate(
            ['user_id' => $this->record->id],
            ['role_id' => $this->roleId],
        );
    }
}
