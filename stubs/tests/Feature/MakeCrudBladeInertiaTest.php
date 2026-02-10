<?php

beforeEach(function () {
    $this->hasBaseActionClass = File::exists(app_path('Actions/BaseTest.php'));
    $this->hasBladeLayout = File::exists(resource_path('views/layouts/app.blade.php'));
    $this->hasBladeLayoutNavigation = File::exists(resource_path('views/layouts/navigation.blade.php'));
    $this->hasBladeLayoutComponent = File::exists(app_path('View/Components/AppLayout.php'));
    $this->hasVueLayout = File::exists(resource_path('js/Layouts/AuthenticatedLayout.vue'));
    $this->model = Str::studly(trim(fake()->unique()->slug(2)));
    $this->migration = Str::plural(Str::snake($this->model));
    $this->uri = Str::plural(Str::slug(Str::snake($this->model)));

    $this->assertFileDoesNotExist(app_path("Models/{$this->model}.php"));
    $this->assertEmpty(glob(database_path("migrations/*_create_{$this->migration}_table.php")));
    $this->assertFileDoesNotExist(app_path("Http/Controllers/{$this->model}Controller.php"));
    $this->assertFileDoesNotExist(app_path("Http/Requests/{$this->model}Request.php"));
    $this->assertFileDoesNotExist(database_path("seeders/{$this->model}Seeder.php"));
    $this->assertFileDoesNotExist(base_path("tests/Feature/{$this->model}Test.php"));
    $this->assertEmpty(glob(app_path("Actions/*{$this->model}Action.php")));
    $this->assertFalse(strpos(file_get_contents(base_path('routes/web.php')), "Route::resource('{$this->uri}', App\Http\Controllers\\{$this->model}Controller::class);"));
});

test('make:crud command works with blade option for all units with prompts', function () {
    $this->assertEmpty(glob(resource_path("views/{$this->uri}/*.blade.php")));

    // call the command
    $this->artisan('make:crud')
        ->expectsQuestion('Please enter the Model name', $this->model)
        ->expectsChoice('Choose Option', 'Blade', ['Api', 'Blade', 'Inertia'])
        ->expectsChoice('Units to Generate', 'All', ['All', 'Select Units'])
        ->expectsOutputToContain('Required units generated successfully.')
        ->assertExitCode(0);

    // check units exist after calling the command
    $this->assertFileExists(app_path("Models/{$this->model}.php"));
    $this->assertNotEmpty(glob(database_path("migrations/*_create_{$this->migration}_table.php")));
    $this->assertFileExists(app_path("Http/Controllers/{$this->model}Controller.php"));
    $this->assertFileExists(app_path("Http/Requests/{$this->model}Request.php"));
    $this->assertFileExists(database_path("seeders/{$this->model}Seeder.php"));
    $this->assertFileExists(base_path("tests/Feature/{$this->model}Test.php"));
    $this->assertCount(7, glob(app_path("Actions/*{$this->model}Action.php")));
    $this->assertNotFalse(strpos(file_get_contents(base_path('routes/web.php')), "Route::resource('{$this->uri}', App\Http\Controllers\\{$this->model}Controller::class);"));
    $this->assertCount(4, glob(resource_path("views/{$this->uri}/*.blade.php")));

    File::deleteDirectory(resource_path("views/{$this->uri}"));
});

test('make:crud command works with blade option for all units without prompts', function () {
    $this->assertEmpty(glob(resource_path("views/{$this->uri}/*.blade.php")));

    $this->artisan("make:crud $this->model --blade --all")
        ->expectsOutputToContain('Required units generated successfully.')
        ->assertExitCode(0);

    $this->assertFileExists(app_path("Models/{$this->model}.php"));
    $this->assertNotEmpty(glob(database_path("migrations/*_create_{$this->migration}_table.php")));
    $this->assertFileExists(app_path("Http/Controllers/{$this->model}Controller.php"));
    $this->assertFileExists(app_path("Http/Requests/{$this->model}Request.php"));
    $this->assertFileExists(database_path("seeders/{$this->model}Seeder.php"));
    $this->assertFileExists(base_path("tests/Feature/{$this->model}Test.php"));
    $this->assertCount(7, glob(app_path("Actions/*{$this->model}Action.php")));
    $this->assertNotFalse(strpos(file_get_contents(base_path('routes/web.php')), "Route::resource('{$this->uri}', App\Http\Controllers\\{$this->model}Controller::class);"));
    $this->assertCount(4, glob(resource_path("views/{$this->uri}/*.blade.php")));

    File::deleteDirectory(resource_path("views/{$this->uri}"));
});

