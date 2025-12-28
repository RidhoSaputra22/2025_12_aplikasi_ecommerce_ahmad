<?php

namespace App\Filament\Vendor\Resources\Carts;

use App\Filament\Vendor\Resources\Carts\Pages\CreateCart;
use App\Filament\Vendor\Resources\Carts\Pages\EditCart;
use App\Filament\Vendor\Resources\Carts\Pages\ListCarts;
use App\Filament\Vendor\Resources\Carts\Schemas\CartForm;
use App\Filament\Vendor\Resources\Carts\Tables\CartsTable;
use App\Models\Cart;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class CartResource extends Resource
{
    protected static ?string $model = Cart::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingCart;
    protected static null|UnitEnum|string $navigationGroup = 'Pesanan';
    protected static ?string $navigationLabel = 'Keranjang';
    protected static ?string $pluralLabel = 'Keranjang';
    protected static ?string $modelLabel = 'Keranjang';


    protected static ?string $recordTitleAttribute = 'user.name';

    public static function form(Schema $schema): Schema
    {
        return CartForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CartsTable::configure($table);
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
            'index' => ListCarts::route('/'),
            'create' => CreateCart::route('/create'),
            'edit' => EditCart::route('/{record}/edit'),
        ];
    }
}
