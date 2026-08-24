<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Misaf\LaravelEmailValidation\EmailVerifierManager;
use Misaf\LaravelEmailValidation\Enums\EmailVerificationStatus;
use Misaf\LaravelEmailValidation\Rules\EmailValidation;
use Misaf\LaravelEmailValidation\Verifiers\NullEmailVerifier;
use Misaf\LaravelEmailValidationBouncer\BouncerEmailVerifier;
use Misaf\LaravelEmailValidationEmailable\EmailableEmailVerifier;

beforeEach(function (): void {
    config([
        'services.email_validation.allowed_domains'  => [],
        'laravel-email-validation-bouncer.host'      => 'https://api.usebouncer.test/v1.1/email/verify',
        'laravel-email-validation-bouncer.api_key'   => 'bouncer-key',
        'laravel-email-validation-emailable.host'    => 'https://api.emailable.test/verify',
        'laravel-email-validation-emailable.api_key' => 'emailable-key',
    ]);
});

it('resolves both drivers from the same manager', function (): void {
    $manager = app(EmailVerifierManager::class);

    expect($manager->driver('bouncer'))->toBeInstanceOf(BouncerEmailVerifier::class)
        ->and($manager->driver('emailable'))->toBeInstanceOf(EmailableEmailVerifier::class)
        ->and($manager->driver('null'))->toBeInstanceOf(NullEmailVerifier::class);
});

it('lets each driver be selected per rule instance while both are installed', function (): void {
    Http::fake([
        'api.usebouncer.test/*'  => Http::response(['status' => 'undeliverable'], 200),
        'api.emailable.test/*'   => Http::response(['state' => 'deliverable'], 200),
    ]);

    $failures = static function (string $driver): array {
        return validator(
            ['email' => 'user@example.com'],
            ['email' => [new EmailValidation($driver)]],
        )->errors()->get('email');
    };

    expect($failures('bouncer'))->not->toBeEmpty()
        ->and($failures('emailable'))->toBeEmpty();
});

it('honours the configured default driver when both are installed', function (): void {
    Http::fake(['*' => Http::response(['state' => 'risky'], 200)]);
    config(['laravel-email-validation.default' => 'emailable']);

    expect(app(EmailVerifierManager::class)->verifier()->verify('user@example.com'))
        ->toBe(EmailVerificationStatus::Risky);
});
