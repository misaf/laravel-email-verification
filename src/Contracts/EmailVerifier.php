<?php

declare(strict_types=1);

namespace Misaf\LaravelEmailValidation\Contracts;

use Misaf\LaravelEmailValidation\Enums\EmailVerificationStatus;

interface EmailVerifier
{
    /**
     * Determine whether the given email address is deliverable.
     */
    public function verify(string $email): EmailVerificationStatus;
}
