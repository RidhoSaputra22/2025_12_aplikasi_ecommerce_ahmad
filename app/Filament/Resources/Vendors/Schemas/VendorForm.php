<?php

namespace App\Filament\Resources\Vendors\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;

class VendorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Logo and Banner Vendor')
                    ->description('Unggah logo dan banner untuk toko vendor.')
                    ->schema([
                        FileUpload::make('logo')
                            ->label('Logo')
                            ->image()
                            ->required()
                            ->columnSpan(1),
                        FileUpload::make('banner')
                            ->label('Banner')
                            ->image()
                            ->required()
                            ->columnSpan(4),
                    ])
                    ->columns(5)
                    ->columnSpanFull(),
                Section::make('Detail Vendor')
                    ->description('Atur detail informasi vendor di sini.')
                    ->schema([
                        Select::make('user_id')
                            ->relationship('user', 'name')
                            ->required(),
                        TextInput::make('store_name')
                            ->required(),

                        Textarea::make('description')
                            ->columnSpanFull(),

                        Toggle::make('is_verified')
                            ->required(),
                        TextInput::make('rating')
                            ->required()
                            ->numeric()
                            ->default(0.0),
                        Select::make('status')
                            ->options(['active' => 'Active', 'inactive' => 'Inactive', 'banned' => 'Banned'])
                            ->default('active')
                            ->required(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

            ]);
    }
}
