<?php

declare(strict_types=1);

namespace Misaf\LaravelEmailVerification\Contracts;

use Misaf\LaravelEmailVerification\Enums\EmailVerificationStatus;

interface EmailVerifier
{
    /**
     * Determine whether the given email address is deliverable.
     */
    public function verify(string $email): EmailVerificationStatus;
}
