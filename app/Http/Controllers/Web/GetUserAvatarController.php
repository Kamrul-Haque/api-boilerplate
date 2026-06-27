<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Storage;

class GetUserAvatarController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(User $user)
    {
        try {
            if ($user->avatar) {
                return Storage::disk('s3')->response($user->avatar);
            }

            return null;
        } catch (Exception $exception) {
            return $exception->getMessage();
        }
    }
}
