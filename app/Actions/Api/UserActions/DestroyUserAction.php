<?php

namespace App\Actions\Api\UserActions;

use App\Actions\BaseAction;
use App\Enums\ReservedRole;
use App\Exceptions\ClientErrorException;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Storage;

class DestroyUserAction extends BaseAction
{
    /**
     * Perform the action
     *
     * @throws Exception
     */
    public function handle(User $user, int $authId): void
    {
        $user->authorizeOwnerOrPermission();

        if ($user->id === $authId) {
            throw new ClientErrorException(trans('common.cannot_delete_self'));
        }

        if ($user->hasRole(ReservedRole::SYSTEM_ADMIN->value)) {
            throw new ClientErrorException(trans('common.cannot_delete_user'));
        }

        if ($user->avatar && Storage::disk('s3')->exists($user->avatar)) {
            Storage::disk('s3')->delete($user->avatar);
        }

        $user->delete();
    }
}
