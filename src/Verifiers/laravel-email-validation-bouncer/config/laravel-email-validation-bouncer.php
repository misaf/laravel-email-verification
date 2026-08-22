<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Bouncer API
    |--------------------------------------------------------------------------
    |
    | Credentials for the Bouncer email verification API
    | (https://usebouncer.com). The host is the real-time verify endpoint.
    |
    */

    'host'    => env('BOUNCER_HOST', ''),
    'api_key' => env('BOUNCER_API_KEY', ''),

];
