<?php

use App\Enums\ReservedRole;
use App\Http\Controllers\Api as ApiControllers;
use Illuminate\Support\Facades\Route;

Route::post('register', [ApiControllers\AuthController::class, 'register']);
Route::post('login', [ApiControllers\AuthController::class, 'login'])
    ->name('login')
    ->middleware('throttle:login');
Route::post('verification-code', [ApiControllers\AuthController::class, 'verificationCode'])
    ->middleware('throttle:verification-code');
Route::post('reset-password', [ApiControllers\AuthController::class, 'resetPassword']);
Route::post('refresh-token', [ApiControllers\AuthController::class, 'refreshToken'])
    ->middleware('throttle:refresh');
Route::post('verify-two-factor', [ApiControllers\AuthController::class, 'verifyTwoFactor'])
    ->middleware('throttle:two-factor');

Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::get('profile', [ApiControllers\ProfileController::class, 'show']);
    Route::put('profile', [ApiControllers\ProfileController::class, 'update']);
    Route::put('update-password', [ApiControllers\AuthController::class, 'updatePassword']);
    Route::put('verify-email', [ApiControllers\AuthController::class, 'verifyEmail']);
    Route::put('switch-role', [ApiControllers\AuthController::class, 'switchRole']);
    Route::post('logout', [ApiControllers\AuthController::class, 'logout']);

    Route::apiResource('users', ApiControllers\UserController::class);
    Route::post('users/{user}/assign-roles', [ApiControllers\UserController::class, 'assignRoles']);

    Route::apiResource('roles', ApiControllers\RoleController::class);
});

Route::group(['middleware' => ['auth:sanctum', 'allow:'.ReservedRole::SYSTEM_ADMIN->value]], function () {
    Route::get('trashes', [ApiControllers\TrashController::class, 'index']);
    Route::post('trashes/{trash}/restore', [ApiControllers\TrashController::class, 'restore']);
    Route::delete('trashes/{trash}/delete', [ApiControllers\TrashController::class, 'delete']);
    Route::delete('trashes/clear', [ApiControllers\TrashController::class, 'clear']);

    Route::apiResource('permissions', ApiControllers\PermissionController::class);
    Route::apiResource('modules', ApiControllers\ModuleController::class);
});
