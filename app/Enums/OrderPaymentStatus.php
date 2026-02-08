<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum OrderPaymentStatus: string implements HasColor, HasIcon, HasLabel
{
    case Pending = 'pending';
    case WaitingConfirmation = 'waiting_confirmation';
    case Paid = 'paid';
    case Failed = 'failed';

    public function getLabel(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu Pembayaran',
            self::WaitingConfirmation => 'Menunggu Konfirmasi Admin',
            self::Paid => 'Sudah Dibayar',
            self::Failed => 'Pembayaran Gagal',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Pending => 'warning',
            self::WaitingConfirmation => 'info',
            self::Paid => 'success',
            self::Failed => 'danger',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Pending => 'heroicon-m-clock',
            self::WaitingConfirmation => 'heroicon-m-document-check',
            self::Paid => 'heroicon-m-check-circle',
            self::Failed => 'heroicon-m-exclamation-circle',
        };
    }

    public static function asSelectArray(): array
    {
        $array = [];
        foreach (self::cases() as $case) {
            $array[] = [
                'label' => $case->getLabel(),
                'value' => $case->value,
            ];
        }
        return $array;
    }
}
