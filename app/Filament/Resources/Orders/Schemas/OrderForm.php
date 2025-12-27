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
use Filament\Forms\Components\Radio;
use Filament\Support\Icons\Heroicon;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use App\Filament\Forms\Components\CardRadio;
use Filament\Schemas\Components\Utilities\Get;
use App\Filament\Resources\Users\Tables\UsersTable;
use Filament\Forms\Components\Repeater\TableColumn;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {


        return $schema
            ->components([
                Section::make('Pilih Produk')
                    ->description('Tambahkan produk ke dalam order')
                    ->schema([

                        Repeater::make('orderItems')
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
                                    ->required()
                                    ->default(function () {
                                        // Mode Testing: Hanya jika APP_DEBUG=true
                                        if (config('app.debug')) {
                                            return ProductVariant::where('stock', '>', 0)->first()?->id;
                                        }
                                        return null;
                                    }),

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
                            ->reorderable(false),
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
                            ->createOptionForm([
                                Section::make('Detail Customer')
                                    ->label('Detail Customer')
                                    ->description('Informasi detail customer yang melakukan order')
                                    ->schema([
                                        TextInput::make('name')
                                            ->required(),
                                        TextInput::make('email')
                                            ->label('Email address')
                                            ->email()
                                            ->required(),
                                        TextInput::make('phone')
                                            ->tel(),
                                        TextInput::make('password')
                                            ->required(),
                                    ])
                                    ->columns(2),

                                Section::make('Alamat Pengiriman')
                                    ->label('Alamat Pengiriman')
                                    ->description('Informasil Alamat Pengiriman')
                                    ->schema([
                                        TextInput::make('province')
                                            ->label('Provinsi')
                                            ->required(),
                                        TextInput::make('city')
                                            ->label('Kota/Kabupaten')
                                            ->required(),
                                        TextInput::make('district')
                                            ->label('Kecamatan')
                                            ->required(),
                                        TextInput::make('postal_code')
                                            ->label('Kode Pos')
                                            ->required(),
                                        TextInput::make('address')
                                            ->label('Alamat Lengkap')
                                            ->required(),
                                    ])

                                    ->columns(3),

                            ])
                            ->columns(3)
                            ->createOptionUsing(function (array $data): int {
                                $user = User::create([
                                    'name' => $data['name'],
                                    'email' => $data['email'],
                                    'phone' => $data['phone'],
                                    'password' => bcrypt($data['password']),
                                ]);

                                UserRole::create([
                                    'user_id' => $user->id,
                                    'role_id' => Role::where('name', 'customer')->first()->id,
                                ]);

                                ShipmentAddress::create([
                                    'user_id' => $user->id,
                                    'province' => $data['province'],
                                    'city' => $data['city'],
                                    'district' => $data['district'],
                                    'postal_code' => $data['postal_code'],
                                    'address' => $data['address'],
                                ]);

                                return $user->id;
                            })
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

                    ])
                    ->columnSpanFull(),
                Section::make('Pembayaran')
                    ->description('Metode pembayaran')
                    ->columns(2)
                    ->schema([

                        Select::make('courier')

                            ->label('Kurir Pengiriman')
                            ->options([
                                'JNE' => 'JNE',
                                'J&T' => 'J&T Express',
                                'SiCepat' => 'SiCepat',
                                'AnterAja' => 'AnterAja',
                                'Ninja' => 'Ninja Xpress',
                            ])
                            ->required()
                            ->default(config('app.debug') ? 'JNE' : null), // Mode Testing

                        Select::make('service')
                            ->label('Layanan')
                            ->options([
                                'REG' => 'Regular',
                                'YES' => 'Yakin Esok Sampai',
                                'OKE' => 'Ongkos Kirim Ekonomis',
                            ])
                            ->required()
                            ->default(config('app.debug') ? 'REG' : null), // Mode Testing

                        TextInput::make('shipping_cost')
                            ->label('Biaya Pengiriman')
                            ->numeric()
                            ->prefix('Rp ')
                            ->default(10000)
                            ->required()
                            ->columnSpanFull(),

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
