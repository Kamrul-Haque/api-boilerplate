<?php

namespace App\Actions\Api\AuthActions;

use App\Actions\BaseAction;
use App\DTOs\Api\VerificationCodeData;
use App\Enums\VerificationCodeIdentifierKey;
use App\Enums\VerificationCodePurpose;
use App\Models\User;
use App\Models\VerificationCode;
use Exception;

class StartTwoFactorChallengeAction extends BaseAction
{
    public function __construct(
        private readonly CreateVerificationCodeAction $createVerificationCodeAction
    ) {
        parent::__construct();
    }

    /**
     * Perform the action
     *
     * @throws Exception
     */
    public function handle(User $user): object
    {
        VerificationCode::where('purpose', VerificationCodePurpose::TWO_FACTOR->value)
            ->where('identifier_key', VerificationCodeIdentifierKey::EMAIL->value)
            ->where('identifier_value', $user->email)
            ->where('is_verified', false)
            ->delete();

        $verificationCode = $this->createVerificationCodeAction->handle(
            new VerificationCodeData(
                purpose: VerificationCodePurpose::TWO_FACTOR,
                identifier_key: VerificationCodeIdentifierKey::EMAIL,
                identifier_value: $user->email,
            )
        );

        return (object) [
            'token' => $verificationCode->token,
            'expiresIn' => VerificationCode::TTL_MINUTES * 60,
        ];
    }
}
