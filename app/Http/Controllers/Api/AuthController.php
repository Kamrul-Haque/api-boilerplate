<?php

namespace App\Http\Controllers\Api;

use App\Actions\Api\AuthActions\CreateVerificationCodeAction;
use App\Actions\Api\AuthActions\LoginAction;
use App\Actions\Api\AuthActions\LogoutAction;
use App\Actions\Api\AuthActions\RefreshTokenAction;
use App\Actions\Api\AuthActions\RegisterAction;
use App\Actions\Api\AuthActions\ResendTwoFactorCodeAction;
use App\Actions\Api\AuthActions\ResetPasswordAction;
use App\Actions\Api\AuthActions\SwitchRoleAction;
use App\Actions\Api\AuthActions\UpdatePasswordAction;
use App\Actions\Api\AuthActions\VerifyEmailAction;
use App\Actions\Api\AuthActions\VerifyTwoFactorAction;
use App\DTOs\Api\LoginData;
use App\DTOs\Api\RefreshTokenData;
use App\DTOs\Api\RegisterData;
use App\DTOs\Api\ResetPasswordData;
use App\DTOs\Api\TwoFactorVerifyData;
use App\DTOs\Api\UpdatePasswordData;
use App\DTOs\Api\VerificationCodeData;
use App\DTOs\Token\TokenPairData;
use App\Enums\VerificationCodePurpose;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Requests\Api\RefreshTokenRequest;
use App\Http\Requests\Api\RegisterRequest;
use App\Http\Requests\Api\ResetPasswordRequest;
use App\Http\Requests\Api\TwoFactorVerifyRequest;
use App\Http\Requests\Api\UpdatePasswordRequest;
use App\Http\Requests\Api\VerificationCodeRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
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
        $registerData = RegisterData::fromRequest($request);

        return $this->tokenResponse(
            trans('success.parent_registered'),
            $registerAction->handle($registerData)
        );
    }

    /**
     * Login
     *
     * Login with valid user credentials by issuing a token pair.
     *
     * @unauthenticated
     *
     * @return JsonResponse
     *
     * @throws AuthenticationException
     */
    public function login(LoginRequest $request, LoginAction $loginAction)
    {
        $loginData = LoginData::fromRequest($request);

        $data = $loginAction->handle($loginData);

        if ($data['two_factor']) {
            return response()->json([
                'message' => trans('success.two_factor_required'),
                'two_factor_required' => true,
                'verification_token' => $data['two_factor']->token,
                'expires_in' => 300,
            ], 202);
        }

        return $this->tokenResponse(
            trans('success.login_success'),
            $data
        );
    }

    /**
     * Verify Two-Factor
     *
     * Exchange a valid two-factor challenge for a token pair.
     *
     * @unauthenticated
     *
     * @return JsonResponse
     *
     * @throws ValidationException
     */
    public function verifyTwoFactor(TwoFactorVerifyRequest $request, VerifyTwoFactorAction $verifyTwoFactorAction)
    {
        $twoFactorVerifyData = TwoFactorVerifyData::fromRequest($request);

        return $this->tokenResponse(
            trans('success.login_success'),
            $verifyTwoFactorAction->handle($twoFactorVerifyData)
        );
    }

    /**
     * Refresh Token
     *
     * Exchange a valid refresh token for a new access and refresh token pair.
     *
     * @unauthenticated
     *
     * @return JsonResponse
     *
     * @throws AuthenticationException
     */
    public function refreshToken(RefreshTokenRequest $request, RefreshTokenAction $refreshTokenAction)
    {
        $refreshTokenData = RefreshTokenData::fromRequest($request);

        return $this->tokenResponse(
            trans('success.token_refreshed'),
            $refreshTokenAction->handle($refreshTokenData)
        );
    }

    /**
     * Verification Code
     *
     * Send verification code to the user.
     *
     * @return JsonResponse
     */
    public function verificationCode(
        VerificationCodeRequest $request,
        CreateVerificationCodeAction $createVerificationCodeAction,
        ResendTwoFactorCodeAction $resendTwoFactorCodeAction
    ) {
        if ($request->validated('purpose') === VerificationCodePurpose::TWO_FACTOR->value) {
            $verificationCode = $resendTwoFactorCodeAction->handle($request->validated('verification_token'));
        } else {
            $verificationCodeData = VerificationCodeData::fromRequest($request);
            $verificationCode = $createVerificationCodeAction->handle($verificationCodeData);
        }

        return response()->json([
            'message' => trans('success.otp_sent'),
            'verification_token' => $verificationCode->token,
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
        $resetPasswordData = ResetPasswordData::fromRequest($request);
        $resetPasswordAction->handle($resetPasswordData);

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
        $updatePasswordData = UpdatePasswordData::fromRequest($request);
        $updatePasswordAction->handle($updatePasswordData, request()->user());

        return response()->json([
            'message' => trans('success.password_updated'),
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
     * Log out the authenticated user by revoking the current session tokens.
     *
     * @return JsonResponse
     */
    public function logout(Request $request, LogoutAction $logoutAction)
    {
        $logoutAction->handle($request->user(), $request->bearerToken());

        return response()->json([
            'message' => trans('success.logout_success'),
        ]);
    }

    /**
     * Build a response containing a user and a token pair.
     *
     * @param  array{user: User, tokens: TokenPairData}  $data
     */
    private function tokenResponse(string $message, array $data): JsonResponse
    {
        $tokens = $data['tokens'];

        return response()->json([
            'message' => $message,
            'user' => UserResource::make($data['user']),
            'token' => $tokens->accessToken,
            'access_token' => $tokens->accessToken,
            'refresh_token' => $tokens->refreshToken,
            'token_type' => $tokens->tokenType,
            'expires_in' => (int) $tokens->expiresIn,
        ]);
    }
}
