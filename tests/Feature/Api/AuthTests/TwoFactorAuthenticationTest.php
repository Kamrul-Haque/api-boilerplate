<?php

use App\Actions\Api\UserActions\AssignRolesAction;
use App\Enums\VerificationCodeIdentifierKey;
use App\Enums\VerificationCodePurpose;
use App\Events\SendVerificationCodeEvent;
use App\Models\Role;
use App\Models\User;
use App\Models\VerificationCode;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;

beforeEach(function () {
    Event::fake([SendVerificationCodeEvent::class]);
    Artisan::call('cache:clear');

    $this->password = 'Password@12345';

    Role::create(['name' => 'user', 'display_name' => 'User']);
    Role::create(['name' => 'admin', 'display_name' => 'Admin']);

    $this->user = User::factory()->create([
        'name' => 'admin',
        'email' => 'admin@email.com',
        'password' => $this->password,
    ]);
});

test('a user with only the user role logs in without two factor', function () {
    $this->user->assignRole('user');

    $this->postJson('/api/login', [
        'email' => $this->user->email,
        'password' => $this->password,
    ])
        ->assertStatus(200)
        ->assertJsonStructure(['access_token', 'refresh_token'])
        ->assertJsonPath('two_factor_required', false);
});

test('a user with a non-user role receives a two factor challenge', function () {
    $this->user->assignRole('admin');

    $response = $this->postJson('/api/login', [
        'email' => $this->user->email,
        'password' => $this->password,
    ])
        ->assertStatus(202)
        ->assertJson([
            'two_factor_required' => true,
            'expires_in' => 300,
        ])
        ->assertJsonStructure(['message', 'verification_token'])
        ->assertJsonMissingPath('access_token')
        ->assertJsonMissingPath('refresh_token')
        ->assertJsonMissingPath('data');

    $this->assertDatabaseHas('verification_codes', [
        'token' => $response->json('verification_token'),
        'purpose' => VerificationCodePurpose::TWO_FACTOR->value,
        'identifier_key' => VerificationCodeIdentifierKey::EMAIL->value,
        'identifier_value' => $this->user->email,
        'is_verified' => false,
    ]);

    Event::assertDispatched(SendVerificationCodeEvent::class);
});

test('it verifies the two factor code and returns tokens', function () {
    $this->user->assignRole('admin');

    $challenge = $this->postJson('/api/login', [
        'email' => $this->user->email,
        'password' => $this->password,
    ])->assertStatus(202);

    $verificationToken = $challenge->json('verification_token');
    $code = VerificationCode::where('token', $verificationToken)->firstOrFail()->code;

    $response = $this->postJson('/api/verify-two-factor', [
        'verification_token' => $verificationToken,
        'verification_code' => $code,
    ])
        ->assertStatus(200)
        ->assertJsonStructure([
            'message',
            'access_token',
            'refresh_token',
            'expires_in',
            'user' => ['id', 'name', 'email'],
        ]);

    $this->assertDatabaseMissing('verification_codes', ['token' => $verificationToken]);

    $this->withHeader('Authorization', 'Bearer '.$response->json('access_token'))
        ->getJson('/api/profile')
        ->assertStatus(200);
});

test('it rejects an invalid two factor code', function () {
    $this->user->assignRole('admin');

    $challenge = $this->postJson('/api/login', [
        'email' => $this->user->email,
        'password' => $this->password,
    ])->assertStatus(202);

    $this->postJson('/api/verify-two-factor', [
        'verification_token' => $challenge->json('verification_token'),
        'verification_code' => '000000',
    ])
        ->assertStatus(422)
        ->assertJsonPath('errors.verification_code.0', trans('common.invalid_verification_code'));
});

test('it rejects an expired two factor code', function () {
    $this->user->assignRole('admin');

    $challenge = $this->postJson('/api/login', [
        'email' => $this->user->email,
        'password' => $this->password,
    ])->assertStatus(202);

    $verificationCode = VerificationCode::where('token', $challenge->json('verification_token'))->firstOrFail();
    $verificationCode->forceFill(['expire_at' => now()->subMinute()])->save();

    $this->postJson('/api/verify-two-factor', [
        'verification_token' => $verificationCode->token,
        'verification_code' => $verificationCode->code,
    ])
        ->assertStatus(422)
        ->assertJsonPath('errors.verification_code.0', trans('common.verification_code_expired'));
});

test('it rejects a reused two factor token', function () {
    $this->user->assignRole('admin');

    $challenge = $this->postJson('/api/login', [
        'email' => $this->user->email,
        'password' => $this->password,
    ])->assertStatus(202);

    $verificationToken = $challenge->json('verification_token');
    $code = VerificationCode::where('token', $verificationToken)->firstOrFail()->code;

    $this->postJson('/api/verify-two-factor', [
        'verification_token' => $verificationToken,
        'verification_code' => $code,
    ])->assertStatus(200);

    $this->postJson('/api/verify-two-factor', [
        'verification_token' => $verificationToken,
        'verification_code' => $code,
    ])
        ->assertStatus(422)
        ->assertJsonPath('errors.verification_token.0', trans('common.invalid_token'));
});

