<?php

namespace App\Filament\Vendor\Resources\Orders;

use BackedEnum;
use App\Models\Order;
use App\Models\Vendor;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Vendor\Resources\Orders\Pages\EditOrder;
use App\Filament\Vendor\Resources\Orders\Pages\ListOrders;
use App\Filament\Vendor\Resources\Orders\Pages\CreateOrder;
use App\Filament\Vendor\Resources\Orders\Schemas\OrderForm;
use App\Filament\Vendor\Resources\Orders\Tables\OrdersTable;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'vendor';

    public static function form(Schema $schema): Schema
    {
        return OrderForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OrdersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $userId = Auth::user()->id;
        return parent::getEloquentQuery()
            ->whereHas('orderVendors', function (Builder $query) use ($userId) {
                $query->whereHas('vendor', function (Builder $vendorQuery) use ($userId) {
                    $vendorQuery->where('user_id', $userId);
                });
            });
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOrders::route('/'),
            'create' => CreateOrder::route('/create'),
            'edit' => EditOrder::route('/{record}/edit'),
        ];
    }
}
