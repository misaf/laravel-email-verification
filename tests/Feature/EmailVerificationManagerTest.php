<?php

declare(strict_types=1);

use Misaf\LaravelEmailVerification\Contracts\EmailVerification;
use Misaf\LaravelEmailVerification\Drivers\NullEmailVerification;
use Misaf\LaravelEmailVerification\EmailVerificationManager;
use Misaf\LaravelEmailVerification\Enums\EmailVerificationStatus;
use Misaf\LaravelEmailVerification\Facades\EmailVerification as EmailVerificationFacade;
use Misaf\LaravelEmailVerification\Rules\EmailValidation;

function manager(): EmailVerificationManager
{
    return app(EmailVerificationManager::class);
}

it('resolves the null driver as the default', function (): void {
    config(['email-verification.default' => 'null']);

    expect(manager()->driver())->toBeInstanceOf(NullEmailVerification::class)
        ->and(manager()->driver()->verify('user@example.com'))->toBe(EmailVerificationStatus::Deliverable);
});

it('falls back to the null driver when no default driver is configured', function (): void {
    // The whole package namespace, not just the key: an application that never
    // published the config still has to resolve a driver.
    config()->offsetUnset('email-verification');

    expect(config('email-verification.default'))->toBeNull()
        ->and(manager()->driver())->toBeInstanceOf(NullEmailVerification::class);
});

it('supports driver packages registering via extend', function (): void {
    $manager = manager();
    $manager->extend('always-undeliverable', fn(): EmailVerification => new class implements EmailVerification {
        public function verify(string $email): EmailVerificationStatus
        {
            return EmailVerificationStatus::Undeliverable;
        }
    });

    expect($manager->driver('always-undeliverable')->verify('user@example.com'))
        ->toBe(EmailVerificationStatus::Undeliverable);
});

it('resolves a registered driver as the configured default', function (): void {
    $manager = manager();
    $manager->extend('custom-default', fn(): EmailVerification => new class implements EmailVerification {
        public function verify(string $email): EmailVerificationStatus
        {
            return EmailVerificationStatus::Risky;
        }
    });
    config(['email-verification.default' => 'custom-default']);

    expect($manager->driver()->verify('user@example.com'))
        ->toBe(EmailVerificationStatus::Risky);
});

it('fails validation when the resolved driver reports the address undeliverable', function (): void {
    config(['email-verification.allowed_domains' => []]);

    manager()->extend('always-undeliverable', fn(): EmailVerification => new class implements EmailVerification {
        public function verify(string $email): EmailVerificationStatus
        {
            return EmailVerificationStatus::Undeliverable;
        }
    });

    $failures = [];
    (new EmailValidation('always-undeliverable'))->validate('email', 'user@example.com', function (string $message) use (&$failures): void {
        $failures[] = $message;
    });

    expect($failures)->toBe(['This :attribute is invalid and cannot be delivered. Please provide a valid one.']);
});

/*
 * Direct verification is deliverability only. The domain allow-list is a
 * validation-rule concern, so the facade must not quietly apply it — otherwise
 * callers could not verify an address outside the list on purpose.
 */
it('verifies directly through the facade without consulting the domain allow-list', function (): void {
    config(['email-verification.allowed_domains' => ['example.com']]);

    expect(EmailVerificationFacade::verify('user@blocked.test'))
        ->toBe(EmailVerificationStatus::Deliverable);
});

it('verifies through a named driver without consulting the domain allow-list', function (): void {
    config(['email-verification.allowed_domains' => ['example.com']]);

    manager()->extend('always-risky', fn(): EmailVerification => new class implements EmailVerification {
        public function verify(string $email): EmailVerificationStatus
        {
            return EmailVerificationStatus::Risky;
        }
    });

    expect(EmailVerificationFacade::driver('always-risky')->verify('user@blocked.test'))
        ->toBe(EmailVerificationStatus::Risky);
});
