<?php

declare(strict_types=1);

use Misaf\LaravelEmailVerification\EmailVerificationManager;
use Misaf\LaravelEmailVerificationBouncer\BouncerEmailVerification;

it('registers the bouncer driver when this package is registered before the core package', function (): void {
    expect(app(EmailVerificationManager::class)->driver('bouncer'))
        ->toBeInstanceOf(BouncerEmailVerification::class);
});

it('resolves the bouncer driver through the facade accessor in either order', function (): void {
    expect(app('email-verification')->driver('bouncer'))
        ->toBeInstanceOf(BouncerEmailVerification::class);
});
