<?php

namespace App\Actions\Api\AuthActions;

use App\Actions\BaseAction;
use App\DTOs\Api\VerificationCodeData;
use App\Enums\VerificationCodeIdentifierKey;
use App\Enums\VerificationCodePurpose;
use App\Models\VerificationCode;
use Illuminate\Validation\ValidationException;

class ResendTwoFactorCodeAction extends BaseAction
{
    /**
     * Perform the action
     *
     * @throws ValidationException
     */
    public function handle(string $verificationToken): VerificationCode
    {
        $verificationCode = VerificationCode::where('token', $verificationToken)
            ->where('purpose', VerificationCodePurpose::TWO_FACTOR->value)
            ->where('is_verified', false)
            ->first();

        if (! $verificationCode) {
            throw ValidationException::withMessages(['verification_token' => trans('common.invalid_token')]);
        }

        if ($verificationCode->expire_at->isPast()) {
            throw ValidationException::withMessages(['verification_token' => trans('common.token_expired')]);
        }

        $identifierKey = VerificationCodeIdentifierKey::from($verificationCode->identifier_key);
        $identifierValue = $verificationCode->identifier_value;

        $verificationCode->delete();

        return app(CreateVerificationCodeAction::class)->handle(
            new VerificationCodeData(
                purpose: VerificationCodePurpose::TWO_FACTOR,
                identifier_key: $identifierKey,
                identifier_value: $identifierValue,
            )
        );
    }
}
