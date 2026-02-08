<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\ProductVariant;
use App\Models\ShipmentCourier;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

class OrderFormUtils
{
    // calculate total amount including shipping cost
    public static function calculateTotalAmount(Get $get, Set $set)
    {
        $items = $get('items') ?? [];
        $total = 0;
        foreach ($items as $item) {
            $variant = ProductVariant::find($item['product_variant_id']);
            if ($variant) {
                $total += $variant->price * $item['quantity'];
            }
        }

        $courierId = $get('shipment_courier_id');
        $shippingCost = 0;

        if ($courierId) {
            $courier = ShipmentCourier::find($courierId);
            $shippingCost = $courier ? $courier->price : 0;
        }

        $set('total_amount', $total + $shippingCost);
        $set('total_amount_pembayaran_display', 'Rp '.number_format((int) ($total + $shippingCost), 2, ',', '.'));
        $set('shipping_cost', $shippingCost);
        $set('shipping_cost_display', 'Rp '.number_format((int) $shippingCost, 2, ',', '.'));
        $set('total_amount_display', 'Rp '.number_format((int) $total, 2, ',', '.'));
    }

    // calculate shipping cost based on courier and service
    public static function calculateShippingCost(Get $get, Set $set, $state)
    {
        $courier = ShipmentCourier::find($state);
        if ($courier) {
            $set('shipping_cost', $courier->price);
            $set('shipping_cost_display', 'Rp '.number_format((int) $courier->price, 2, ',', '.'));

            // Update total amount
            $items = $get('items') ?? [];
            $total = 0;
            foreach ($items as $item) {
                $variant = ProductVariant::find($item['product_variant_id']);
                if ($variant) {
                    $total += $variant->price * $item['quantity'];
                }
            }
            $set('total_amount_display', 'Rp '.number_format((int) $total, 2, ',', '.'));
            $set('total_amount_pembayaran_display', 'Rp '.number_format((int) ($total + $courier->price), 2, ',', '.'));
            $set('total_amount', $total += $courier->price);
        }
    }

    // customer schema
    public static function customerSchema(): array
    {
        return [
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
                        ->minLength(8)
                        ->password()
                        ->revealable()
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

        ];
    }

    // function to create customer and shipment address, return user id
    public static function createCustomerData(array $data): int
    {
        $user = \App\Models\User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => bcrypt($data['password']),
        ]);

        $address = \App\Models\ShipmentAddress::create([
            'user_id' => $user->id,
            'province' => $data['province'],
            'city' => $data['city'],
            'district' => $data['district'],
            'postal_code' => $data['postal_code'],
            'address' => $data['address'],
        ]);

        return $user->id;
    }
}
