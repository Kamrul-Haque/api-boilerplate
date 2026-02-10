<?php

use App\Http\Controllers\GetUserAvatarController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/docs/api');
});

Route::get('users/{user}/avatar', GetUserAvatarController::class)->name('users.avatar');
