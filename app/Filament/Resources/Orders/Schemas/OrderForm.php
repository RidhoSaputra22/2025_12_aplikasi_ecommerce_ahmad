<?php

namespace App\Filament\Resources\Orders\Schemas;

use Closure;
use App\Models\Role;
use App\Models\User;
use App\Models\Order;
use App\Models\UserRole;
use Filament\Actions\Action;
use Filament\Schemas\Schema;
use App\Models\ProductVariant;
use App\Models\ShipmentAddress;
use App\Models\ShipmentCourier;
use Filament\Forms\Components\Radio;
use Filament\Support\Enums\TextSize;
use Filament\Support\Icons\Heroicon;
use Filament\Forms\Components\Select;
use Filament\Support\Enums\FontWeight;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use App\Filament\Forms\Components\CardRadio;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use App\Filament\Resources\Users\Tables\UsersTable;
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

                        Repeater::make('items')
                            ->label('Produk dipesan')
                            ->required()
                            ->table([
                                TableColumn::make('Produk'),
                                TableColumn::make('Jumlah'),
                                TableColumn::make('Harga Satuan'),

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
                                    ->required()
                                    ->default(function () {
                                        // Mode Testing: Hanya jika APP_DEBUG=true
                                        if (config('app.debug')) {
                                            return ProductVariant::where('stock', '>', 0)->first()?->id;
                                        }
                                        return null;
                                    }),

                                TextInput::make('price')
                                    ->label('Harga Satuan'),
                                TextInput::make('quantity')
                                    ->label('Jumlah')
                                    ->numeric()
                                    ->minValue(1)
                                    ->required()
                                    ->default(config('app.debug') ? 1 : null) // Mode Testing
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
                            ->reorderable(false)
                            ->reactive()
                            ->live(onBlur: true)
                            ->afterStateHydrated(fn(Get $get, Set $set) => OrderFormUtils::calculateTotalAmount($get, $set))
                            ->afterStateUpdated(fn(Get $get, Set $set) => OrderFormUtils::calculateTotalAmount($get, $set)),

                        TextEntry::make('total_amount_display')
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
                            ->default('default')
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

                        TextEntry::make('shipping_cost')
                            ->label('Harga Pengiriman')
                            ->money('IDR')
                            ->size(TextSize::Large)
                            ->weight(FontWeight::Medium)
                            ->copyable()
                            ->copyMessage('Copied!')
                            ->copyMessageDuration(1500)
                        // ->reactive()





                    ])
                    ->columnSpanFull(),
                Section::make('Pembayaran')
                    ->description('Metode pembayaran')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('total_amount')
                            ->label('Total Harga')
                            ->reactive()
                            ->money('IDR')
                            ->size(TextSize::Large)
                            ->weight(FontWeight::Medium)
                            ->copyable()
                            ->copyMessage('Copied!')
                            ->copyMessageDuration(1500),


                        Select::make('payment_method')
                            ->label('Metode Pembayaran')
                            ->options([
                                'transfer' => 'Transfer Bank',
                                'ewallet'  => 'E-Wallet',
                                'cod'      => 'COD',
                            ])
                            ->required()
                            ->default(config('app.debug') ? 'transfer' : null) // Mode Testing
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),


                Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                        'shipped' => 'Shipped',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ])
                    ->default('pending')
                    ->required(),
                Select::make('payment_status')
                    ->options(['pending' => 'Pending', 'paid' => 'Paid', 'failed' => 'Failed'])
                    ->default('pending')
                    ->required(),
            ]);
    }
}