<?php

namespace App\Filament\Resources\AdminBankAccounts\Pages;

use App\Filament\Resources\AdminBankAccounts\AdminBankAccountResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAdminBankAccount extends EditRecord
{
    protected static string $resource = AdminBankAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
