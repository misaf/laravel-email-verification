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
 * @return list<string>
 */
function validateWithRecordingDriver(string $email): array
{
    return validator(
        ['email' => $email],
        ['email' => [new EmailValidation('recording')]],
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
