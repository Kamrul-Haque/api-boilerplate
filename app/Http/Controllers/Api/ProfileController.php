<?php

namespace App\Http\Controllers\Api;

use App\Actions\Api\ProfileActions\ProfileShowAction;
use App\Actions\Api\ProfileActions\ProfileUpdateAction;
use App\DTOs\Api\ProfileData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ProfileRequest;
use App\Http\Resources\UserResource;
use Dedoc\Scramble\Attributes\Group;

#[Group(name: 'Authentication')]
class ProfileController extends Controller
{
    /**
     * Get Profile
     *
     * Get user profile data.
     *
     * @return UserResource
     */
    public function show(ProfileShowAction $profileShowAction)
    {
        $user = $profileShowAction->handle(auth()->user());

        return UserResource::make($user);
    }

    /**
     * Update Profile
     *
     * Update user profile data.
     *
     * @return UserResource
     */
    public function update(ProfileRequest $request, ProfileUpdateAction $updateAction)
    {
        $profileData = ProfileData::fromRequest($request);
        $user = $updateAction->handle($profileData, auth()->user());

        return UserResource::make($user->refresh());
    }
}
