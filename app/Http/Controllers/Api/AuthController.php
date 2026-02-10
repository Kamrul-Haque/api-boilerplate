<?php

namespace App\Http\Controllers\Api;

use App\Actions\Api\AuthActions\CreateVerificationCodeAction;
use App\Actions\Api\AuthActions\ForgotPasswordAction;
use App\Actions\Api\AuthActions\LoginAction;
use App\Actions\Api\AuthActions\LogoutAction;
use App\Actions\Api\AuthActions\RegisterAction;
use App\Actions\Api\AuthActions\ResetPasswordAction;
use App\Actions\Api\AuthActions\SwitchRoleAction;
use App\Actions\Api\AuthActions\UpdatePasswordAction;
use App\Actions\Api\AuthActions\VerifyEmailAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Requests\Api\RegisterRequest;
use App\Http\Requests\Api\ResetPasswordRequest;
use App\Http\Requests\Api\UpdatePasswordRequest;
use App\Http\Resources\UserResource;
use Dedoc\Scramble\Attributes\Group;
use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

#[Group(name: 'Authentication')]
class AuthController extends Controller
{
    /**
     * Register
     *
     * Register a new user with required data. Also logs in the user if successful.
     *
     * @unauthenticated
     *
     * @return JsonResponse
     *
     * @throws Exception
     * @throws Throwable
     */
    public function register(RegisterRequest $request, RegisterAction $registerAction)
    {
        $data = $registerAction->handle($request->validated());

        return response()->json([
            'message' => trans('success.parent_registered'),
            'user' => UserResource::make($data['user']),
            'token' => $data['token'],
        ]);
    }

    /**
     * Login
     *
     * Login with valid user credentials by issuing token.
     *
     * @unauthenticated
     *
     * @return JsonResponse
     *
     * @throws AuthenticationException
     */
    public function login(LoginRequest $request, LoginAction $loginAction)
    {
        $data = $loginAction->handle($request->validated());

        return response()->json([
            'message' => trans('success.login_success'),
            'user' => UserResource::make($data['user']),
            'token' => $data['token'],
        ]);
    }

    /**
     * Forgot Password
     *
     * Send verification code for updating the password of the user.
     *
     * @unauthenticated
     *
     * @return JsonResponse
     */
    public function forgotPassword(Request $request, ForgotPasswordAction $forgotPasswordAction)
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255', 'exists:users,email'],
        ]);

        $verificationCode = $forgotPasswordAction->handle($validated);

        return response()->json([
            'message' => trans('success.otp_sent'),
            'password_reset_token' => $verificationCode->token,
        ]);
    }

    /**
     * Reset Password
     *
     * Reset the password of the user.
     *
     * @unauthenticated
     *
     * @return JsonResponse
     *
     * @throws ValidationException
     */
    public function resetPassword(ResetPasswordRequest $request, ResetPasswordAction $resetPasswordAction)
    {
        $resetPasswordAction->handle($request->validated());

        return response()->json([
            'message' => trans('success.password_updated'),
        ]);
    }

    /**
     * Update Password
     *
     * Update the password of the authenticated user.
     *
     * @return JsonResponse
     *
     * @throws ValidationException
     */
    public function updatePassword(UpdatePasswordRequest $request, UpdatePasswordAction $updatePasswordAction)
    {
        $updatePasswordAction->handle($request->validated(), request()->user());

        return response()->json([
            'message' => trans('success.password_updated'),
        ]);
    }

    /**
     * Verification Code
     *
     * Send verification code to the user.
     *
     * @return JsonResponse
     */
    public function verificationCode(Request $request, CreateVerificationCodeAction $createVerificationCodeAction)
    {
        $verificationCode = $createVerificationCodeAction->handle($request->user());

        return response()->json([
            'message' => trans('success.otp_sent'),
            'verification_token' => $verificationCode->token,
        ]);
    }

    /**
     * Verify Email
     *
     * Verify the email of the user.
     *
     * @return JsonResponse
     *
     * @throws ValidationException|Throwable
     */
    public function verifyEmail(Request $request, VerifyEmailAction $verifyEmailAction)
    {
        $validated = $request->validate([
            'verification_token' => ['required', 'uuid'],
            'verification_code' => ['required', 'digits:6'],
        ]);

        $verifyEmailAction->handle($validated);

        return response()->json([
            'message' => trans('success.email_verified'),
        ]);
    }

    /**
     * Switch Role
     *
     * Switch between assigned roles.
     *
     * @return JsonResponse
     *
     * @throws ValidationException
     */
    public function switchRole(Request $request, SwitchRoleAction $switchRoleAction)
    {
        $user = auth()->user();

        $request->validate([
            'role_id' => ['required', 'integer'],
        ]);

        $switchRoleAction->handle($request->role_id, $user);

        return response()->json([
            'message' => trans('success.role_switched'),
            'user' => UserResource::make($user),
        ]);
    }

    /**
     * Logout
     *
     * Log out the authenticated user by revoking the token.
     *
     * @return JsonResponse
     */
    public function logout(Request $request, LogoutAction $logoutAction)
    {
        $logoutAction->handle($request->user());

        return response()->json([
            'message' => trans('success.logout_success'),
        ]);
    }
}
