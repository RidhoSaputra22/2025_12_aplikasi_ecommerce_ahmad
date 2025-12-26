<?php

namespace App\Filament\Resources\Orders\Pages;

use Closure;
use App\Models\Role;
use Filament\Actions\Action;
use App\Models\ProductVariant;
use Filament\Actions\CreateAction;
use Illuminate\Support\HtmlString;
use Filament\Forms\Components\Select;
use Illuminate\Support\Facades\Blade;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Wizard;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Components\Utilities\Get;
use App\Filament\Resources\Orders\OrderResource;
use Filament\Forms\Components\Repeater\TableColumn;


class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('create')
                ->label('Buat Order Baru')
                ->button()
                ->icon('heroicon-o-plus')
                ->schema([
                    Wizard::make([
                        Step::make('Pilih Produk')
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

                                                return ProductVariant::with('product')
                                                    ->where('stock', '>', 0)
                                                    ->whereNotIn('id', $selectedVariants)
                                                    ->get()
                                                    ->mapWithKeys(fn($v) => [
                                                        $v->id => $v->product->name . ' - ' . $v->variant_name
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
                                    ->defaultItems(1)
                                    ->columnSpanFull()
                                    ->columns(2)
                                    ->reorderable(false),
                            ]),
                        Step::make('Pengiriman')
                            ->description('Alamat & pengiriman order')
                            ->columns(3)
                            ->schema([

                                TextInput::make('receiver_name')
                                    ->label('Nama Penerima')
                                    ->required(),

                                TextInput::make('phone')
                                    ->label('No. Telepon')
                                    ->required(),

                                DatePicker::make('delivery_date')
                                    ->label('Tanggal Pengiriman')
                                    ->default(now())
                                    ->required(),

                                TextInput::make('province')
                                    ->required(),

                                TextInput::make('city')
                                    ->required(),

                                TextInput::make('district')
                                    ->required(),

                                TextInput::make('postal_code')
                                    ->required(),

                                Textarea::make('address')
                                    ->label('Alamat Lengkap')
                                    ->columnSpanFull()
                                    ->required(),
                            ]),
                        Step::make('Pembayaran')
                            ->description('Metode pembayaran')
                            ->schema([

                                TextInput::make('shipping_cost')
                                    ->label('Biaya Pengiriman')
                                    ->numeric()
                                    ->prefix('Rp ')
                                    ->default(10000)
                                    ->required(),

                                Select::make('payment_method')
                                    ->label('Metode Pembayaran')
                                    ->options([
                                        'transfer' => 'Transfer Bank',
                                        'ewallet'  => 'E-Wallet',
                                        'cod'      => 'COD',
                                    ])
                                    ->required(),
                            ]),
                    ])

                        ->skippable(false)
                        ->submitAction(
                            new HtmlString(
                                Blade::render(
                                    '<x-filament::button type="submit" size="sm">Buat Order</x-filament::button>'
                                )
                            )
                        )




                ])
                ->modalWidth('7xl')

                ->action(function (array $data) {
                    // Handle order creation logic here

                }),
        ];
    }
}