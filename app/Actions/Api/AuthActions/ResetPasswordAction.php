<?php

namespace App\Actions\Api\AuthActions;

use App\Actions\BaseAction;
use App\Models\User;
use App\Models\VerificationCode;
use Illuminate\Validation\ValidationException;

class ResetPasswordAction extends BaseAction
{
    /**
     * Perform the action
     *
     * @throws ValidationException
     */
    public function handle(mixed $validated): void
    {
        $verificationCode = VerificationCode::where('token', $validated['password_reset_token'])->first();

        if (! $verificationCode) {
            throw ValidationException::withMessages(['password_reset_token' => 'Invalid token.']);
        }

        if ($verificationCode->code != $validated['verification_code']) {
            throw ValidationException::withMessages(['verification_code' => trans('common.invalid_verification_code')]);
        }

        if ($verificationCode->expire_at < now()) {
            throw ValidationException::withMessages(['verification_code' => trans('common.verification_code_expired')]);
        }

        $user = User::where('email', $verificationCode->email)->first();

        $user->forceFill(['password' => bcrypt($validated['password'])])
            ->save();

        $verificationCode->delete();
    }
}
