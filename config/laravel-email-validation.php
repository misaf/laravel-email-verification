<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Allowed Domains
    |--------------------------------------------------------------------------
    |
    | Restricts the domains accepted by the EmailValidation rule. Leave this
    | empty to impose no domain restriction. When populated, only addresses
    | whose domain appears in this list are accepted.
    |
    */

    'allowed_domains' => array_values(array_filter(
        explode(',', (string) env('EMAIL_ALLOWED_DOMAINS', '')),
    )),

    /*
    |--------------------------------------------------------------------------
    | Default Deliverability Driver
    |--------------------------------------------------------------------------
    |
    | The deliverability verifier used by the EmailValidation rule. The core
    | package ships only the "null" driver, which performs no external check
    | and treats every address as deliverable. Install a driver package (e.g.
    | misaf/laravel-email-validation-emailable) to add real verification, then
    | set this to its driver name.
    |
    */

    'default' => env('EMAIL_VERIFIER_DRIVER', 'null'),

];
