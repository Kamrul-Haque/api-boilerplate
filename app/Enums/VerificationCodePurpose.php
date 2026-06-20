<?php

namespace App\Enums;

use App\Traits\EnumToArray;

enum VerificationCodePurpose: string
{
    use EnumToArray;

    case RESET_PASSWORD = 'reset-password';
    case VERIFY_EMAIL = 'verify-email';
    case UPDATE_EMAIL = 'update-email';
}
