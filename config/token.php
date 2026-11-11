<?php

return [
    'access_name' => 'access_token',
    'refresh_name' => 'refresh_token',
    'access_ttl' => (int) env('TOKEN_ACCESS_TTL', 60),
    'refresh_ttl' => (int) env('TOKEN_REFRESH_TTL', 43200),
    'cache_prefix' => env('TOKEN_CACHE_PREFIX', 'sanctum:access:'),
    'cache_ttl' => (int) env('TOKEN_CACHE_TTL', 300),
    'revoked_retention_days' => (int) env('TOKEN_REVOKED_RETENTION_DAYS', 7),
];
