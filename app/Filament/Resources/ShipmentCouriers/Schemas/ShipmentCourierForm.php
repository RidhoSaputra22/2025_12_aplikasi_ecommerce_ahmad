<?php

namespace App\Filament\Resources\ShipmentCouriers\Schemas;

use App\Models\User;
use Filament\Forms\Components\Select;
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
                Select::make('user_id')
                    ->label('Akun Pihak Kapal')
                    ->options(function () {
                        return User::query()
                            ->whereHas('role', fn ($query) => $query->where('name', 'pihak_kapal'))
                            ->orderBy('name')
                            ->pluck('name', 'id');
                    })
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->unique(ignoreRecord: true)
                    ->helperText('Opsional. Jika diisi, ekspedisi ini dapat login sebagai portal pihak kapal.'),
            ]);
    }
}
