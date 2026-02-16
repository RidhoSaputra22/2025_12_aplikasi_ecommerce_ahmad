<?php

namespace App\Filament\Resources\AdminBankAccounts\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class AdminBankAccountForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('bank_name')
                    ->label('Nama Bank')
                    ->placeholder('Contoh: BCA, BRI, Mandiri')
                    ->required()
                    ->maxLength(100),

                TextInput::make('account_number')
                    ->label('Nomor Rekening')
                    ->placeholder('Contoh: 1234567890')
                    ->required()
                    ->maxLength(50),

                TextInput::make('account_holder')
                    ->label('Atas Nama')
                    ->placeholder('Nama pemilik rekening')
                    ->required()
                    ->maxLength(100),

                Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true)
                    ->helperText('Hanya rekening aktif yang ditampilkan ke customer.'),
            ]);
    }
}
