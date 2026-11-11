<?php

use App\Models\PersonalAccessToken;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    Cache::flush();

    $this->user = User::factory()->create();
});

test('it prunes expired and stale revoked tokens', function () {
    $expired = $this->user->createToken('access_token', ['*'], now()->subHour())->accessToken;

    $active = $this->user->createToken('access_token', ['*'], now()->addHour())->accessToken;

    $staleRevoked = $this->user->createToken('refresh_token', ['refresh'], now()->addDay())->accessToken;
    $staleRevoked->forceFill(['revoked_at' => now()->subDays(10)])->save();

    $recentRevoked = $this->user->createToken('refresh_token', ['refresh'], now()->addDay())->accessToken;
    $recentRevoked->forceFill(['revoked_at' => now()->subDay()])->save();

    $this->artisan('tokens:prune')->assertSuccessful();

    $this->assertDatabaseMissing('personal_access_tokens', ['id' => $expired->id]);
    $this->assertDatabaseMissing('personal_access_tokens', ['id' => $staleRevoked->id]);
    $this->assertDatabaseHas('personal_access_tokens', ['id' => $active->id]);
    $this->assertDatabaseHas('personal_access_tokens', ['id' => $recentRevoked->id]);
});

test('it prunes in chunks without skipping rows', function () {
    foreach (range(1, 5) as $index) {
        $this->user->createToken('access_token', ['*'], now()->subHour());
    }

    $this->artisan('tokens:prune', ['--chunk' => 1])->assertSuccessful();

    $this->assertDatabaseCount('personal_access_tokens', 0);
});

test('it forgets cache entries for pruned tokens', function () {
    $token = $this->user->createToken('access_token', ['*'], now()->subHour())->accessToken;

    Cache::put(PersonalAccessToken::cacheKeyForId($token->id), $token->getAttributes(), 300);

    expect(Cache::has(PersonalAccessToken::cacheKeyForId($token->id)))->toBeTrue();

    $this->artisan('tokens:prune')->assertSuccessful();

    expect(Cache::has(PersonalAccessToken::cacheKeyForId($token->id)))->toBeFalse();
});

test('dry run does not delete tokens', function () {
    $token = $this->user->createToken('access_token', ['*'], now()->subHour())->accessToken;

    $this->artisan('tokens:prune', ['--dry-run' => true])->assertSuccessful();

    $this->assertDatabaseHas('personal_access_tokens', ['id' => $token->id]);
});
