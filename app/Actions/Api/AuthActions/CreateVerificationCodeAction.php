<?php

namespace App\Actions\Api\AuthActions;

use App\Actions\BaseAction;
use App\DTOs\Api\VerificationCodeData;
use App\Events\SendVerificationCodeEvent;
use App\Models\VerificationCode;
use Illuminate\Support\Str;

class CreateVerificationCodeAction extends BaseAction
{
    /**
     * Perform the action
     */
    public function handle(VerificationCodeData $verificationCodeData): VerificationCode
    {
        $verificationCode = VerificationCode::create([
            'purpose' => $verificationCodeData->purpose->value,
            'identifier_key' => $verificationCodeData->identifier_key->value,
            'identifier_value' => $verificationCodeData->identifier_value,
            'code' => (string) random_int(100000, 999999),
            'expire_at' => now()->addMinutes(VerificationCode::TTL_MINUTES),
            'token' => (string) Str::uuid(),
        ]);

        SendVerificationCodeEvent::dispatch(
            $verificationCode->identifier_key,
            $verificationCode->identifier_value,
            $verificationCode->code
        );

        return $verificationCode;
    }
}
