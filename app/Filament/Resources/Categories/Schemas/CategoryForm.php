<?php

namespace App\Filament\Resources\Categories\Schemas;

use Closure;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama Kategori')
                    ->required()
                    ->unique(ignoreRecord: true),
                // subcategory
                Repeater::make('subCategory')
                    ->relationship('children')
                    ->columnSpanFull()
                    ->reorderable(false)
                    ->deletable(true)
                    ->defaultItems(0)
                    ->addActionAlignment(Alignment::Start)
                    ->addActionLabel('Tambah Sub Kategori')
                    ->label('Sub Kategori')
                    ->simple(TextInput::make('name')
                        ->label('Nama Sub Kategori')
                        ->unique(
                            ignoreRecord: true,
                        )
                        ->rules([
                            fn(Get $get): Closure => function (string $attribute, $value, Closure $fail) use ($get) {
                                $parentName = $get('../../name');
                                $thisName = $value;

                                // Unique subcategory name under the same parent
                                if ($parentName == $thisName) {
                                    $fail('Sub Kategori tidak boleh sama dengan Kategori Induk.');
                                }
                            }
                        ])),
            ]);
    }
}