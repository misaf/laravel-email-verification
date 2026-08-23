<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Misaf\LaravelEmailValidation\EmailVerifierManager;
use Misaf\LaravelEmailValidation\Enums\EmailVerificationStatus;
use Misaf\LaravelEmailValidationBouncer\BouncerEmailVerifier;

beforeEach(function (): void {
    config([
        'laravel-email-validation-bouncer.host'    => 'https://api.usebouncer.test/v1.1/email/verify',
        'laravel-email-validation-bouncer.api_key' => 'test-key',
    ]);
});

it('registers the bouncer driver on the manager', function (): void {
    expect(app(EmailVerifierManager::class)->driver('bouncer'))
        ->toBeInstanceOf(BouncerEmailVerifier::class);
});

it('authenticates with an x-api-key header and sends the email as a query parameter', function (): void {
    Http::fake(['*' => Http::response(['status' => 'deliverable'], 200)]);

    app(EmailVerifierManager::class)->driver('bouncer')->verify('user@example.com');

    Http::assertSent(function ($request): bool {
        return $request->hasHeader('x-api-key', 'test-key')
            && 'user@example.com' === $request['email']
            && is_numeric($request['timeout']);
    });
});

it('maps a deliverable response', function (): void {
    Http::fake(['*' => Http::response(['status' => 'deliverable', 'reason' => 'accepted_email'], 200)]);

    expect(app(EmailVerifierManager::class)->driver('bouncer')->verify('user@example.com'))
        ->toBe(EmailVerificationStatus::Deliverable);
});

it('maps an undeliverable response', function (): void {
    Http::fake(['*' => Http::response(['status' => 'undeliverable', 'reason' => 'rejected_email'], 200)]);

    expect(app(EmailVerifierManager::class)->driver('bouncer')->verify('user@example.com'))
        ->toBe(EmailVerificationStatus::Undeliverable);
});

it('maps a risky response', function (): void {
    Http::fake(['*' => Http::response(['status' => 'risky', 'reason' => 'low_quality'], 200)]);

    expect(app(EmailVerifierManager::class)->driver('bouncer')->verify('user@example.com'))
        ->toBe(EmailVerificationStatus::Risky);
});

it('maps an unknown response to unverifiable', function (): void {
    Http::fake(['*' => Http::response(['status' => 'unknown', 'reason' => 'timeout'], 200)]);

    expect(app(EmailVerifierManager::class)->driver('bouncer')->verify('user@example.com'))
        ->toBe(EmailVerificationStatus::Unverifiable);
});

it('treats a failed request as unverifiable', function (): void {
    Http::fake(['*' => Http::response(null, 500)]);

    expect(app(EmailVerifierManager::class)->driver('bouncer')->verify('user@example.com'))
        ->toBe(EmailVerificationStatus::Unverifiable);
});

it('treats a rate limited request as unverifiable', function (): void {
    Http::fake(['*' => Http::response(['error' => 'Too Many Requests'], 429)]);

    expect(app(EmailVerifierManager::class)->driver('bouncer')->verify('user@example.com'))
        ->toBe(EmailVerificationStatus::Unverifiable);
});

it('treats a malformed payload as unverifiable', function (): void {
    Http::fake(['*' => Http::response(['unexpected' => true], 200)]);
    Log::shouldReceive('error')
        ->once()
        ->with('Bouncer API returned an unexpected response.', ['status' => 200]);

    expect(app(EmailVerifierManager::class)->driver('bouncer')->verify('user@example.com'))
        ->toBe(EmailVerificationStatus::Unverifiable);
});

it('does not retry a client error', function (): void {
    Http::fake(['*' => Http::response(['error' => 'Too Many Requests'], 429)]);

    expect(app(EmailVerifierManager::class)->driver('bouncer')->verify('user@example.com'))
        ->toBe(EmailVerificationStatus::Unverifiable);

    Http::assertSentCount(1);
});

it('retries a server error before giving up', function (): void {
    Http::fake(['*' => Http::response(null, 500)]);

    expect(app(EmailVerifierManager::class)->driver('bouncer')->verify('user@example.com'))
        ->toBe(EmailVerificationStatus::Unverifiable);

    Http::assertSentCount(2);
});
