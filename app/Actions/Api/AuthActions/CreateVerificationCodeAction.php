<?php

namespace App\Actions\Api\AuthActions;

use App\Actions\BaseAction;
use App\Models\User;
use App\Models\VerificationCode;
use App\Notifications\SendVerificationCode;
use Illuminate\Support\Str;

class CreateVerificationCodeAction extends BaseAction
{
    /**
     * Perform the action
     */
    public function handle(User $user): VerificationCode
    {
        $verificationCode = VerificationCode::create([
            'email' => $user->email,
            'code' => random_int(100000, 999999),
            'expire_at' => now()->addMinutes(5),
            'token' => Str::uuid(),
        ]);

        $user->notify(new SendVerificationCode($verificationCode->code));

        return $verificationCode;
    }
}
