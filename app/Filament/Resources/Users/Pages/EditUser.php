<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\UserRole;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected ?int $roleId = null;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['role_id'] = $this->record->userRoles?->role_id;

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->roleId = (int) $data['role_id'];
        unset($data['role_id']);

        return $data;
    }

    protected function afterSave(): void
    {
        UserRole::query()->updateOrCreate(
            ['user_id' => $this->record->id],
            ['role_id' => $this->roleId],
        );
    }
}
