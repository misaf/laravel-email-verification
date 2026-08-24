<?php

declare(strict_types=1);

use Misaf\LaravelEmailValidation\Contracts\EmailVerifier;
use Misaf\LaravelEmailValidation\EmailVerifierManager;
use Misaf\LaravelEmailValidation\Enums\EmailVerificationStatus;
use Misaf\LaravelEmailValidation\Rules\EmailValidation;

/**
 * @return list<string>
 */
function emailValidationFailures(mixed $email, ?string $verifier = null): array
{
    return validator(
        ['email' => $email],
        ['email' => [new EmailValidation($verifier)]],
    )->errors()->get('email');
}

beforeEach(function (): void {
    config(['services.email_validation.allowed_domains' => ['example.com']]);
    config(['laravel-email-validation.default' => 'null']);
});

it('rejects a non-string value with a translated message', function (): void {
    expect(emailValidationFailures(['user@example.com']))
        ->toBe([__('laravel-email-validation::validation.email.invalid')]);
});

it('matches an allowed domain against a mixed-case address', function (): void {
    expect(emailValidationFailures('User@EXAMPLE.com'))->toBeEmpty();
});

it('matches a mixed-case configured domain case-insensitively', function (): void {
    config(['services.email_validation.allowed_domains' => ['EXAMPLE.COM']]);

    expect(emailValidationFailures('User@example.com'))->toBeEmpty();
});

it('rejects a disallowed domain', function (): void {
    expect(emailValidationFailures('user@blocked.test'))->not->toBeEmpty();
});

it('imposes no domain restriction when the allow-list is empty', function (): void {
    config(['services.email_validation.allowed_domains' => []]);

    expect(emailValidationFailures('user@any-domain.test'))->toBeEmpty();
});

it('passes an allowed domain with the null driver', function (): void {
    expect(emailValidationFailures('user@example.com'))->toBeEmpty();
});

it('rejects an address reported as risky', function (): void {
    app()->make(EmailVerifierManager::class)->extend(
        'always-risky',
        fn(): EmailVerifier => new class implements EmailVerifier {
            public function verify(string $email): EmailVerificationStatus
            {
                return EmailVerificationStatus::Risky;
            }
        },
    );

    expect(emailValidationFailures('user@example.com', 'always-risky'))->not->toBeEmpty();
});

it('rejects an address reported as unverifiable with a service unavailable message', function (): void {
    app()->make(EmailVerifierManager::class)->extend(
        'always-unverifiable',
        fn(): EmailVerifier => new class implements EmailVerifier {
            public function verify(string $email): EmailVerificationStatus
            {
                return EmailVerificationStatus::Unverifiable;
            }
        },
    );

    expect(emailValidationFailures('user@example.com', 'always-unverifiable'))
        ->toBe([__('laravel-email-validation::validation.email.service_unavailable')]);
});
