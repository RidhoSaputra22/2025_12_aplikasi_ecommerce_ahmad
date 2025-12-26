<?php

namespace App\Filament\Resources\Shipments\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ShipmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('order_vendor_id')
                    ->required()
                    ->numeric(),
                TextInput::make('courier')
                    ->required(),
                TextInput::make('service')
                    ->required(),
                TextInput::make('tracking_number'),
                TextInput::make('shipping_cost')
                    ->required()
                    ->numeric()
                    ->prefix('$'),
                Select::make('status')
                    ->options(['pending' => 'Pending', 'shipped' => 'Shipped', 'delivered' => 'Delivered'])
                    ->default('pending')
                    ->required(),
                DateTimePicker::make('shipped_at'),
                DateTimePicker::make('delivered_at'),
            ]);
    }
}
