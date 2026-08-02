<?php

namespace App\Filament\Resources\ShipParties;

use App\Filament\Resources\ShipParties\Pages\CreateShipParty;
use App\Filament\Resources\ShipParties\Pages\EditShipParty;
use App\Filament\Resources\ShipParties\Pages\ListShipParties;
use App\Filament\Resources\ShipParties\Schemas\ShipPartyForm;
use App\Filament\Resources\ShipParties\Tables\ShipPartiesTable;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class ShipPartyResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTruck;
    protected static null|UnitEnum|string $navigationGroup = 'Pengguna';
    protected static ?string $navigationLabel = 'Pihak Kapal';
    protected static ?string $pluralLabel = 'Pihak Kapal';
    protected static ?string $modelLabel = 'Pihak Kapal';
    protected static ?string $slug = 'ship-parties';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return ShipPartyForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ShipPartiesTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('role', fn (Builder $query) => $query->where('name', 'pihak_kapal'));
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListShipParties::route('/'),
            'create' => CreateShipParty::route('/create'),
            'edit' => EditShipParty::route('/{record}/edit'),
        ];
    }
}
