<?php

namespace App\Actions\Api\AuthActions;

use App\Actions\BaseAction;
use App\DTOs\Api\RegisterData;
use App\Models\User;
use App\Services\TokenService;
use Exception;

class RegisterAction extends BaseAction
{
    /**
     * Perform the action
     *
     * @throws Exception
     */
    public function handle(RegisterData $registerData): array
    {
        $validated = $registerData->toArray();
        $validated['password'] = bcrypt($validated['password']);

        $user = User::create($validated);
        $user->assignRole(User::DEFAULT_ROLE);

        return [
            'user' => $user,
            'tokens' => app(TokenService::class)->issueTokenPair($user),
        ];
    }
}
