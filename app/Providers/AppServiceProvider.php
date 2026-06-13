<?php

namespace App\Providers;

use App\Enums\ReservedRole;
use App\Models\User;
use Carbon\Carbon;
use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\SecurityScheme;
use Exception;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->isProduction()) {
            URL::forceScheme('https');
        }

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)
                ->by($request->user()?->id ?: $request->ip())
                ->response(function (Request $request, array $headers) {
                    return response()->json('Too many attempts, please wait before you retry.', 429, $headers);
                });
        });

        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)
                ->by($request->user()?->id ?: $request->ip())
                ->response(function (Request $request, array $headers) {
                    return response()->json('Too many attempts, please wait before you retry.', 429, $headers);
                });
        });

        Gate::before(function (User $user, string $permission) {
            if ($user->hasRole(ReservedRole::SYSTEM_ADMIN->value)) {
                return true;
            } else {
                return $user->hasPermission($permission);
            }
        });

        Gate::define('viewApiDocs', function () {
            return true;
        });

        Scramble::afterOpenApiGenerated(function (OpenApi $openApi) {
            $openApi->secure(
                SecurityScheme::http('bearer')
            );
        });

        Builder::macro('whereDateFormat', function ($column, $operator, $value = null, $boolean = 'and') {
            if ($value === null) {
                $value = $operator;
                $operator = '=';
            }

            try {
                $date = Carbon::createFromFormat(config('app.date_format'), $value)?->toDateString();
            } catch (Exception $e) {
                return $this;
            }

            return $this->whereDate($column, $operator, $date, $boolean);
        });

        Builder::macro('orWhereDateFormat', function ($column, $operator, $value = null) {
            if ($value === null) {
                $value = $operator;
                $operator = '=';
            }

            try {
                $date = Carbon::createFromFormat(config('app.date_format'), $value)?->toDateString();
            } catch (Exception $e) {
                return $this;
            }

            return $this->whereDate($column, $operator, $date, 'or');
        });
    }
}
