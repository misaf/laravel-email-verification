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

    /*
    |--------------------------------------------------------------------------
    | Timeouts
    |--------------------------------------------------------------------------
    |
    | "server" is the verification budget Emailable is asked to respect, and
    | "client" is how long this application waits for the response. Keep the
    | client timeout above the server one, so a slow verification comes back as
    | a clean result instead of a client-side abort.
    |
    */

    'timeout' => [
        'server' => env('EMAILABLE_SERVER_TIMEOUT', 5),
        'client' => env('EMAILABLE_CLIENT_TIMEOUT', 6),
    ],

    /*
    |--------------------------------------------------------------------------
    | Retry
    |--------------------------------------------------------------------------
    |
    | How a failed verification is retried. Only transient faults are retried —
    | a connection failure or a server-side 5xx. A 4xx is never retried, since
    | a bad key or a rate limit cannot resolve itself and would only burn paid
    | API quota. "times" is the total number of attempts.
    |
    */

    'retry' => [
        'times'              => env('EMAILABLE_RETRY_TIMES', 2),
        'sleep_milliseconds' => env('EMAILABLE_RETRY_SLEEP', 100),
    ],

];
