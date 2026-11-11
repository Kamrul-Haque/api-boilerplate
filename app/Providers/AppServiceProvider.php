<?php

namespace App\Providers;

use App\Enums\ReservedRole;
use App\Models\PersonalAccessToken;
use App\Models\User;
use App\Services\AccessControlService;
use App\Services\TokenService;
use Carbon\Carbon;
use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\SecurityScheme;
use Exception;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(AccessControlService::class);
        $this->app->singleton(TokenService::class);

        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);
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

        RateLimiter::for('refresh', function (Request $request) {
            return Limit::perMinute(10)
                ->by($request->ip())
                ->response(function (Request $request, array $headers) {
                    return response()->json('Too many attempts, please wait before you retry.', 429, $headers);
                });
        });

        RateLimiter::for('verification-code', function (Request $request) {
            return Limit::perMinute(5)
                ->by($request->input('identifier_value') ?: $request->input('verification_token') ?: $request->ip())
                ->response(function (Request $request, array $headers) {
                    return response()->json('Too many attempts, please wait before you retry.', 429, $headers);
                });
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)
                ->by($request->input('verification_token') ?: $request->ip())
                ->response(function (Request $request, array $headers) {
                    return response()->json('Too many attempts, please wait before you retry.', 429, $headers);
                });
        });

        Sanctum::authenticateAccessTokensUsing(function ($accessToken, bool $isValid) {
            if (! $isValid) {
                return false;
            }

            if ($accessToken->name !== config('token.access_name')) {
                return false;
            }

            if ($accessToken->revoked_at !== null) {
                return false;
            }

            if ($accessToken->session_id && Cache::has(PersonalAccessToken::sessionRevokedKey($accessToken->session_id))) {
                return false;
            }

            $user = $accessToken->tokenable;

            return $user && (int) $accessToken->token_version === (int) $user->token_version;
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
