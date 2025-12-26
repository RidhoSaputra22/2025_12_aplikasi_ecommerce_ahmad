<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum ProductStatus: string implements HasColor, HasIcon, HasLabel
{
    case Draft = 'draft';
    case Active = 'active';
    case Archived = 'archived';

    public function getLabel(): string
    {
        return match($this) {
            self::Draft => 'Draf',
            self::Active => 'Aktif',
            self::Archived => 'Diarsipkan',
        };
    }

    public function getColor(): string|array|null
    {
        return match($this) {
            self::Draft => 'gray',
            self::Active => 'success',
            self::Archived => 'warning',
        };
    }

    public function getIcon(): ?string
    {
        return match($this) {
            self::Draft => 'heroicon-m-document-text',
            self::Active => 'heroicon-m-check-circle',
            self::Archived => 'heroicon-m-archive-box',
        };
    }
}
