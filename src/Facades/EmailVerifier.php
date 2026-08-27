<?php

declare(strict_types=1);

namespace Misaf\LaravelEmailVerification\Facades;

use Illuminate\Support\Facades\Facade;
use Misaf\LaravelEmailVerification\EmailVerifierManager;

/**
 * @method static \Misaf\LaravelEmailVerification\Contracts\EmailVerifier driver(string|null $driver = null)
 * @method static \Misaf\LaravelEmailVerification\Enums\EmailVerificationStatus verify(string $email)
 *
 * @see EmailVerifierManager
 */
final class EmailVerifier extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return EmailVerifierManager::class;
    }
}
