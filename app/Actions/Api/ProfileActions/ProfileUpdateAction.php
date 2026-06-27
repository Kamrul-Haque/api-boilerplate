<?php

namespace App\Actions\Api\ProfileActions;

use App\Actions\BaseAction;
use App\DTOs\Api\ProfileData;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

class ProfileUpdateAction extends BaseAction
{
    /**
     * Perform the action
     */
    public function handle(ProfileData $profileData, User $user): User
    {
        $validated = $profileData->toArray();

        if ($validated['email'] != $user->email) {
            $validated['email_verified_at'] = null;
        }

        if (isset($validated['avatar'])) {
            if ($user->avatar && Storage::disk('s3')->exists($user->avatar)) {
                Storage::disk('s3')->delete($user->avatar);
            }

            $validated['avatar'] = $validated['avatar']->store('uploads/avatars', 's3');
        } else {
            $validated = Arr::except($validated, 'avatar');
        }

        $user->update($validated);

        return $user->refresh()->load(['active_role', 'active_role.permissions', 'roles']);
    }
}
