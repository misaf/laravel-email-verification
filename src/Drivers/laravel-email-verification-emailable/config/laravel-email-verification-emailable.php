<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Emailable API
    |--------------------------------------------------------------------------
    |
    | Credentials for the Emailable deliverability API (https://emailable.com).
    |
    */

    'host'    => env('EMAILABLE_HOST', ''),
    'api_key' => env('EMAILABLE_API_KEY', ''),

];
