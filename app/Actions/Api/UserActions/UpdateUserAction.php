<?php

namespace App\Actions\Api\UserActions;

use App\Actions\BaseAction;
use App\DTOs\Api\UserData;
use App\Models\User;
use App\Services\TokenService;
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
                $currentRoles = $user->roles()->pluck('id')->map(fn ($id) => (int) $id)->sort()->values()->all();
                $newRoles = collect($validated['roles'])->map(fn ($id) => (int) $id)->sort()->values()->all();

                $validated['active_role_id'] = $validated['roles'][0];
                $user->roles()->detach();

                foreach ($validated['roles'] as $role) {
                    $user->assignRole((int) $role);
                }

                if ($currentRoles !== $newRoles) {
                    app(TokenService::class)->revokeAll($user);
                }
            }

            $user->update($validated);

            return $user->refresh()->load(['roles', 'active_role', 'active_role.permissions']);
        });
    }
}
