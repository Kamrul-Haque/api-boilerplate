<?php

namespace App\Actions\Api\AuthActions;

use App\Actions\BaseAction;
use App\Models\User;
use App\Models\VerificationCode;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class VerifyEmailAction extends BaseAction
{
    /**
     * Perform the action
     *
     * @throws Throwable
     */
    public function handle(mixed $validated): void
    {
        $verificationCode = VerificationCode::where('token', $validated['verification_token'])->first();

        if (! $verificationCode) {
            throw ValidationException::withMessages(['verification_token' => 'Invalid token.']);
        }

        if ($verificationCode->code != $validated['verification_code']) {
            throw ValidationException::withMessages(['verification_code' => trans('common.invalid_verification_code')]);
        }

        if ($verificationCode->expire_at < now()) {
            throw ValidationException::withMessages(['verification_code' => trans('common.verification_code_expired')]);
        }

        DB::transaction(function () use ($verificationCode) {
            $user = User::where('email', $verificationCode->email)->first();

            $user->update(['email_verified_at' => now()]);

            $verificationCode->delete();
        });
    }
}
