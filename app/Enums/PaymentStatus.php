<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum PaymentStatus: string implements HasColor, HasIcon, HasLabel
{
    case Pending = 'pending';
    case WaitingConfirmation = 'waiting_confirmation';
    case Success = 'success';
    case Failed = 'failed';

    public function getLabel(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu Pembayaran',
            self::WaitingConfirmation => 'Menunggu Konfirmasi',
            self::Success => 'Berhasil',
            self::Failed => 'Gagal',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Pending => 'warning',
            self::WaitingConfirmation => 'info',
            self::Success => 'success',
            self::Failed => 'danger',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Pending => 'heroicon-m-clock',
            self::WaitingConfirmation => 'heroicon-m-document-check',
            self::Success => 'heroicon-m-check-circle',
            self::Failed => 'heroicon-m-x-circle',
        };
    }

    public static function asArray(): array
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
