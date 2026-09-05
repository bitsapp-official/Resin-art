<?php

namespace App\Enums;

enum ContactInquiryType: string
{
    case CUSTOM = 'custom';
    case TRADE = 'trade';
    case PRESS = 'press';
    case VISIT = 'visit';
    case GENERAL = 'general';

    public function label(): string
    {
        return match ($this) {
            self::CUSTOM => 'Custom Orders / Bespoke Pieces',
            self::TRADE => 'Trade / Designers & Hotels',
            self::PRESS => 'Press / Editorial Requests',
            self::VISIT => 'Visits / Book the Atelier',
            self::GENERAL => 'General Correspondence',
        };
    }

    public function subtitle(): string
    {
        return match ($this) {
            self::CUSTOM => 'Custom sculptures, tables & art installations',
            self::TRADE => 'Architectural partnerships & interior designers',
            self::PRESS => 'Media kits, interviews & high-res assets',
            self::VISIT => 'Private viewing at our Bordeaux studio',
            self::GENERAL => 'Inquiries, feedback or say hello',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::CUSTOM => 'amber',
            self::TRADE => 'emerald',
            self::PRESS => 'sky',
            self::VISIT => 'purple',
            self::GENERAL => 'gray',
        };
    }
}
