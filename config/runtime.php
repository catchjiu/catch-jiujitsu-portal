<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Runtime Debug Toggle (HTTP)
    |--------------------------------------------------------------------------
    |
    | Used by the custom runtime debugging helpers we added for Coolify. Do not
    | read env() directly at runtime in application code; use config() so this
    | still works when config is cached.
    |
    */
    'debug' => (bool) env('APP_RUNTIME_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Debug Token
    |--------------------------------------------------------------------------
    |
    | Token required to access /debug/* endpoints. If empty, debug endpoints
    | are treated as disabled (404).
    |
    */
    'debug_token' => (string) env('DEBUG_TOKEN', ''),
];

