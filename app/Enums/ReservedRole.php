<?php

namespace App\Enums;

use App\Traits\EnumToArray;

enum ReservedRole: int
{
    use EnumToArray;

    case SYSTEM_ADMIN = 1;
    case CUSTOMER = 2;
}
