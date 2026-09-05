<?php

namespace App\Enums;

enum CustomRequestSenderType: string
{
    case CUSTOMER = 'customer';
    case ADMIN = 'admin';
}
