<?php

namespace App\Actions\Api\UserActions;

use App\Actions\BaseAction;
use App\DTOs\Api\UserData;
use App\Mail\NewAccountCreated;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class StoreUserAction extends BaseAction
{
    /**
     * Perform the action
     *
     * @throws Exception
     * @throws Throwable
     */
    public function handle(UserData $userData): User
    {
        $password = Str::password(16);

        $validated = $userData->toArray();
        $validated['password'] = bcrypt($password);

        if (isset($validated['avatar'])) {
            $validated['avatar'] = $validated['avatar']->store('uploads/avatars', 's3');
        }

        try {
            $user = DB::transaction(function () use ($validated) {
                $user = User::create($validated);

                foreach ($validated['roles'] as $role) {
                    $user->assignRole((int) $role);
                }

                return $user->refresh()->load('roles');
            });
        } catch (Throwable $e) {
            if (isset($validated['avatar']) && Storage::disk('s3')->exists($validated['avatar'])) {
                Storage::disk('s3')->delete($validated['avatar']);
            }

            throw $e;
        }

        DB::afterCommit(function () use ($password, $user) {
            Event::dispatch(new NewAccountCreated($user, $password));
        });

        return $user->refresh()->load(['roles', 'active_role', 'active_role.permissions']);
    }
}
