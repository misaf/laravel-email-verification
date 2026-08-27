<?php

declare(strict_types=1);

use Misaf\LaravelEmailVerification\Contracts\EmailVerifier;
use Misaf\LaravelEmailVerification\Drivers\NullEmailVerifier;
use Misaf\LaravelEmailVerification\EmailVerifierManager;
use Misaf\LaravelEmailVerification\Enums\EmailVerificationStatus;
use Misaf\LaravelEmailVerification\Rules\EmailValidation;

function manager(): EmailVerifierManager
{
    return app(EmailVerifierManager::class);
}

it('resolves the null driver as the default', function (): void {
    config(['laravel-email-verification.default' => 'null']);

    expect(manager()->driver())->toBeInstanceOf(NullEmailVerifier::class)
        ->and(manager()->driver()->verify('user@example.com'))->toBe(EmailVerificationStatus::Deliverable);
});

it('falls back to the null driver when the package config is missing', function (): void {
    config(['laravel-email-verification' => []]);

    expect(manager()->driver())->toBeInstanceOf(NullEmailVerifier::class);
});

it('supports driver packages registering via extend', function (): void {
    $manager = manager();
    $manager->extend('always-undeliverable', fn(): EmailVerifier => new class implements EmailVerifier {
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
    $manager->extend('custom-default', fn(): EmailVerifier => new class implements EmailVerifier {
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

    manager()->extend('always-undeliverable', fn(): EmailVerifier => new class implements EmailVerifier {
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
