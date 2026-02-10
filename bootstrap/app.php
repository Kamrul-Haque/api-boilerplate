<?php

use App\Exceptions\ClientErrorException;
use App\Http\Middleware\Allow;
use App\Http\Middleware\ForceJsonResponse;
use App\Http\Middleware\SetLocale;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
                  ->withRouting(
                      web: __DIR__.'/../routes/web.php',
                      api: __DIR__.'/../routes/api.php',
                      commands: __DIR__.'/../routes/console.php',
                      health: '/up',
                  )
                  ->withMiddleware(function (Middleware $middleware) {
                      $middleware->trustProxies(at: [
                          '*',
                      ]);
                      $middleware->api(prepend: [
                          'throttle:api',
                          SetLocale::class,
                      ]);
                      $middleware->alias([
                          'allow' => Allow::class,
                      ]);
                      $middleware->validateCsrfTokens(except: [
                          '/routepay/callback/*',
                          'bank-transfer/*',
                          '/route-code/register/callback',
                          '/route-code/login/callback',
                      ]);
                  })
                  ->withExceptions(function (Exceptions $exceptions) {
                      if (request()->expectsJson()) {
                          $exceptions->render(function (NotFoundHttpException $e) {
                              return response()->json(['error' => __('common.not_found')], 404);
                          });

                          $exceptions->render(function (AuthenticationException $e) {
                              return response()->json(['error' => __('common.unauthorized')], 401);
                          });

                          $exceptions->render(function (AccessDeniedHttpException $e) {
                              return response()->json(['error' => $e->getMessage() ?? __('common.forbidden')], 403);
                          });

                          $exceptions->render(function (ClientErrorException $e) {
                              return response()->json(['error' => $e->getMessage()], 400);
                          });
                      }

                      $exceptions->render(function (QueryException $e) {
                          if (request()->expectsJson()) {
                              if ((config('app.debug') && config('app.env') !== 'production')) {
                                  return response()->json(['error' => $e->getMessage()], 500);
                              }

                              return response()->json(['error' => __('common.database_error')], 500);
                          }

                          if (!config('app.debug')) {
                              session()->flash('error', $e->getMessage());
                          }

                          throw $e;
                      });
                  })->create();
