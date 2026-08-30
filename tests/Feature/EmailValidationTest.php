<?php

declare(strict_types=1);

use Misaf\LaravelEmailVerification\Contracts\EmailVerification;
use Misaf\LaravelEmailVerification\EmailVerificationManager;
use Misaf\LaravelEmailVerification\Enums\EmailVerificationStatus;
use Misaf\LaravelEmailVerification\Rules\EmailValidation;

/**
 * @return list<string>
 */
function emailValidationFailures(mixed $email, ?string $driver = null): array
{
    return validator(
        ['email' => $email],
        ['email' => [new EmailValidation($driver)]],
    )->errors()->get('email');
}

beforeEach(function (): void {
    config(['email-verification.allowed_domains' => ['example.com']]);
    config(['email-verification.default' => 'null']);
});

it('rejects a non-string value with a translated message', function (): void {
    expect(emailValidationFailures(['user@example.com']))
        ->toBe(['The email must be a valid email address.']);
});

it('matches an allowed domain against a mixed-case address', function (): void {
    expect(emailValidationFailures('User@EXAMPLE.com'))->toBeEmpty();
});

it('matches a mixed-case configured domain case-insensitively', function (): void {
    config(['email-verification.allowed_domains' => ['EXAMPLE.COM']]);

    expect(emailValidationFailures('User@example.com'))->toBeEmpty();
});

it('rejects a non-string configured domain', function (): void {
    config(['email-verification.allowed_domains' => ['example.com', 123]]);

    emailValidationFailures('user@example.com');
})->throws(
    InvalidArgumentException::class,
    'The email-verification.allowed_domains value at key [1] must be a non-empty string without surrounding whitespace.',
);

it('rejects an empty configured domain', function (): void {
    config(['email-verification.allowed_domains' => ['']]);

    emailValidationFailures('user@example.com');
})->throws(
    InvalidArgumentException::class,
    'The email-verification.allowed_domains value at key [0] must be a non-empty string without surrounding whitespace.',
);

it('rejects a configured domain with surrounding whitespace', function (): void {
    config(['email-verification.allowed_domains' => [' example.com ']]);

    emailValidationFailures('user@example.com');
})->throws(
    InvalidArgumentException::class,
    'The email-verification.allowed_domains value at key [0] must be a non-empty string without surrounding whitespace.',
);

it('rejects a disallowed domain', function (): void {
    expect(emailValidationFailures('user@blocked.test'))
        ->toBe(['The blocked.test domain is not allowed. Please try another email address.']);
});

it('imposes no domain restriction when the allow-list is empty', function (): void {
    config(['email-verification.allowed_domains' => []]);

    expect(emailValidationFailures('user@any-domain.test'))->toBeEmpty();
});

it('passes an allowed domain with the null driver', function (): void {
    expect(emailValidationFailures('user@example.com'))->toBeEmpty();
});

it('rejects an address reported as risky', function (): void {
    app()->make(EmailVerificationManager::class)->extend(
        'always-risky',
        fn(): EmailVerification => new class implements EmailVerification {
            public function verify(string $email): EmailVerificationStatus
            {
                return EmailVerificationStatus::Risky;
            }
        },
    );

    expect(emailValidationFailures('user@example.com', 'always-risky'))
        ->toBe(['This email may not be safely deliverable. Please provide another one.']);
});

it('rejects an address reported as unverifiable with a service unavailable message', function (): void {
    app()->make(EmailVerificationManager::class)->extend(
        'always-unverifiable',
        fn(): EmailVerification => new class implements EmailVerification {
            public function verify(string $email): EmailVerificationStatus
            {
                return EmailVerificationStatus::Unverifiable;
            }
        },
    );

    expect(emailValidationFailures('user@example.com', 'always-unverifiable'))
        ->toBe(['We are unable to check this email right now. Please try again later.']);
});
