<?php

namespace App\Filament\Vendor\Resources\Carts\Schemas;

use Closure;
use App\Models\Role;
use Filament\Schemas\Schema;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Forms\Components\Repeater\TableColumn;
use App\Filament\Vendor\Resources\Orders\Schemas\OrderFormUtils;

class CartForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->label('Pelanggan')
                    ->options(function () {
                        // return users who have role customer
                        return Role::where('name', 'customer')->first()
                            ->users()
                            ->pluck('name', 'user_id');
                    })

                    ->required(),
                Section::make('Pilih Produk')
                    ->description('Tambahkan produk ke dalam order')
                    ->schema([
                        Repeater::make('items')
                            ->label('Produk dipesan')
                            ->required()
                            ->table([
                                TableColumn::make('Produk'),
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
                                        // return product variant by vendor
                                        return ProductVariant::with('product')
                                            ->whereHas('product', function ($query) use ($get) {
                                                $vendorId = Auth::user()->vendor?->id;
                                                if ($vendorId) {
                                                    $query->where('vendor_id', $vendorId);
                                                }
                                            })
                                            ->where('stock', '>', 0)
                                            ->whereNotIn('id', $selectedVariants)
                                            ->get()
                                            ->mapWithKeys(fn($v) => [
                                                $v->id => $v->product->name . ' - ' . $v->variant_name . ' - Rp . ' . number_format($v->price, 2, ',', '.')
                                            ]);
                                    })
                                    ->searchable()
                                    ->reactive()
                                    ->required(),


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

                            ->afterStateHydrated(fn(Get $get, Set $set) => self::calculateTotalAmount($get, $set))
                            ->afterStateUpdated(fn(Get $get, Set $set) => self::calculateTotalAmount($get, $set)),

                        TextInput::make('total_amount_display')
                            ->label('Total Harga Produk')
                            ->disabled(),


                    ])
                    ->columnSpanFull(),

            ]);
    }

    private static function calculateTotalAmount(Get $get, Set $set): void
    {
        $items = $get('items') ?? [];
        $total = 0;
        foreach ($items as $item) {
            $variant = ProductVariant::find($item['product_variant_id']);
            if ($variant) {
                $total += $variant->price * $item['quantity'];
            }
        }


        $set('total_amount_display', 'Rp . ' . number_format($total, 2, ',', '.'));
    }
}
