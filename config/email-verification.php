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
    | set this to its driver name. Each driver package owns its own endpoint,
    | credential, timeout, and retry configuration.
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

];
