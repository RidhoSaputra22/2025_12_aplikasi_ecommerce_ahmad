<?php

namespace App\Filament\Resources\ShipParties\Schemas;

use App\Enums\UserStatus;
use App\Models\ShipmentCourier;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class ShipPartyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Data Akun')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama')
                            ->required(),
                        TextInput::make('email')
                            ->label('Email address')
                            ->email()
                            ->unique(ignoreRecord: true)
                            ->required(),
                        TextInput::make('phone')
                            ->label('Phone')
                            ->tel(),
                        TextInput::make('password')
                            ->label('Password')
                            ->minLength(8)
                            ->password()
                            ->revealable()
                            ->visibleOn('create')
                            ->required(),
                        Select::make('status')
                            ->options(UserStatus::class)
                            ->default('active')
                            ->required(),
                        DateTimePicker::make('email_verified_at'),
                        DateTimePicker::make('last_login_at'),
                    ])
                    ->columns(2),
                Section::make('Kaitkan Ekspedisi')
                    ->description('Pilih jasa ekspedisi yang dikelola akun pihak kapal ini.')
                    ->schema([
                        Select::make('shipment_courier_id')
                            ->label('Jasa Expedisi')
                            ->options(function (?string $operation, ?object $record) {
                                return ShipmentCourier::query()
                                    ->when($operation === 'edit' && $record, function (Builder $query) use ($record) {
                                        $query->where(function (Builder $nested) use ($record) {
                                            $nested->whereNull('user_id')
                                                ->orWhere('user_id', $record->getKey());
                                        });
                                    }, fn (Builder $query) => $query->whereNull('user_id'))
                                    ->orderBy('name')
                                    ->orderBy('service')
                                    ->get()
                                    ->mapWithKeys(fn (ShipmentCourier $courier) => [
                                        $courier->id => "{$courier->name} - {$courier->service} ({$courier->code})",
                                    ]);
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->helperText('Satu akun pihak kapal hanya boleh terhubung ke satu ekspedisi.'),
                    ]),
            ]);
    }
}
