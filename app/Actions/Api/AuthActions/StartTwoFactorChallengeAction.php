<?php

namespace App\Actions\Api\AuthActions;

use App\Actions\BaseAction;
use App\DTOs\Api\VerificationCodeData;
use App\Enums\VerificationCodeIdentifierKey;
use App\Enums\VerificationCodePurpose;
use App\Models\User;
use App\Models\VerificationCode;

class StartTwoFactorChallengeAction extends BaseAction
{
    /**
     * Perform the action
     */
    public function handle(User $user): VerificationCode
    {
        VerificationCode::where('purpose', VerificationCodePurpose::TWO_FACTOR->value)
            ->where('identifier_key', VerificationCodeIdentifierKey::EMAIL->value)
            ->where('identifier_value', $user->email)
            ->where('is_verified', false)
            ->delete();

        return app(CreateVerificationCodeAction::class)->handle(
            new VerificationCodeData(
                purpose: VerificationCodePurpose::TWO_FACTOR,
                identifier_key: VerificationCodeIdentifierKey::EMAIL,
                identifier_value: $user->email,
            )
        );
    }
}
