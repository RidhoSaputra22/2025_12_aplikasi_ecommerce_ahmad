<?php

namespace App\Filament\Resources\OrderVendors;

use App\Filament\Resources\OrderVendors\Pages\CreateOrderVendor;
use App\Filament\Resources\OrderVendors\Pages\EditOrderVendor;
use App\Filament\Resources\OrderVendors\Pages\ListOrderVendors;
use App\Filament\Resources\OrderVendors\Schemas\OrderVendorForm;
use App\Filament\Resources\OrderVendors\Tables\OrderVendorsTable;
use App\Models\OrderVendor;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class OrderVendorResource extends Resource
{
    protected static ?string $model = OrderVendor::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return OrderVendorForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OrderVendorsTable::configure($table);
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
            'index' => ListOrderVendors::route('/'),
            'create' => CreateOrderVendor::route('/create'),
            'edit' => EditOrderVendor::route('/{record}/edit'),
        ];
    }
}
