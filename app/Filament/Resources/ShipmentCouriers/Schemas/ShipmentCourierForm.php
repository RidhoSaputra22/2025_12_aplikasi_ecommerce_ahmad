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
                    ->label('Nama')
                    ->required(),
                TextInput::make('code')
                    ->label('Kode')
                    ->required(),
                TextInput::make('service')
                    ->label('Layanan')
                    ->required(),
                TextInput::make('price')
                    ->label('Harga')
                    ->required()
                    ->numeric()
                    ->prefix('Rp. '),
            ]);
    }
}
