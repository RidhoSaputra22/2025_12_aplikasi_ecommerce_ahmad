<?php

namespace App\Filament\Resources\ShipmentCouriers;

use App\Filament\Resources\ShipmentCouriers\Pages\CreateShipmentCourier;
use App\Filament\Resources\ShipmentCouriers\Pages\EditShipmentCourier;
use App\Filament\Resources\ShipmentCouriers\Pages\ListShipmentCouriers;
use App\Filament\Resources\ShipmentCouriers\Schemas\ShipmentCourierForm;
use App\Filament\Resources\ShipmentCouriers\Tables\ShipmentCouriersTable;
use App\Models\ShipmentCourier;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ShipmentCourierResource extends Resource
{
    protected static ?string $model = ShipmentCourier::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return ShipmentCourierForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ShipmentCouriersTable::configure($table);
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
            'index' => ListShipmentCouriers::route('/'),
            'create' => CreateShipmentCourier::route('/create'),
            'edit' => EditShipmentCourier::route('/{record}/edit'),
        ];
    }
}
