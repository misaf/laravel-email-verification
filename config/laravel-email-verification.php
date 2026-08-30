<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Default Deliverability Driver
    |--------------------------------------------------------------------------
    |
    | The deliverability driver used by the EmailValidation rule. The core
    | package ships only the "null" driver, which performs no external check
    | and treats every address as deliverable. Install a driver package (e.g.
    | misaf/laravel-email-verification-emailable) to add real verification, then
    | set this to its driver name.
    |
    */

    'default' => env('EMAIL_VERIFICATION_DRIVER', 'null'),

    /*
    |--------------------------------------------------------------------------
    | Allowed Domains
    |--------------------------------------------------------------------------
    |
    | The domains the EmailValidation rule accepts. Comparison is
    | case-insensitive. Leave the list empty to impose no domain restriction.
    |
    */

    'allowed_domains' => [

    ],

    /*
    |--------------------------------------------------------------------------
    | Retry
    |--------------------------------------------------------------------------
    |
    | How an HTTP driver retries a failed verification. Only transient faults
    | are retried — a connection failure or a server-side 5xx. A 4xx is never
    | retried, since a bad key or a rate limit cannot resolve itself and would
    | only burn paid API quota.
    |
    */

    'retry' => [
        'times'              => env('EMAIL_VERIFICATION_RETRY_TIMES', 2),
        'sleep_milliseconds' => env('EMAIL_VERIFICATION_RETRY_SLEEP', 100),
    ],

];
