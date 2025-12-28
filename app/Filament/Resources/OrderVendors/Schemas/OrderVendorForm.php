<?php

namespace App\Filament\Resources\OrderVendors\Schemas;

use Closure;
use App\Models\Vendor;
use Filament\Schemas\Schema;
use App\Models\ProductVariant;
use App\Enums\OrderVendorStatus;
use Filament\Support\Enums\TextSize;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Select;
use Filament\Support\Enums\FontWeight;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Forms\Components\Repeater\TableColumn;

class OrderVendorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Pilih Produk')
                    ->description('Tambahkan produk ke dalam order')
                    ->schema([

                        Repeater::make('order_items')
                            ->relationship('orderItems')
                            ->label('Produk dipesan')
                            ->required()
                            ->table([
                                TableColumn::make('Produk'),
                                TableColumn::make('Harga Satuan'),
                                TableColumn::make('Jumlah'),
                            ])
                            ->schema([
                                Select::make('product_variant_id')
                                    ->label('Produk')

                                    ->options(function (Get $get) {
                                        $selectedVariants = collect($get('../../items'))
                                            ->pluck('product_variant_id')
                                            ->filter()
                                            ->toArray();
                                        $current = $get('product_variant_id');
                                        if ($current) {
                                            $selectedVariants = array_diff($selectedVariants, [$current]);
                                        }
                                        return ProductVariant::whereHas('product', function ($query) {
                                            $query->where('vendor_id', 2);
                                        })
                                            ->whereNotIn('id', $selectedVariants)
                                            ->get()
                                            ->pluck('name', 'id') ?? [];
                                    })
                                    ->searchable()
                                    ->reactive()
                                    ->required()
                                    ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                        $variant = ProductVariant::find($state);
                                        $set('price', "99999");
                                    }),

                                TextEntry::make('price')
                                    ->label('Harga Satuan')
                                    ->money('IDR')
                                    ->reactive()
                                    ->alignCenter(),
                                TextInput::make('quantity')
                                    ->label('Jumlah')
                                    ->numeric()
                                    ->minValue(1)
                                    ->required()

                                    ->rules([
                                        function (Get $get) {
                                            return function (string $attribute, mixed $value, Closure $fail) use ($get) {
                                                $variant = ProductVariant::find($get('product_variant_id'));

                                                if (!$variant || $variant->stock < $value) {
                                                    $fail('Stok tidak mencukupi. Stok tersedia: ' . ($variant->stock ?? 0));
                                                }
                                            };
                                        }
                                    ]),
                            ])

                            ->columnSpanFull()
                            ->columns(2)
                            ->reorderable(false)
                            ->reactive()
                            ->live(onBlur: true),

                        TextEntry::make('order.total_amount')
                            ->label('Total')
                            ->reactive()
                            ->money('IDR')
                            ->size(TextSize::Large)
                            ->weight(FontWeight::Medium)
                            ->copyable()
                            ->copyMessage('Copied!')
                            ->copyMessageDuration(1500),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}