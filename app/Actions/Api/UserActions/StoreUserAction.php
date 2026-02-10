<?php

namespace App\Actions\Api\UserActions;

use App\Actions\BaseAction;
use App\Mail\NewAccountCreated;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
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
    public function handle(mixed $validated): User
    {
        $password = Str::password(16);

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
            Mail::to($user->email)->send(new NewAccountCreated($user, $password));
        });

        return $user->refresh()->load(['roles', 'active_role', 'active_role.permissions']);
    }
}
