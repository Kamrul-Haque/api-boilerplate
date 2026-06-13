<?php

namespace App\Actions\Api\UserActions;

use App\Actions\BaseAction;
use App\DTOs\UserData;
use App\Models\User;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class UpdateUserAction extends BaseAction
{
    /**
     * Perform the action
     *
     * @throws Exception
     * @throws Throwable
     */
    public function handle(UserData $userData, User $user): User
    {
        $user->authorizeOwnerOrPermission();

        $validated = $userData->toArray();

        if (isset($validated['avatar'])) {
            if ($user->avatar && Storage::disk('s3')->exists($user->avatar)) {
                Storage::disk('s3')->delete($user->avatar);
            }

            $validated['avatar'] = $validated['avatar']->store('uploads/avatars', 's3');
        } else {
            $validated = Arr::except($validated, 'avatar');
        }

        return DB::transaction(function () use ($validated, $user) {
            if (isset($validated['roles'])) {
                $validated['active_role_id'] = $validated['roles'][0];
                $user->roles()->detach();

                foreach ($validated['roles'] as $role) {
                    $user->assignRole((int) $role);
                }
            }

            $user->update($validated);

            return $user->refresh()->load(['roles', 'active_role', 'active_role.permissions']);
        });
    }
}
