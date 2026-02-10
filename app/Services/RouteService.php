<?php

namespace App\Services;

use Illuminate\Support\Facades\Route;

class RouteService
{
    private static array $excluded = ['profile', 'logout', 'verification-code', 'verify-email', 'update-password',
        'switch-role', 'route-prefixes'];

    public static function getAllRoutePrefixes(): array
    {
        $routes = Route::getRoutes();
        $prefixes = [];

        foreach ($routes as $route) {
            $uri = $route->uri();
            $prefix = explode('/', str_replace('api/', '', $uri))[0] ?? null;
            if ($prefix) {
                $prefixes[$prefix] = true;
            }
        }

        return array_keys($prefixes);
    }

    public static function getAuthenticatedApiRoutePrefixes(): array
    {
        $routes = Route::getRoutes();
        $prefixes = [];

        foreach ($routes as $route) {
            $middlewares = $route->gatherMiddleware();

            if (str_starts_with($route->uri(), 'api/') && in_array('auth:sanctum', $middlewares)) {
                $uri = $route->uri();
                $prefix = explode('/', str_replace('api/', '', $uri))[0] ?? null;
                if ($prefix && ! in_array($prefix, self::$excluded)) {
                    $prefixes[$prefix] = true;
                }
            }
        }

        return array_keys($prefixes);
    }

    public static function getPublicApiRoutePrefixes(): array
    {
        $routes = Route::getRoutes();
        $prefixes = [];

        foreach ($routes as $route) {
            $middlewares = $route->gatherMiddleware();

            if (str_starts_with($route->uri(), 'api/') && ! (in_array('auth:sanctum', $middlewares))) {
                $uri = $route->uri();
                $prefix = explode('/', str_replace('api/', '', $uri))[0] ?? null;
                if ($prefix) {
                    $prefixes[$prefix] = true;
                }
            }
        }

        return array_keys($prefixes);
    }

    public static function getAllApiRoutes(): array
    {
        $routes = Route::getRoutes();
        $prefixes = [];

        foreach ($routes as $route) {
            if (str_starts_with($route->uri(), 'api/')) {
                $uri = $route->uri();
                $prefix = explode('/', str_replace('api/', '', $uri))[0] ?? null;
                if ($prefix) {
                    $prefixes[$prefix] = true;
                }
            }
        }

        return array_keys($prefixes);
    }
}
