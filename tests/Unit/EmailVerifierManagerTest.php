<?php

declare(strict_types=1);

use Misaf\LaravelEmailValidation\Contracts\EmailVerifier;
use Misaf\LaravelEmailValidation\EmailVerifierManager;
use Misaf\LaravelEmailValidation\Enums\EmailVerificationStatus;
use Misaf\LaravelEmailValidation\Rules\EmailValidation;
use Misaf\LaravelEmailValidation\Verifiers\NullEmailVerifier;

function manager(): EmailVerifierManager
{
    return app(EmailVerifierManager::class);
}

it('resolves the null driver as the default', function (): void {
    config(['laravel-email-validation.default' => 'null']);

    expect(manager()->driver())->toBeInstanceOf(NullEmailVerifier::class)
        ->and(manager()->driver()->verify('user@example.com'))->toBe(EmailVerificationStatus::Deliverable);
});

it('supports driver packages registering via extend', function (): void {
    $manager = manager();
    $manager->extend('always-undeliverable', fn(): EmailVerifier => new class () implements EmailVerifier {
        public function verify(string $email): EmailVerificationStatus
        {
            return EmailVerificationStatus::Undeliverable;
        }
    });

    expect($manager->driver('always-undeliverable')->verify('user@example.com'))
        ->toBe(EmailVerificationStatus::Undeliverable);
});

it('fails validation when the resolved driver reports the address undeliverable', function (): void {
    config(['laravel-email-validation.allowed_domains' => []]);

    manager()->extend('always-undeliverable', fn(): EmailVerifier => new class () implements EmailVerifier {
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
