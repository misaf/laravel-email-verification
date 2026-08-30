<?php

declare(strict_types=1);

use Misaf\LaravelEmailVerification\EmailVerificationManager;
use Misaf\LaravelEmailVerificationEmailable\EmailableEmailVerification;

it('registers the emailable driver when this package is registered before the core package', function (): void {
    expect(app(EmailVerificationManager::class)->driver('emailable'))
        ->toBeInstanceOf(EmailableEmailVerification::class);
});

it('resolves the emailable driver through the facade accessor in either order', function (): void {
    expect(app('email-verification')->driver('emailable'))
        ->toBeInstanceOf(EmailableEmailVerification::class);
});
