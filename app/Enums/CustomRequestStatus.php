<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum CustomRequestStatus: string implements HasLabel, HasColor
{
    // Minimal 5-Step Lifecycle
    case SUBMITTED         = 'submitted';
    case UNDER_REVIEW      = 'under_review';
    case NEEDS_INFORMATION = 'needs_information';
    case QUOTE_PREPARATION = 'quote_preparation';
    case QUOTE_SENT        = 'quote_sent';
    case CONFIRMED         = 'confirmed';
    case IN_PRODUCTION     = 'in_production';
    case QUALITY_CHECK     = 'quality_check';
    case READY_TO_SHIP     = 'ready_to_ship';
    case SHIPPED           = 'shipped';
    case DELIVERED         = 'delivered';
    case DECLINED          = 'declined';
    case EXPIRED           = 'expired';

    /**
     * Filament Admin Label
     */
    public function getLabel(): ?string
    {
        return match ($this) {
            self::SUBMITTED         => 'New Request',
            self::UNDER_REVIEW      => 'Under Review',
            self::NEEDS_INFORMATION => 'Under Review',
            self::QUOTE_PREPARATION => 'Under Review',
            self::QUOTE_SENT        => 'Under Review',
            self::CONFIRMED         => 'In Production',
            self::IN_PRODUCTION     => 'In Production',
            self::QUALITY_CHECK     => 'In Production',
            self::READY_TO_SHIP     => 'Dispatched',
            self::SHIPPED           => 'Dispatched',
            self::DELIVERED         => 'Delivered',
            self::DECLINED          => 'Declined',
            self::EXPIRED           => 'Declined',
        };
    }

    /**
     * Customer Facing Label
     */
    public function customerLabel(): string
    {
        return match ($this) {
            self::SUBMITTED         => 'Request Submitted',
            self::UNDER_REVIEW,
            self::NEEDS_INFORMATION,
            self::QUOTE_PREPARATION,
            self::QUOTE_SENT        => 'Under Review',
            self::CONFIRMED,
            self::IN_PRODUCTION,
            self::QUALITY_CHECK     => 'In Production',
            self::READY_TO_SHIP,
            self::SHIPPED           => 'Dispatched',
            self::DELIVERED         => 'Delivered',
            self::DECLINED,
            self::EXPIRED           => 'Declined',
        };
    }

    /**
     * Filament Badge Color
     */
    public function getColor(): string|array|null
    {
        return match ($this) {
            self::SUBMITTED         => 'gray',
            self::UNDER_REVIEW,
            self::NEEDS_INFORMATION,
            self::QUOTE_PREPARATION,
            self::QUOTE_SENT        => 'warning',
            self::CONFIRMED,
            self::IN_PRODUCTION,
            self::QUALITY_CHECK     => 'primary',
            self::READY_TO_SHIP,
            self::SHIPPED           => 'info',
            self::DELIVERED         => 'success',
            self::DECLINED,
            self::EXPIRED           => 'danger',
        };
    }

    /**
     * 5-Segment Progress Index (1 to 5)
     */
    public function stepIndex(): int
    {
        return match ($this) {
            self::SUBMITTED         => 1,
            self::UNDER_REVIEW,
            self::NEEDS_INFORMATION,
            self::QUOTE_PREPARATION,
            self::QUOTE_SENT        => 2,
            self::CONFIRMED,
            self::IN_PRODUCTION,
            self::QUALITY_CHECK     => 3,
            self::READY_TO_SHIP,
            self::SHIPPED           => 4,
            self::DELIVERED         => 5,
            self::DECLINED,
            self::EXPIRED           => 0,
        };
    }
}
