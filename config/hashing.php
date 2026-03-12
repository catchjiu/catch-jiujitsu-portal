<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Hash Driver
    |--------------------------------------------------------------------------
    |
    | This option controls the default hash driver that will be used to hash
    | passwords. This is usually "bcrypt" for Laravel apps.
    |
    */
    'driver' => env('HASH_DRIVER', 'bcrypt'),

    /*
    |--------------------------------------------------------------------------
    | Bcrypt Options
    |--------------------------------------------------------------------------
    |
    | When hashing passwords using Bcrypt, you may specify the number of rounds
    | to apply. Higher rounds are more secure but take longer to compute.
    |
    | Note: pgcrypto's crypt(gen_salt('bf')) produces "$2a$" hashes. Laravel's
    | Bcrypt hasher can throw when algorithm verification is enabled because PHP
    | may report "$2a$" as "unknown". Disabling "verify" allows these hashes to
    | be checked via password_verify() without 500 errors during login.
    |
    */
    'bcrypt' => [
        'rounds' => env('BCRYPT_ROUNDS', 12),
        'verify' => env('BCRYPT_VERIFY', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Argon Options
    |--------------------------------------------------------------------------
    |
    | The options here control the Argon2 hashing settings. They are unused
    | unless you switch HASH_DRIVER to "argon" / "argon2id".
    |
    */
    'argon' => [
        'memory' => env('ARGON_MEMORY', 65536),
        'threads' => env('ARGON_THREADS', 1),
        'time' => env('ARGON_TIME', 4),
        'verify' => env('ARGON_VERIFY', true),
    ],
];

