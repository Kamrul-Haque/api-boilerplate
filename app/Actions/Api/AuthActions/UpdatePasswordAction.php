<?php

namespace App\Actions\Api\AuthActions;

use App\Actions\BaseAction;
use App\DTOs\Api\UpdatePasswordData;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UpdatePasswordAction extends BaseAction
{
    /**
     * Perform the action
     */
    public function handle(UpdatePasswordData $updatePasswordData, User $user): void
    {
        if (! Hash::check($updatePasswordData->current_password, $user->password)) {
            throw ValidationException::withMessages(['current_password' => trans('common.wrong_password')]);
        }

        $user->forceFill(['password' => bcrypt($updatePasswordData->password)])
            ->save();
    }
}
