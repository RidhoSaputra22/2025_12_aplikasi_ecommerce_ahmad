<?php

namespace App\Filament\Resources\Orders\Pages;

use Closure;
use App\Models\Role;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Shipment;
use App\Models\OrderItem;
use App\Models\OrderVendor;
use Filament\Actions\Action;
use App\Models\ProductVariant;
use Filament\Actions\CreateAction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Select;
use Illuminate\Support\Facades\Blade;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Wizard;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
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
                            'user_id' => Auth::id(),
                            'total_amount' => $totalAmount,
                            'status' => 'pending',
                            'payment_status' => 'unpaid'
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

                            foreach ($items as $item) {
                                $itemTotal = $item['variant']->price * $item['quantity'];
                                $vendorTotal += $itemTotal;

                                // Buat order item
                                OrderItem::create([
                                    'order_id' => $order->id,
                                    'product_variant_id' => $item['variant']->id,
                                    'quantity' => $item['quantity'],
                                    'price' => $item['variant']->price,
                                    'total' => $itemTotal
                                ]);

                                // Kurangi stok
                                $item['variant']->decrement('stock', $item['quantity']);
                            }

                            // Buat order vendor
                            $orderVendor = OrderVendor::create([
                                'order_id' => $order->id,
                                'vendor_id' => $vendorId,
                                'total_amount' => $vendorTotal,
                                'status' => 'pending'
                            ]);

                            // Buat shipment
                            Shipment::create([
                                'order_vendor_id' => $orderVendor->id,
                                'shipping_cost' => $data['shipping_cost'],
                                'status' => 'pending'
                            ]);
                        }

                        // Buat payment record
                        Payment::create([
                            'order_id' => $order->id,
                            'amount' => $totalAmount,
                            'method' => $data['payment_method'],
                            'status' => 'pending',
                            'receiver_name' => $data['receiver_name'],
                            'phone' => $data['phone'],
                            'province' => $data['province'],
                            'city' => $data['city'],
                            'district' => $data['district'],
                            'postal_code' => $data['postal_code'],
                            'address' => $data['address'],
                            'delivery_date' => $data['delivery_date']
                        ]);

                        DB::commit();

                        // Tampilkan notifikasi sukses
                        Notification::make()
                            ->title('Order Berhasil Dibuat')
                            ->body('Order #' . $order->order_number . ' telah dibuat dengan total Rp ' . number_format($totalAmount, 0, ',', '.'))
                            ->success()
                            ->send();

                        // Redirect ke halaman order
                        redirect('/admin/orders/' . $order->id);
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
