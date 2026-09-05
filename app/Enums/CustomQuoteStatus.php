<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum CustomQuoteStatus: string implements HasLabel, HasColor
{
    case DRAFT = 'draft';
    case SENT = 'sent';
    case VIEWED = 'viewed';
    case ACCEPTED = 'accepted';
    case DECLINED = 'declined';
    case EXPIRED = 'expired';
    case CANCELLED = 'cancelled';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::SENT => 'Sent',
            self::VIEWED => 'Viewed',
            self::ACCEPTED => 'Accepted',
            self::DECLINED => 'Declined',
            self::EXPIRED => 'Expired',
            self::CANCELLED => 'Cancelled',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::DRAFT => 'gray',
            self::SENT => 'info',
            self::VIEWED => 'info',
            self::ACCEPTED => 'success',
            self::DECLINED => 'danger',
            self::EXPIRED => 'danger',
            self::CANCELLED => 'gray',
        };
    }
}
