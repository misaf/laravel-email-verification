<?php

declare(strict_types=1);

namespace Misaf\LaravelEmailValidation\Verifiers;

use Misaf\LaravelEmailValidation\Contracts\EmailVerifier;
use Misaf\LaravelEmailValidation\Enums\EmailVerificationStatus;

/**
 * Performs no external verification and treats every address as deliverable.
 * This is the default driver for local and testing environments.
 */
final class NullEmailVerifier implements EmailVerifier
{
    public function verify(string $email): EmailVerificationStatus
    {
        return EmailVerificationStatus::Deliverable;
    }
}
