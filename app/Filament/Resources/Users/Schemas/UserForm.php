<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\UserStatus;
use App\Models\Role;
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
                    ->unique(ignoreRecord: true)
                    ->required(),
                TextInput::make('phone')
                    ->tel(),
                TextInput::make('password')
                    ->minLength(8)
                    ->password()
                    ->revealable()
                    ->visibleOn('create')
                    ->required(),
                Select::make('role_id')
                    ->label('Role')
                    ->options(fn () => Role::query()->orderBy('name')->pluck('name', 'id'))
                    ->rules(['exists:roles,id'])
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
