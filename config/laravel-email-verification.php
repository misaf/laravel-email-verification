<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Default Deliverability Driver
    |--------------------------------------------------------------------------
    |
    | The deliverability verifier used by the EmailValidation rule. The core
    | package ships only the "null" driver, which performs no external check
    | and treats every address as deliverable. Install a driver package (e.g.
    | misaf/laravel-email-verification-emailable) to add real verification, then
    | set this to its driver name.
    |
    */

    'default' => env('EMAIL_VERIFIER_DRIVER', 'null'),

];
