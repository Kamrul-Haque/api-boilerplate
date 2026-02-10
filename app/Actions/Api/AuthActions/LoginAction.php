<?php

namespace App\Actions\Api\AuthActions;

use App\Actions\BaseAction;
use App\Models\User;
use Hash;
use Illuminate\Auth\AuthenticationException;

class LoginAction extends BaseAction
{
    /**
     * Perform the action
     *
     * @throws AuthenticationException
     */
    public function handle(mixed $validated): array
    {
        $user = User::where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            throw new AuthenticationException(trans('common.unauthorized'));
        }

        $token = $user->createToken('auth_token')->plainTextToken;
        $user->load(['active_role', 'active_role.permissions', 'roles']);

        return [
            'user' => $user,
            'token' => $token,
        ];
    }
}
