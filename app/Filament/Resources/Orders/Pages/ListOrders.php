<?php

namespace App\Filament\Resources\Orders\Pages;

use Closure;
use App\Models\Role;
use App\Models\User;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Shipment;
use App\Models\UserRole;
use App\Models\OrderItem;
use App\Enums\OrderStatus;
use App\Models\OrderVendor;
use App\Enums\PaymentStatus;
use Filament\Actions\Action;
use Filament\Schemas\Schema;
use App\Enums\ShipmentStatus;
use App\Models\ProductVariant;
use App\Models\ShipmentAddress;
use App\Enums\OrderVendorStatus;

use App\Enums\OrderPaymentStatus;
use Filament\Actions\CreateAction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;
use Filament\Forms\Components\Radio;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Select;
use Illuminate\Support\Facades\Blade;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Wizard;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Forms\Components\CardRadio;
use Filament\Schemas\Components\Wizard\Step;
use App\Filament\Resources\Users\UserResource;
use Filament\Schemas\Components\Utilities\Get;
use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Resources\Users\Schemas\UserForm;
use Filament\Forms\Components\Repeater\TableColumn;

/**
 * ListOrders Page
 *
 * Halaman untuk menampilkan daftar orders dan membuat order baru melalui wizard
 * dengan langkah: pilih produk, pengiriman, dan pembayaran
 */
class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    /**
     * Mendapatkan aksi header yang tersedia di halaman list orders
     *
     * @return array
     */
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
                            ]),
                        Step::make('Pengiriman')
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
                                    ->default(3)
                                    ->reactive(),



                                CardRadio::make('shipping_address_id')
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
                                    ->extraAttributes([
                                        'class' => 'bg-white p-4 rounded-xl border border-red-500'
                                    ])
                                    ->required(),

                            ]),
                        Step::make('Pembayaran')
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
                    // dd($data);
                    try {
                        // Mulai transaksi database
                        DB::beginTransaction();

                        // Hitung total amount
                        $totalAmount = 0;
                        foreach ($data['items'] as $item) {
                            $variant = ProductVariant::with('product')->find($item['product_variant_id']);
                            $itemTotal = $variant->price * $item['quantity'];
                            $totalAmount += $itemTotal;
                        }

                        $totalAmount += $data['shipping_cost'];

                        // Buat order baru
                        $order = Order::create([
                            'user_id' => $data['user_id'],
                            'total_amount' => $totalAmount,
                            'status' => OrderStatus::Pending,
                            'payment_status' => OrderPaymentStatus::Pending
                        ]);

                        // Kelompokkan items berdasarkan vendor
                        $vendorItems = [];
                        foreach ($data['items'] as $item) {
                            $variant = ProductVariant::with('product.vendor')->find($item['product_variant_id']);
                            $vendorId = $variant->product->vendor_id;

                            if (!isset($vendorItems[$vendorId])) {
                                $vendorItems[$vendorId] = [];
                            }

                            $vendorItems[$vendorId][] = [
                                'variant' => $variant,
                                'quantity' => $item['quantity']
                            ];
                        }

                        // Buat OrderItem dan OrderVendor untuk setiap vendor
                        foreach ($vendorItems as $vendorId => $items) {
                            $vendorTotal = 0;

                            // Hitung total vendor terlebih dahulu
                            foreach ($items as $item) {
                                $itemTotal = $item['variant']->price * $item['quantity'];
                                $vendorTotal += $itemTotal;
                            }

                            // Buat order vendor terlebih dahulu
                            $orderVendor = OrderVendor::create([
                                'order_id' => $order->id,
                                'vendor_id' => $vendorId,
                                'subtotal' => $vendorTotal,
                                'status' => OrderVendorStatus::Pending
                            ]);

                            // Kemudian buat order items untuk vendor ini
                            foreach ($items as $item) {
                                $itemTotal = $item['variant']->price * $item['quantity'];

                                // Buat order item
                                OrderItem::create([
                                    'order_vendor_id' => $orderVendor->id,
                                    'product_variant_id' => $item['variant']->id,
                                    'quantity' => $item['quantity'],
                                    'price' => $item['variant']->price,
                                    'total' => $itemTotal
                                ]);

                                // Kurangi stok
                                $item['variant']->decrement('stock', $item['quantity']);
                            }

                            // Buat shipment
                            $shipment = Shipment::create([
                                'order_vendor_id' => $orderVendor->id,
                                'shipment_address_id' => $data['shipping_address_id'],
                                'courier' => $data['courier'],
                                'service' => $data['service'],
                                'shipping_cost' => $data['shipping_cost'],
                                'status' => ShipmentStatus::Pending
                            ]);

                            // Buat shipment address jika diperlukan
                            // (Asumsikan alamat sudah ada, jadi tidak dibuat ulang di sini)
                            ShipmentAddress::find($data['shipping_address_id']);
                        }

                        // Buat payment record
                        Payment::create([
                            'order_id' => $order->id,
                            'amount' => $totalAmount,
                            'payment_method' => $data['payment_method'],
                            'status' => PaymentStatus::Pending,
                        ]);

                        DB::commit();

                        // Tampilkan notifikasi sukses
                        Notification::make()
                            ->title('Order Berhasil Dibuat')
                            ->body('Order #' . $order->order_number . ' telah dibuat dengan total Rp ' . number_format($totalAmount, 0, ',', '.'))
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        DB::rollBack();

                        Notification::make()
                            ->title('Gagal Membuat Order')
                            ->body('Terjadi kesalahan: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
