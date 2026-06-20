<?php

namespace App\Enums;

use App\Traits\EnumToArray;

enum VerificationCodeIdentifierKey: string
{
    use EnumToArray;

    case EMAIL = 'email';
    case PHONE = 'phone';
    case BOTH = 'both';
}
