<?php

namespace App\Actions\Api\AuthActions;

use App\Actions\BaseAction;
use App\DTOs\Api\VerificationCodeData;
use App\Enums\VerificationCodeIdentifierKey;
use App\Enums\VerificationCodePurpose;
use App\Models\User;
use App\Models\VerificationCode;

class ForgotPasswordAction extends BaseAction
{
    public function __construct(private readonly CreateVerificationCodeAction $createVerificationCodeAction)
    {
        parent::__construct();
    }

    /**
     * Perform the action
     */
    public function handle(mixed $validated): VerificationCode
    {
        $user = User::where('email', $validated['email'])->first();

        return $this->createVerificationCodeAction->handle(
            new VerificationCodeData(
                purpose: VerificationCodePurpose::RESET_PASSWORD,
                identifier_key: VerificationCodeIdentifierKey::EMAIL,
                identifier_value: $user->email,
            )
        );
    }
}
