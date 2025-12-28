<?php

namespace App\Filament\Resources\Products\RelationManagers;

use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductVariantRelationManager extends RelationManager
{
    protected static string $relationship = 'productVariants';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('variant_name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('sku')
                    ->required()
                    ->maxLength(100),
                TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->prefix('Rp'),
                TextInput::make('stock')
                    ->required()
                    ->numeric(),
            ])
            ->columns(3);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('variant_name')
            ->columns([
                TextColumn::make('variant_name')
                    ->searchable(),
                TextColumn::make('sku')
                    ->label('SKU'),
                TextColumn::make('price')
                    ->label('Harga')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('stock')
                    ->label('Stok')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
