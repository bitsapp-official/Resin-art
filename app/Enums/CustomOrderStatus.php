<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum CustomOrderStatus: string implements HasLabel, HasColor
{
    case CONFIRMED = 'confirmed';
    case DESIGN_IN_PROGRESS = 'design_in_progress';
    case DESIGN_APPROVAL = 'design_approval';
    case DESIGN_REVISION = 'design_revision';
    case APPROVED = 'approved';
    case IN_PRODUCTION = 'in_production';
    case QUALITY_CHECK = 'quality_check';
    case READY_TO_SHIP = 'ready_to_ship';
    case SHIPPED = 'shipped';
    case DELIVERED = 'delivered';
    case CANCELLED = 'cancelled';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::CONFIRMED => 'Confirmed',
            self::DESIGN_IN_PROGRESS => 'Design In Progress',
            self::DESIGN_APPROVAL => 'Awaiting Approval',
            self::DESIGN_REVISION => 'Design Revision',
            self::APPROVED => 'Approved',
            self::IN_PRODUCTION => 'In Production',
            self::QUALITY_CHECK => 'Quality Check',
            self::READY_TO_SHIP => 'Ready to Ship',
            self::SHIPPED => 'Shipped',
            self::DELIVERED => 'Delivered',
            self::CANCELLED => 'Cancelled',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::CONFIRMED => 'info',
            self::DESIGN_IN_PROGRESS => 'gray',
            self::DESIGN_APPROVAL => 'warning',
            self::DESIGN_REVISION => 'warning',
            self::APPROVED => 'success',
            self::IN_PRODUCTION => 'primary',
            self::QUALITY_CHECK => 'warning',
            self::READY_TO_SHIP => 'success',
            self::SHIPPED => 'primary',
            self::DELIVERED => 'success',
            self::CANCELLED => 'danger',
        };
    }
}