test('it rejects a reset password code on the two factor endpoint', function () {
    $this->user->assignRole('admin');

    $verificationCode = VerificationCode::create([
        'purpose' => VerificationCodePurpose::RESET_PASSWORD->value,
        'identifier_key' => VerificationCodeIdentifierKey::EMAIL->value,
        'identifier_value' => $this->user->email,
        'code' => random_int(100000, 999999),
        'expire_at' => now()->addMinutes(5),
        'token' => Str::uuid(),
    ]);

    $this->postJson('/api/verify-two-factor', [
        'verification_token' => $verificationCode->token,
        'verification_code' => $verificationCode->code,
    ])
        ->assertStatus(422)
        ->assertJsonPath('errors.verification_token.0', trans('common.invalid_token'));
});

test('it rejects a two factor code on the reset password endpoint', function () {
    $this->user->assignRole('admin');

    $challenge = $this->postJson('/api/login', [
        'email' => $this->user->email,
        'password' => $this->password,
    ])->assertStatus(202);

    $verificationCode = VerificationCode::where('token', $challenge->json('verification_token'))->firstOrFail();

    $this->postJson('/api/reset-password', [
        'password_reset_token' => $verificationCode->token,
        'verification_code' => $verificationCode->code,
        'password' => 'NewPassword123',
        'password_confirmation' => 'NewPassword123',
    ])
        ->assertStatus(422)
        ->assertJsonPath('errors.password_reset_token.0', 'Invalid token.');
});

test('it resends a two factor code using the challenge token', function () {
    $this->user->assignRole('admin');

    $challenge = $this->postJson('/api/login', [
        'email' => $this->user->email,
        'password' => $this->password,
    ])->assertStatus(202);

    $oldToken = $challenge->json('verification_token');
    $oldCode = VerificationCode::where('token', $oldToken)->firstOrFail()->code;

    $response = $this->postJson('/api/verification-code', [
        'purpose' => VerificationCodePurpose::TWO_FACTOR->value,
        'verification_token' => $oldToken,
    ])
        ->assertStatus(200)
        ->assertJsonStructure(['message', 'verification_token']);

    $newToken = $response->json('verification_token');

    expect($newToken)->not->toBe($oldToken);
    $this->assertDatabaseMissing('verification_codes', ['token' => $oldToken]);
    $this->assertDatabaseHas('verification_codes', [
        'token' => $newToken,
        'purpose' => VerificationCodePurpose::TWO_FACTOR->value,
    ]);

    $this->postJson('/api/verify-two-factor', [
        'verification_token' => $oldToken,
        'verification_code' => $oldCode,
    ])->assertStatus(422);

    $newCode = VerificationCode::where('token', $newToken)->firstOrFail()->code;

    $this->postJson('/api/verify-two-factor', [
        'verification_token' => $newToken,
        'verification_code' => $newCode,
    ])->assertStatus(200);
});

test('it requires a challenge token to resend a two factor code', function () {
    $this->postJson('/api/verification-code', [
        'purpose' => VerificationCodePurpose::TWO_FACTOR->value,
    ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('verification_token');
});

test('it keeps the non two factor verification code flow unchanged', function () {
    $this->postJson('/api/verification-code', [
        'purpose' => VerificationCodePurpose::RESET_PASSWORD->value,
        'identifier_key' => VerificationCodeIdentifierKey::EMAIL->value,
        'identifier_value' => $this->user->email,
    ])
        ->assertStatus(200)
        ->assertJsonStructure(['message', 'verification_token']);
});

test('it revokes tokens when roles change', function () {
    $this->user->assignRole('user');

    $this->postJson('/api/login', [
        'email' => $this->user->email,
        'password' => $this->password,
    ])->assertStatus(200);

    $this->assertDatabaseHas('personal_access_tokens', ['tokenable_id' => $this->user->id]);

    app(AssignRolesAction::class)->handle(
        [Role::where('name', 'admin')->firstOrFail()->id],
        $this->user
    );

    $this->assertDatabaseMissing('personal_access_tokens', ['tokenable_id' => $this->user->id]);
});

test('it rate limits two factor verification', function () {
    $this->user->assignRole('admin');

    $challenge = $this->postJson('/api/login', [
        'email' => $this->user->email,
        'password' => $this->password,
    ])->assertStatus(202);

    $verificationToken = $challenge->json('verification_token');

    foreach (range(1, 5) as $attempt) {
        $this->postJson('/api/verify-two-factor', [
            'verification_token' => $verificationToken,
            'verification_code' => '000000',
        ])->assertStatus(422);
    }

    $this->postJson('/api/verify-two-factor', [
        'verification_token' => $verificationToken,
        'verification_code' => '000000',
    ])->assertStatus(429);
});
