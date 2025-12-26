<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum VendorWalletTransactionType: string implements HasColor, HasIcon, HasLabel
{
    case Credit = 'credit';
    case Debit = 'debit';

    public function getLabel(): string
    {
        return match($this) {
            self::Credit => 'Pemasukan',
            self::Debit => 'Pengeluaran',
        };
    }

    public function getColor(): string|array|null
    {
        return match($this) {
            self::Credit => 'success',
            self::Debit => 'danger',
        };
    }

    public function getIcon(): ?string
    {
        return match($this) {
            self::Credit => 'heroicon-m-arrow-trending-up',
            self::Debit => 'heroicon-m-arrow-trending-down',
        };
    }
}
