<?php

declare(strict_types=1);

namespace Misaf\LaravelEmailVerification\Drivers;

use Misaf\LaravelEmailVerification\Contracts\EmailVerification;
use Misaf\LaravelEmailVerification\Enums\EmailVerificationStatus;

/**
 * Performs no external verification and treats every address as deliverable.
 * This is the default driver for local and testing environments.
 */
final class NullEmailVerification implements EmailVerification
{
    public function verify(string $email): EmailVerificationStatus
    {
        return EmailVerificationStatus::Deliverable;
    }
}
