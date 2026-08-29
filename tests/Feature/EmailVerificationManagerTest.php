<?php

declare(strict_types=1);

use Misaf\LaravelEmailVerification\Contracts\EmailVerification;
use Misaf\LaravelEmailVerification\Drivers\NullEmailVerification;
use Misaf\LaravelEmailVerification\EmailVerificationManager;
use Misaf\LaravelEmailVerification\Enums\EmailVerificationStatus;
use Misaf\LaravelEmailVerification\Rules\EmailValidation;

function manager(): EmailVerificationManager
{
    return app(EmailVerificationManager::class);
}

it('resolves the null driver as the default', function (): void {
    config(['laravel-email-verification.default' => 'null']);

    expect(manager()->driver())->toBeInstanceOf(NullEmailVerification::class)
        ->and(manager()->driver()->verify('user@example.com'))->toBe(EmailVerificationStatus::Deliverable);
});

it('falls back to the null driver when the package config is missing', function (): void {
    config(['laravel-email-verification' => []]);

    expect(manager()->driver())->toBeInstanceOf(NullEmailVerification::class);
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
    config(['laravel-email-verification.default' => 'custom-default']);

    expect($manager->driver()->verify('user@example.com'))
        ->toBe(EmailVerificationStatus::Risky);
});

it('fails validation when the resolved driver reports the address undeliverable', function (): void {
    config(['laravel-email-verification.allowed_domains' => []]);

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

    expect($failures)->not->toBeEmpty();
});
