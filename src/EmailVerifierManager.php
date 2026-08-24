<?php

declare(strict_types=1);

namespace Misaf\LaravelEmailValidation;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Manager;
use LogicException;
use Misaf\LaravelEmailValidation\Contracts\EmailVerifier;
use Misaf\LaravelEmailValidation\Verifiers\NullEmailVerifier;

/**
 * Resolves deliverability verifiers. The core package ships only the "null"
 * driver; concrete provider drivers register themselves via {@see extend()}
 * from their own packages.
 *
 * @method \Misaf\LaravelEmailValidation\Enums\EmailVerificationStatus verify(string $email)
 */
final class EmailVerifierManager extends Manager
{
    public function getDefaultDriver(): string
    {
        return Config::string('laravel-email-validation.default', 'null');
    }

    public function verifier(?string $driver = null): EmailVerifier
    {
        $verifier = $this->driver($driver);

        if ( ! $verifier instanceof EmailVerifier) {
            throw new LogicException('The configured email verifier must implement the EmailVerifier contract.');
        }

        return $verifier;
    }

    protected function createNullDriver(): EmailVerifier
    {
        return new NullEmailVerifier();
    }
}
