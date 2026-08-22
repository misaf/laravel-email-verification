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
    config(['laravel-email-validation.allowed_domains' => ['example.com']]);
    config(['laravel-email-validation.default' => 'null']);
});

it('rejects a non-string value', function (): void {
    expect(emailValidationFailures(['user@example.com']))->not->toBeEmpty();
});

it('rejects a disallowed domain', function (): void {
    expect(emailValidationFailures('user@blocked.test'))->not->toBeEmpty();
});

it('imposes no domain restriction when the allow-list is empty', function (): void {
    config(['laravel-email-validation.allowed_domains' => []]);

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
