<?php

declare(strict_types=1);

namespace Misaf\LaravelEmailVerification\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Misaf\LaravelEmailVerification\Contracts\EmailVerification driver(string|null $driver = null)
 * @method static \Misaf\LaravelEmailVerification\Enums\EmailVerificationStatus verify(string $email)
 *
 * @see EmailVerificationManager
 */
final class EmailVerification extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'email-verification';
    }
}
