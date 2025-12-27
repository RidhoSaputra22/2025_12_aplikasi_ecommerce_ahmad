<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum OrderVendorStatus: string implements HasColor, HasIcon, HasLabel
{
    case Pending = 'pending';
    case Processed = 'processed';
    case Shipped = 'shipped';
    case Completed = 'completed';

    public function getLabel(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu',
            self::Processed => 'Diproses',
            self::Shipped => 'Dikirim',
            self::Completed => 'Selesai',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Processed => 'info',
            self::Shipped => 'primary',
            self::Completed => 'success',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Pending => 'heroicon-m-clock',
            self::Processed => 'heroicon-m-cog-6-tooth',
            self::Shipped => 'heroicon-m-truck',
            self::Completed => 'heroicon-m-check-circle',
        };
    }
}
