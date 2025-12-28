<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Enums\ProductStatus;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('vendor_id')
                    ->label('Vendor')
                    ->relationship('vendor', 'store_name')
                    ->required(),
                Select::make('category_id')
                    ->label('Kategori')
                    ->relationship('category', 'name')
                    ->required(),
                TextInput::make('name')
                    ->label("Nama Produk")
                    ->required(),

                Textarea::make('description')
                    ->label("Deskripsi Produk")
                    ->columnSpanFull(),
                TextInput::make('price')
                    ->label("Harga Produk")
                    ->required()
                    ->numeric()
                    ->prefix('Rp'),
                TextInput::make('weight')
                    ->label("Berat Produk")
                    ->required()
                    ->numeric()
                    ->suffix('gram')
                    ->default(0),
                Select::make('status')
                    ->label("Status Produk")
                    ->options(ProductStatus::class)
                    ->default('draft')
                    ->required(),
            ]);
    }
}
