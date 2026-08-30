<?php

declare(strict_types=1);

namespace Misaf\LaravelEmailVerification\Support;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Throwable;

/**
 * The retry predicate shared by every HTTP driver.
 */
final class TransientFault
{
    /**
     * Retry only faults that a later attempt could plausibly resolve: a
     * connection-level failure, or a server-side 5xx. Retrying a 4xx — a bad
     * key, a malformed address, or a 429 rate limit — burns paid API quota
     * without any chance of a different answer.
     */
    public static function shouldRetry(Throwable $exception): bool
    {
        if ($exception instanceof ConnectionException) {
            return true;
        }

        return $exception instanceof RequestException
            && $exception->response->serverError();
    }
}
