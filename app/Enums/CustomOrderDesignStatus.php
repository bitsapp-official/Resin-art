<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum CustomOrderDesignStatus: string implements HasLabel, HasColor
{
    case PENDING = 'pending';
    case SENT = 'sent';
    case APPROVED = 'approved';
    case CHANGES_REQUESTED = 'changes_requested';
    case SUPERSEDED = 'superseded';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::SENT => 'Sent to Customer',
            self::APPROVED => 'Approved',
            self::CHANGES_REQUESTED => 'Changes Requested',
            self::SUPERSEDED => 'Superseded (Old Version)',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::PENDING => 'gray',
            self::SENT => 'info',
            self::APPROVED => 'success',
            self::CHANGES_REQUESTED => 'warning',
            self::SUPERSEDED => 'gray',
        };
    }
}
