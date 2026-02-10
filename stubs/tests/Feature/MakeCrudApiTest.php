<?php

beforeEach(function () {
    $this->hasBaseActionClass = File::exists(app_path('Actions/BaseTest.php'));
    $this->model = Str::studly(trim(fake()->unique()->slug(2)));
    $this->migration = Str::plural(Str::snake($this->model));
    $this->uri = Str::plural(Str::slug(Str::snake($this->model)));

    $this->assertFileDoesNotExist(app_path("Models/{$this->model}.php"));
    $this->assertEmpty(glob(database_path("migrations/*_create_{$this->migration}_table.php")));
    $this->assertFileDoesNotExist(app_path("Http/Controllers/Api/{$this->model}Controller.php"));
    $this->assertFileDoesNotExist(app_path("Http/Requests/Api/{$this->model}Request.php"));
    $this->assertFileDoesNotExist(app_path("Http/Resources/{$this->model}Resource.php"));
    $this->assertFileDoesNotExist(database_path("seeders/{$this->model}Seeder.php"));
    $this->assertFileDoesNotExist(base_path("tests/Feature/Api/{$this->model}Test.php"));
    $this->assertEmpty(glob(app_path("Actions/Api/*{$this->model}Action.php")));
    $this->assertFalse(strpos(file_get_contents(base_path('routes/api.php')), "Route::resource('{$this->uri}', App\Http\Controllers\Api\\{$this->model}Controller::class);"));
});

test('make:crud command works with api option for all units with prompts', function () {
    $this->artisan('make:crud')
        ->expectsQuestion('Please enter the Model name', $this->model)
        ->expectsChoice('Choose Option', 'Api', ['Api', 'Blade', 'Inertia'])
        ->expectsChoice('Units to Generate', 'All', ['All', 'Select Units'])
        ->assertExitCode(0);

    $this->assertFileExists(app_path("Models/{$this->model}.php"));
    $this->assertNotEmpty(glob(database_path("migrations/*_create_{$this->migration}_table.php")));
    $this->assertFileExists(app_path("Http/Controllers/Api/{$this->model}Controller.php"));
    $this->assertFileExists(app_path("Http/Requests/Api/{$this->model}Request.php"));
    $this->assertFileExists(app_path("Http/Resources/{$this->model}Resource.php"));
    $this->assertFileExists(database_path("seeders/{$this->model}Seeder.php"));
    $this->assertFileExists(base_path("tests/Feature/Api/{$this->model}Test.php"));
    $this->assertCount(5, glob(app_path("Actions/Api/*{$this->model}Action.php")));
    $this->assertNotFalse(strpos(file_get_contents(base_path('routes/api.php')), "Route::resource('{$this->uri}', App\Http\Controllers\Api\\{$this->model}Controller::class);"));
});

test('make:crud command works with api option for all units without prompts', function () {
    $this->artisan("make:crud $this->model --api --all")
        ->assertExitCode(0);

    // check units exist after calling the command
    $this->assertFileExists(app_path("Models/{$this->model}.php"));
    $this->assertNotEmpty(glob(database_path("migrations/*_create_{$this->migration}_table.php")));
    $this->assertFileExists(app_path("Http/Controllers/Api/{$this->model}Controller.php"));
    $this->assertFileExists(app_path("Http/Requests/Api/{$this->model}Request.php"));
    $this->assertFileExists(app_path("Http/Resources/{$this->model}Resource.php"));
    $this->assertFileExists(database_path("seeders/{$this->model}Seeder.php"));
    $this->assertFileExists(base_path("tests/Feature/Api/{$this->model}Test.php"));
    $this->assertCount(5, glob(app_path("Actions/Api/*{$this->model}Action.php")));
    $this->assertNotFalse(strpos(file_get_contents(base_path('routes/api.php')), "Route::resource('{$this->uri}', App\Http\Controllers\Api\\{$this->model}Controller::class);"));
});

afterEach(function () {
    if (! $this->hasBaseActionClass) {
        File::delete(app_path('Actions/BaseAction.php'));

        if (File::isEmptyDirectory(app_path('Actions'))) {
            File::deleteDirectory(app_path('Actions'));
        }
    }

    File::delete(app_path("Models/{$this->model}.php"));
    File::delete(glob(database_path("migrations/*_create_{$this->migration}_table.php")));
    File::delete(app_path("Http/Controllers/Api/{$this->model}Controller.php"));
    File::delete(app_path("Http/Requests/Api/{$this->model}Request.php"));
    File::delete(app_path("Http/Resources/{$this->model}Resource.php"));
    File::delete(database_path("seeders/{$this->model}Seeder.php"));
    File::delete(base_path("tests/Feature/Api/{$this->model}Test.php"));
    File::delete(glob(app_path("Actions/Api/*{$this->model}Action.php")));
    $updatedContent = preg_replace("/Route::resource\(\s*'{$this->uri}'\s*,\s*App\\\\Http\\\\Controllers\\\\Api\\\\{$this->model}Controller::class\s*\);\n?/", '', file_get_contents(base_path('routes/api.php')));
    file_put_contents(base_path('routes/api.php'), $updatedContent);
});
