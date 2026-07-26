<?php

declare(strict_types=1);

namespace Misaf\LaravelEmailValidation\Facades;

use Illuminate\Support\Facades\Facade;
use Misaf\LaravelEmailValidation\EmailVerifierManager;

/**
 * @method static \Misaf\LaravelEmailValidation\Contracts\EmailVerifier driver(string|null $driver = null)
 * @method static \Misaf\LaravelEmailValidation\Enums\EmailVerificationStatus verify(string $email)
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
