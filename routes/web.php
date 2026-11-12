<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (app()->isLocal() && app()->hasDebugModeEnabled()) {
        return redirect('/docs/api');
    }
});

// Route::get('users/{user}/avatar', GetUserAvatarController::class)->name('users.avatar');
