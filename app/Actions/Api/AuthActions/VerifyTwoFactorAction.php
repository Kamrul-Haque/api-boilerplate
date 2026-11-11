<?php

namespace App\Actions\Api\AuthActions;

use App\Actions\BaseAction;
use App\DTOs\Api\TwoFactorVerifyData;
use App\DTOs\Token\TokenPairData;
use App\Enums\VerificationCodePurpose;
use App\Models\User;
use App\Models\VerificationCode;
use App\Services\TokenService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class VerifyTwoFactorAction extends BaseAction
{
    /**
     * Perform the action
     *
     * @return array{user: User, tokens: TokenPairData}
     *
     * @throws ValidationException
     */
    public function handle(TwoFactorVerifyData $twoFactorVerifyData): array
    {
        $verificationCode = VerificationCode::where('token', $twoFactorVerifyData->verification_token)
            ->where('purpose', VerificationCodePurpose::TWO_FACTOR->value)
            ->where('is_verified', false)
            ->first();

        if (! $verificationCode) {
            throw ValidationException::withMessages(['verification_token' => trans('common.invalid_token')]);
        }

        if ($verificationCode->code != $twoFactorVerifyData->verification_code) {
            throw ValidationException::withMessages(['verification_code' => trans('common.invalid_verification_code')]);
        }

        if ($verificationCode->expire_at->isPast()) {
            throw ValidationException::withMessages(['verification_code' => trans('common.verification_code_expired')]);
        }

        $user = User::where($verificationCode->identifier_key, $verificationCode->identifier_value)->first();

        if (! $user) {
            throw ValidationException::withMessages(['verification_token' => trans('common.user_not_exist')]);
        }

        return DB::transaction(function () use ($verificationCode, $user) {
            $verificationCode->delete();

            $user->load(['active_role', 'active_role.permissions', 'roles']);

            return [
                'user' => $user,
                'tokens' => app(TokenService::class)->issueTokenPair($user),
            ];
        });
    }
}
