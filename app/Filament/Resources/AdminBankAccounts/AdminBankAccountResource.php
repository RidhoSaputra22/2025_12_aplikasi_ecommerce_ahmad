<?php

namespace App\Filament\Resources\AdminBankAccounts;

use App\Filament\Resources\AdminBankAccounts\Pages\CreateAdminBankAccount;
use App\Filament\Resources\AdminBankAccounts\Pages\EditAdminBankAccount;
use App\Filament\Resources\AdminBankAccounts\Pages\ListAdminBankAccounts;
use App\Filament\Resources\AdminBankAccounts\Schemas\AdminBankAccountForm;
use App\Filament\Resources\AdminBankAccounts\Tables\AdminBankAccountsTable;
use App\Models\AdminBankAccount;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class AdminBankAccountResource extends Resource
{
    protected static ?string $model = AdminBankAccount::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingLibrary;
    protected static null|UnitEnum|string $navigationGroup = 'Pengaturan';
    protected static ?string $navigationLabel = 'Rekening Admin';
    protected static ?string $pluralLabel = 'Rekening Admin';
    protected static ?string $modelLabel = 'Rekening Admin';

    protected static ?string $recordTitleAttribute = 'bank_name';

    public static function form(Schema $schema): Schema
    {
        return AdminBankAccountForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AdminBankAccountsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAdminBankAccounts::route('/'),
            'create' => CreateAdminBankAccount::route('/create'),
            'edit' => EditAdminBankAccount::route('/{record}/edit'),
        ];
    }
}
