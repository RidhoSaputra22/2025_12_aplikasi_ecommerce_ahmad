<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\UserStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                TextInput::make('phone')
                    ->tel(),
                TextInput::make('password')
                    ->password()
                    ->visibleOn('create')
                    ->required(),
                DateTimePicker::make('email_verified_at'),
                Select::make('status')
                    ->options(UserStatus::class)
                    ->default('active')
                    ->required(),
                DateTimePicker::make('last_login_at'),
            ]);
    }
}
