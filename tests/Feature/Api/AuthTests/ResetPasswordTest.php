<?php

use App\Enums\VerificationCodeIdentifierKey;
use App\Enums\VerificationCodePurpose;
use App\Models\PersonalAccessToken;
use App\Models\User;
use App\Models\VerificationCode;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

beforeEach(function () {
    $this->password = 'Pa$$word';

    $this->user = User::create([
        'name' => 'test',
        'email' => 'test@test.com',
        'password' => $this->password,
    ]);

    Artisan::call('cache:clear');
});

test('reset password token validation works', function () {
    $token = Str::uuid();

    $this->postJson('/api/reset-password', [
        'password_reset_token' => $token,
        'verification_code' => 234325,
        'password' => $this->password,
        'password_confirmation' => $this->password,
    ])
        ->assertStatus(422)
        ->assertJsonPath('message', 'Invalid token.');
});

test('reset password verification code validation works', function () {
    $verificationCode = VerificationCode::create([
        'purpose' => VerificationCodePurpose::RESET_PASSWORD->value,
        'identifier_key' => VerificationCodeIdentifierKey::EMAIL->value,
        'identifier_value' => $this->user->email,
        'code' => random_int(100000, 999999),
        'expire_at' => now()->addMinutes(5),
        'token' => Str::uuid(),
    ]);
    $code = random_int(100000, 999999);

    $this->postJson('/api/reset-password', [
        'password_reset_token' => $verificationCode->token,
        'verification_code' => $code,
        'password' => $this->password,
        'password_confirmation' => $this->password,
    ])
        ->assertStatus(422)
        ->assertJsonPath('message', 'Invalid verification code.');
});

test('reset password verification code expiry works', function () {
    $verificationCode = VerificationCode::create([
        'purpose' => VerificationCodePurpose::RESET_PASSWORD->value,
        'identifier_key' => VerificationCodeIdentifierKey::EMAIL->value,
        'identifier_value' => $this->user->email,
        'code' => random_int(100000, 999999),
        'expire_at' => now()->subMinutes(5),
        'token' => Str::uuid(),
    ]);

    $this->postJson('/api/reset-password', [
        'password_reset_token' => $verificationCode->token,
        'verification_code' => $verificationCode->code,
        'password' => $this->password,
        'password_confirmation' => $this->password,
    ])
        ->assertStatus(422)
        ->assertJsonPath('message', 'The verification code has expired.');
});

test('password reset endpoint works', function () {
    $verificationCode = VerificationCode::create([
        'purpose' => VerificationCodePurpose::RESET_PASSWORD->value,
        'identifier_key' => VerificationCodeIdentifierKey::EMAIL->value,
        'identifier_value' => $this->user->email,
        'code' => random_int(100000, 999999),
        'expire_at' => now()->addMinutes(5),
        'token' => Str::uuid(),
    ]);
    $newPassword = 'NewPassword';

    $this->postJson('/api/login', [
        'email' => $this->user->email,
        'password' => $this->password,
    ])
        ->assertStatus(200);

    $this->postJson('/api/reset-password', [
        'password_reset_token' => $verificationCode->token,
        'verification_code' => $verificationCode->code,
        'password' => $newPassword,
        'password_confirmation' => $newPassword,
    ])
        ->assertStatus(200);

    $this->postJson('/api/login', [
        'email' => $this->user->email,
        'password' => $this->password,
    ])
        ->assertStatus(401);

    $this->postJson('/api/login', [
        'email' => $this->user->email,
        'password' => $newPassword,
    ])
        ->assertStatus(200);
});

test('password reset revokes all sessions', function () {
    $verificationCode = VerificationCode::create([
        'purpose' => VerificationCodePurpose::RESET_PASSWORD->value,
        'identifier_key' => VerificationCodeIdentifierKey::EMAIL->value,
        'identifier_value' => $this->user->email,
        'code' => random_int(100000, 999999),
        'expire_at' => now()->addMinutes(5),
        'token' => Str::uuid(),
    ]);
    $newPassword = 'NewPassword';

    $logins = collect([1, 2])->map(fn () => $this->postJson('/api/login', [
        'email' => $this->user->email,
        'password' => $this->password,
    ])->assertStatus(200));

    $logins->each(function ($login) {
        $this->withHeader('Authorization', 'Bearer '.$login->json('access_token'))
            ->getJson('/api/profile')
            ->assertStatus(200);
    });

    $this->postJson('/api/reset-password', [
        'password_reset_token' => $verificationCode->token,
        'verification_code' => $verificationCode->code,
        'password' => $newPassword,
        'password_confirmation' => $newPassword,
    ])->assertStatus(200);

    expect(Cache::has(PersonalAccessToken::cacheKey($logins->first()->json('access_token'))))->toBeFalse();

    $logins->each(function ($login) {
        $this->app['auth']->forgetGuards();

        $this->withHeader('Authorization', 'Bearer '.$login->json('access_token'))
            ->getJson('/api/profile')
            ->assertStatus(401);

        $this->postJson('/api/refresh-token', ['refresh_token' => $login->json('refresh_token')])
            ->assertStatus(401);
    });
});
