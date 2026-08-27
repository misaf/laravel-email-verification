<?php

declare(strict_types=1);

namespace Misaf\LaravelEmailVerification\Enums;

enum EmailVerificationStatus
{
    /**
     * The address is deliverable.
     */
    case Deliverable;

    /**
     * The address is confirmed undeliverable.
     */
    case Undeliverable;

    /**
     * The address may receive mail but has quality or deliverability concerns.
     */
    case Risky;

    /**
     * Deliverability could not be determined (provider error or timeout).
     */
    case Unverifiable;
}
