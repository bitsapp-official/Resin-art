<?php

namespace App\Enums;

enum ContactInquiryStatus: string
{
    case NEW = 'new';
    case READ = 'read';
    case IN_PROGRESS = 'in_progress';
    case REPLIED = 'replied';
    case CLOSED = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::NEW => 'New Inquiry',
            self::READ => 'Read',
            self::IN_PROGRESS => 'In Progress',
            self::REPLIED => 'Replied',
            self::CLOSED => 'Closed',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::NEW => 'danger',
            self::READ => 'info',
            self::IN_PROGRESS => 'warning',
            self::REPLIED => 'success',
            self::CLOSED => 'gray',
        };
    }
}
