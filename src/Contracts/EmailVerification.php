<?php

declare(strict_types=1);

namespace Misaf\LaravelEmailVerification\Contracts;

use Misaf\LaravelEmailVerification\Enums\EmailVerificationStatus;

interface EmailVerification
{
    public function verify(string $email): EmailVerificationStatus;
}
