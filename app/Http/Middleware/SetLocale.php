<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->header('Accept-Language');
        $timezone = $request->header('X-User-Timezone');

        if (in_array($locale, ['en', 'ja'])) {
            App::setLocale($locale);
        }

        if (in_array($timezone, timezone_identifiers_list(), true)) {
            Config::set('app.timezone', $timezone);
            date_default_timezone_set($timezone);
        }

        return $next($request);
    }
}
