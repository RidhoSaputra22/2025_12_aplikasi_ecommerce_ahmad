<?php

namespace App\Filament\Resources\AdminBankAccounts\Pages;

use App\Filament\Resources\AdminBankAccounts\AdminBankAccountResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAdminBankAccounts extends ListRecords
{
    protected static string $resource = AdminBankAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
