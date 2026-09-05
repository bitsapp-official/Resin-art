<?php

namespace App\Enums;

enum DepositType: string
{
    case PERCENTAGE = 'percentage';
    case FIXED = 'fixed';
}
