<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum ShipmentStatus: string implements HasColor, HasIcon, HasLabel
{
    case Pending = 'pending';
    case Shipped = 'shipped';
    case Delivered = 'delivered';

    public function getLabel(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu Pengiriman',
            self::Shipped => 'Dikirim',
            self::Delivered => 'Tiba di Tujuan',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Shipped => 'info',
            self::Delivered => 'success',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Pending => 'heroicon-m-clock',
            self::Shipped => 'heroicon-m-truck',
            self::Delivered => 'heroicon-m-check-circle',
        };
    }
}
