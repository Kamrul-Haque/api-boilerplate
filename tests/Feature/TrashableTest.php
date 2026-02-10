<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;

test('trashable trait works properly', function () {
    $user = User::create([
        'name' => 'test',
        'email' => 'test@test.com',
        'password' => 'hashed',
    ]);

    $userRaw = DB::table('users')->find($user->id);

    $this->assertDatabaseHas('users', (array) $userRaw);
    $this->assertTrue($user->delete());
    $this->assertDatabaseMissing('users', $user->toArray());
    $this->assertDatabaseHas('trashes', [
        'trashable_type' => get_class($user),
        'trashable_id' => $user->id,
    ]);
});
