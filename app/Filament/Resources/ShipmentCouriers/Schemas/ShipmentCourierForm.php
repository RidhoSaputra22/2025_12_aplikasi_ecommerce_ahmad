<?php

namespace App\Filament\Resources\ShipmentCouriers\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ShipmentCourierForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('code')
                    ->required(),
                TextInput::make('service')
                    ->required(),
                TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->prefix('$'),
            ]);
    }
}
