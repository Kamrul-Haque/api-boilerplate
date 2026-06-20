<?php

namespace App\Actions\Api\AuthActions;

use App\Actions\BaseAction;
use App\DTOs\VerificationCodeData;
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
            'purpose' => $verificationCodeData->purpose,
            'identifier_key' => $verificationCodeData->identifier_key,
            'identifier_value' => $verificationCodeData->identifier_value,
            'code' => random_int(100000, 999999),
            'expire_at' => now()->addMinutes(5),
            'token' => Str::uuid(),
        ]);

        SendVerificationCodeEvent::dispatch(
            $verificationCode->identifier_key,
            $verificationCode->identifier_value,
            $verificationCode->code
        );

        return $verificationCode;
    }
}
