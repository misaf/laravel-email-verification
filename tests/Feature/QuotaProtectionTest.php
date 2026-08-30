<?php

declare(strict_types=1);

use Misaf\LaravelEmailVerification\Contracts\EmailVerification;
use Misaf\LaravelEmailVerification\EmailVerificationManager;
use Misaf\LaravelEmailVerification\Enums\EmailVerificationStatus;
use Misaf\LaravelEmailVerification\Rules\EmailValidation;

/**
 * Deliverability verification costs paid API quota, so the allow-list has to
 * short-circuit before the driver is ever asked.
 */
function recordingDriver(): EmailVerification
{
    return new class implements EmailVerification {
        public int $calls = 0;

        public function verify(string $email): EmailVerificationStatus
        {
            $this->calls++;

            return EmailVerificationStatus::Deliverable;
        }
    };
}

/**
 * @param  list<mixed>  $leadingRules
 * @return list<string>
 */
function validateWithRecordingDriver(string $email, array $leadingRules = []): array
{
    return validator(
        ['email' => $email],
        ['email' => [...$leadingRules, new EmailValidation('recording')]],
    )->errors()->get('email');
}

beforeEach(function (): void {
    $driver = $this->driver = recordingDriver();

    app(EmailVerificationManager::class)->extend('recording', fn(): EmailVerification => $driver);

    config(['email-verification.allowed_domains' => ['example.com']]);
});

it('never calls the deliverability provider for a blocked domain', function (): void {
    expect(validateWithRecordingDriver('user@blocked.test'))
        ->toBe(['The blocked.test domain is not allowed. Please try another email address.'])
        ->and($this->driver->calls)->toBe(0);
});

it('calls the deliverability provider once for an allowed domain', function (): void {
    expect(validateWithRecordingDriver('user@example.com'))->toBeEmpty()
        ->and($this->driver->calls)->toBe(1);
});

/*
 * Syntax is Laravel's responsibility, not this package's. The documented rule
 * stack puts the framework's `email` rule first behind `bail`, so a malformed
 * address never reaches — and never bills — the provider. This proves the
 * boundary holds without any syntax parsing inside EmailValidation.
 */
it('never calls the deliverability provider for input Laravel rejects as malformed', function (): void {
    config(['email-verification.allowed_domains' => []]);

    expect(validateWithRecordingDriver('not-an-email', ['bail', 'email:rfc,strict']))
        ->toBe(['The email field must be a valid email address.'])
        ->and($this->driver->calls)->toBe(0);
});

it('calls the deliverability provider once for input Laravel accepts as well-formed', function (): void {
    expect(validateWithRecordingDriver('user@example.com', ['bail', 'email:rfc,strict']))->toBeEmpty()
        ->and($this->driver->calls)->toBe(1);
});
