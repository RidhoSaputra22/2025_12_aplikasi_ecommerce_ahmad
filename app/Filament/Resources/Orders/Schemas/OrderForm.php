<?php

namespace App\Filament\Resources\Orders\Schemas;

use Closure;
use App\Models\Role;
use App\Models\Vendor;
use App\Enums\OrderStatus;
use App\Enums\OrderPaymentStatus;
use Filament\Schemas\Schema;
use App\Models\ProductVariant;
use App\Models\ShipmentCourier;
use Filament\Support\Enums\TextSize;
use Filament\Forms\Components\Select;
use Filament\Support\Enums\FontWeight;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use App\Filament\Forms\Components\CardRadio;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Forms\Components\Repeater\TableColumn;
use App\Filament\Resources\Orders\Schemas\OrderFormUtils;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {


        return $schema
            ->components([
                Section::make('Pilih Produk')
                    ->description('Tambahkan produk ke dalam order')
                    ->schema([
                        Select::make('vendor_id')
                            ->label('Vendor')
                            ->options(function () {
                                return Vendor::all()
                                    ->pluck('store_name', 'id')
                                    ->toArray() ?? [];
                            })
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                // Reset items when vendor changes
                                $set('items', []);
                                $set('total_amount', 0);
                                $set('total_amount_display', "Rp 0,00");
                                $set('total_amount_pembayaran_display', "Rp 0,00");
                            }),

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
                                    ->disabled(fn(Get $get) => !$get('../../vendor_id'))
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
                                                $vendorId = $get('../../vendor_id');
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

                            ->afterStateHydrated(fn(Get $get, Set $set) => OrderFormUtils::calculateTotalAmount($get, $set))
                            ->afterStateUpdated(fn(Get $get, Set $set) => OrderFormUtils::calculateTotalAmount($get, $set)),

                        TextInput::make('total_amount_display')
                            ->label('Total Harga Produk')
                            ->disabled(),


                    ])
                    ->columnSpanFull(),
                Section::make('Pengiriman')
                    ->description('Alamat & pengiriman order')
                    ->columns(3)
                    ->schema([
                        Select::make('user_id')
                            ->label('Customer')
                            ->options(function () {
                                return Role::where('name', 'customer')
                                    ->first()
                                    ?->users()
                                    ->pluck('name', 'user_id');
                            })
                            ->createOptionModalHeading('Buat Customer Baru')
                            ->createOptionForm(OrderFormUtils::customerSchema())
                            ->columns(3)
                            ->createOptionUsing(fn(array $data) => OrderFormUtils::createCustomerData($data))
                            ->searchable()
                            ->required()

                            ->reactive(),

                        CardRadio::make('shipment_address_id')
                            ->columnSpanFull()

                            ->label('Alamat Pengiriman')
                            ->label('Alamat Pengiriman')
                            ->options(function (Get $get) {
                                $userId = $get('user_id');
                                if (!$userId) {
                                    return [];
                                }

                                $user = \App\Models\User::with('addresses')->find($userId);
                                $options = [];
                                foreach ($user->addresses as $address) {
                                    $options[$address->id] = $address->city;
                                }
                                return $options;
                            })
                            ->descriptions(function (Get $get) {
                                $userId = $get('user_id');
                                if (!$userId) {
                                    return [];
                                }

                                $user = \App\Models\User::with('addresses')->find($userId);
                                $options = [];
                                foreach ($user->addresses as $address) {
                                    $options[$address->id] = "{$address->address}, {$address->district}, {$address->city}, {$address->province}, {$address->postal_code}";
                                }
                                return $options;
                            })

                            ->reactive()

                            ->required(),

                        Select::make('shipment_courier_id')
                            ->label('Kurir Pengiriman')
                            ->options(function () {
                                return ShipmentCourier::all()
                                    ->mapWithKeys(fn($c) => [
                                        $c->id => $c->name . ' - ' . $c->service . ' (Rp ' . number_format($c->price, 2, ',', '.') . ')'
                                    ]);
                            })
                            ->required()
                            ->reactive()
                            ->live()
                            ->afterStateHydrated(fn(Get $get, Set $set, $state) => OrderFormUtils::calculateShippingCost($get, $set, $state))
                            ->afterStateUpdated(fn(Get $get, Set $set, $state) => OrderFormUtils::calculateShippingCost($get, $set, $state)),

                        TextInput::make('shipping_cost_display')
                            ->label('Harga Pengiriman')

                            ->disabled()
                        // ->reactive()





                    ])
                    ->columnSpanFull(),
                Section::make('Pembayaran')
                    ->description('Metode pembayaran')
                    ->columns(2)
                    ->schema([
                        TextInput::make('total_amount_pembayaran_display')
                            ->label('Total Harga')
                            ->reactive()
                            ->disabled(),


                        Select::make('payment_method')
                            ->label('Metode Pembayaran')
                            ->options([
                                'transfer' => 'Transfer Bank',
                                'ewallet'  => 'E-Wallet',
                                'cod'      => 'COD',
                            ])
                            ->required()

                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),


                Select::make('status')
                    ->options(OrderStatus::class)

                    ->required(),
                Select::make('payment_status')
                    ->options(OrderPaymentStatus::class)

                    ->required(),
            ]);
    }
}