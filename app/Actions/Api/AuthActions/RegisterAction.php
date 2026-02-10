<?php

namespace App\Actions\Api\AuthActions;

use App\Actions\BaseAction;
use App\Models\User;
use Exception;

class RegisterAction extends BaseAction
{
    /**
     * Perform the action
     *
     * @throws Exception
     */
    public function handle(mixed $validated): array
    {
        $validated['password'] = bcrypt($validated['password']);

        $user = User::create($validated);
        $user->assignRole('user');

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }
}
