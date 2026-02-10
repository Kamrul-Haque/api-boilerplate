<?php

use Illuminate\Support\Facades\File;

beforeEach(function () {
    $this->hasBaseActionClass = File::exists(app_path('Actions/BaseTest.php'));
    $this->action = Str::studly(trim(fake()->unique()->slug(2)));
    $this->assertFileDoesNotExist(app_path("Actions/{$this->action}Action.php"));
});

test('make:action command works without class name prompt', function () {
    $this->artisan("make:action {$this->action}Action")
        ->assertExitCode(0);

    $this->assertFileExists(app_path('Actions/BaseAction.php'));
    $this->assertFileExists(app_path("Actions/{$this->action}Action.php"));
});

test('make:action command works with class name prompt', function () {
    $this->artisan('make:action')
        ->expectsQuestion('Please enter the class name', "{$this->action}Action")
        ->assertExitCode(0);

    $this->assertFileExists(app_path('Actions/BaseAction.php'));
    $this->assertFileExists(app_path("Actions/{$this->action}Action.php"));
});

afterEach(function () {
    if (! $this->hasBaseActionClass) {
        File::delete(app_path('Actions/BaseAction.php'));
    }

    if (File::isEmptyDirectory(app_path('Actions'))) {
        File::deleteDirectory(app_path('Actions'));
    }

    File::delete(app_path("Actions/{$this->action}Action.php"));
});