test('make:crud command works with inertia option for all units with prompts', function () {
    $this->assertEmpty(glob(resource_path("js/Pages/{$this->model}/*.vue")));

    $this->artisan('make:crud')
        ->expectsQuestion('Please enter the Model name', $this->model)
        ->expectsChoice('Choose Option', 'Inertia', ['Api', 'Blade', 'Inertia'])
        ->expectsChoice('Units to Generate', 'All', ['All', 'Select Units'])
        ->expectsOutputToContain('Required units generated successfully.')
        ->assertExitCode(0);

    $this->assertFileExists(app_path("Models/{$this->model}.php"));
    $this->assertNotEmpty(glob(database_path("migrations/*_create_{$this->migration}_table.php")));
    $this->assertFileExists(app_path("Http/Controllers/{$this->model}Controller.php"));
    $this->assertFileExists(app_path("Http/Requests/{$this->model}Request.php"));
    $this->assertFileExists(database_path("seeders/{$this->model}Seeder.php"));
    $this->assertFileExists(base_path("tests/Feature/{$this->model}Test.php"));
    $this->assertCount(7, glob(app_path("Actions/*{$this->model}Action.php")));
    $this->assertNotFalse(strpos(file_get_contents(base_path('routes/web.php')), "Route::resource('{$this->uri}', App\Http\Controllers\\{$this->model}Controller::class);"));
    $this->assertCount(3, glob(resource_path("js/Pages/{$this->model}/*.vue")));

    File::deleteDirectory(resource_path("js/Pages/{$this->model}"));
});

test('make:crud command works with inertia option for all units without prompts', function () {
    $this->assertEmpty(glob(resource_path("js/Pages/{$this->model}/*.vue")));

    $this->artisan("make:crud $this->model --inertia --all")
        ->expectsOutputToContain('Required units generated successfully.')
        ->assertExitCode(0);

    $this->assertFileExists(app_path("Models/{$this->model}.php"));
    $this->assertNotEmpty(glob(database_path("migrations/*_create_{$this->migration}_table.php")));
    $this->assertFileExists(app_path("Http/Controllers/{$this->model}Controller.php"));
    $this->assertFileExists(app_path("Http/Requests/{$this->model}Request.php"));
    $this->assertFileExists(database_path("seeders/{$this->model}Seeder.php"));
    $this->assertFileExists(base_path("tests/Feature/{$this->model}Test.php"));
    $this->assertCount(7, glob(app_path("Actions/*{$this->model}Action.php")));
    $this->assertNotFalse(strpos(file_get_contents(base_path('routes/web.php')), "Route::resource('{$this->uri}', App\Http\Controllers\\{$this->model}Controller::class);"));
    $this->assertCount(3, glob(resource_path("js/Pages/{$this->model}/*.vue")));

    File::deleteDirectory(resource_path("js/Pages/{$this->model}"));
});

afterEach(function () {
    File::delete(app_path("Models/{$this->model}.php"));
    File::delete(glob(database_path("migrations/*_create_{$this->migration}_table.php")));
    File::delete(app_path("Http/Controllers/{$this->model}Controller.php"));
    File::delete(app_path("Http/Requests/{$this->model}Request.php"));
    File::delete(database_path("seeders/{$this->model}Seeder.php"));
    File::delete(base_path("tests/Feature/{$this->model}Test.php"));
    File::delete(glob(app_path("Actions/*{$this->model}Action.php")));
    $updatedContent = preg_replace("/Route::resource\(\s*'{$this->uri}'\s*,\s*App\\\\Http\\\\Controllers\\\\{$this->model}Controller::class\s*\);\n?/", '', file_get_contents(base_path('routes/api.php')));
    file_put_contents(base_path('routes/web.php'), $updatedContent);

    if (! $this->hasBaseActionClass) {
        File::delete(app_path('Actions/BaseAction.php'));

        if (File::isEmptyDirectory(app_path('Actions'))) {
            File::deleteDirectory(app_path('Actions'));
        }
    }

    if (! $this->hasBladeLayout) {
        File::delete(resource_path('views/layouts/app.blade.php'));

        if (File::exists(resource_path('views/layouts')) && File::isEmptyDirectory(resource_path('views/layouts'))) {
            File::deleteDirectory(resource_path('views/layouts'));
        }
    }

    if (! $this->hasBladeLayoutNavigation) {
        File::delete(resource_path('views/layouts/navigation.blade.php'));

        if (File::exists(resource_path('views/layouts')) && File::isEmptyDirectory(resource_path('views/layouts'))) {
            File::deleteDirectory(resource_path('views/layouts'));
        }
    }

    if (! $this->hasBladeLayoutComponent) {
        File::delete(app_path('View/Components/AppLayout.php'));

        if (File::exists(app_path('View/Components')) && File::isEmptyDirectory(app_path('View/Components'))) {
            File::deleteDirectory(app_path('View/Components'));
        }

        if (File::isEmptyDirectory(app_path('View'))) {
            File::deleteDirectory(app_path('view'));
        }
    }

    if (! $this->hasVueLayout) {
        File::delete(resource_path('js/Layouts/AuthenticatedLayout.vue'));

        if (File::exists(resource_path('js/Layouts')) && File::isEmptyDirectory(resource_path('js/Layouts'))) {
            File::deleteDirectory(resource_path('js/Layouts'));
        }
    }
});
