<?php

namespace App\Filament\Vendor\Resources\Shipments\Schemas;

use App\Models\Role;
use Filament\Schemas\Schema;
use App\Enums\ShipmentStatus;
use App\Models\ShipmentCourier;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use App\Filament\Forms\Components\CardRadio;
use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use App\Filament\Vendor\Resources\Orders\Schemas\OrderFormUtils;

class ShipmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detail Pengiriman')
                    ->description('Detail mengenai pengiriman pesanan.')
                    ->schema([

                        TextInput::make('tracking_number'),
                        Select::make('status')
                            ->options(ShipmentStatus::class)
                            ->default('pending')
                            ->required(),
                        DateTimePicker::make('shipped_at'),
                        DateTimePicker::make('delivered_at'),
                    ])
                    ->columnSpanFull()
                    ->columns(3),

                Section::make('Detail Penerima')
                    ->description('Informasi mengenai penerima pengiriman.')
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
                    ->columnSpanFull()
                    ->columns(3),


            ]);
    }
}
