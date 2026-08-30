<?php

declare(strict_types=1);

namespace Misaf\LaravelEmailVerification;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Manager;
use Misaf\LaravelEmailVerification\Contracts\EmailVerification;
use Misaf\LaravelEmailVerification\Drivers\NullEmailVerification;

/**
 * @method \Misaf\LaravelEmailVerification\Enums\EmailVerificationStatus verify(string $email)
 */
final class EmailVerificationManager extends Manager
{
    public function getDefaultDriver(): string
    {
        return Config::string('email-verification.default', 'null');
    }

    protected function createNullDriver(): EmailVerification
    {
        return new NullEmailVerification();
    }
}
