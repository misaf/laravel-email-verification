<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Misaf\LaravelEmailVerification\EmailVerificationManager;
use Misaf\LaravelEmailVerification\Enums\EmailVerificationStatus;
use Misaf\LaravelEmailVerificationBouncer\BouncerEmailVerification;

beforeEach(function (): void {
    config([
        'laravel-email-verification-bouncer.host'    => 'https://api.usebouncer.test/v1.1/email/verify',
        'laravel-email-verification-bouncer.api_key' => 'test-key',
    ]);
});

it('registers the bouncer driver on the manager', function (): void {
    expect(app(EmailVerificationManager::class)->driver('bouncer'))
        ->toBeInstanceOf(BouncerEmailVerification::class);
});

it('sends the expected request to the configured endpoint', function (): void {
    Http::fake(['*' => Http::response(['status' => 'deliverable'], 200)]);

    app(EmailVerificationManager::class)->driver('bouncer')->verify('user@example.com');

    Http::assertSent(function ($request): bool {
        return 'GET' === $request->method()
            && str_starts_with($request->url(), 'https://api.usebouncer.test/v1.1/email/verify?')
            && $request->hasHeader('accept', 'application/json')
            && $request->hasHeader('x-api-key', 'test-key')
            && 'user@example.com' === $request['email']
            && 5 === $request['timeout'];
    });
});

it('maps a deliverable response', function (): void {
    Http::fake(['*' => Http::response(['status' => 'deliverable', 'reason' => 'accepted_email'], 200)]);

    expect(app(EmailVerificationManager::class)->driver('bouncer')->verify('user@example.com'))
        ->toBe(EmailVerificationStatus::Deliverable);
});

it('maps an undeliverable response', function (): void {
    Http::fake(['*' => Http::response(['status' => 'undeliverable', 'reason' => 'rejected_email'], 200)]);

    expect(app(EmailVerificationManager::class)->driver('bouncer')->verify('user@example.com'))
        ->toBe(EmailVerificationStatus::Undeliverable);
});

it('maps a risky response', function (): void {
    Http::fake(['*' => Http::response(['status' => 'risky', 'reason' => 'low_quality'], 200)]);

    expect(app(EmailVerificationManager::class)->driver('bouncer')->verify('user@example.com'))
        ->toBe(EmailVerificationStatus::Risky);
});

it('maps an unknown response to unverifiable', function (): void {
    Http::fake(['*' => Http::response(['status' => 'unknown', 'reason' => 'timeout'], 200)]);

    expect(app(EmailVerificationManager::class)->driver('bouncer')->verify('user@example.com'))
        ->toBe(EmailVerificationStatus::Unverifiable);
});

it('treats a failed request as unverifiable', function (): void {
    Http::fake(['*' => Http::response(null, 500)]);

    expect(app(EmailVerificationManager::class)->driver('bouncer')->verify('user@example.com'))
        ->toBe(EmailVerificationStatus::Unverifiable);
});

it('treats a rate limited request as unverifiable', function (): void {
    Http::fake(['*' => Http::response(['error' => 'Too Many Requests'], 429)]);

    expect(app(EmailVerificationManager::class)->driver('bouncer')->verify('user@example.com'))
        ->toBe(EmailVerificationStatus::Unverifiable);
});

it('treats a malformed payload as unverifiable', function (): void {
    Http::fake(['*' => Http::response(['unexpected' => true], 200)]);
    Log::shouldReceive('warning')
        ->once()
        ->with('Bouncer API returned an unexpected response.', ['status' => 200]);

    expect(app(EmailVerificationManager::class)->driver('bouncer')->verify('user@example.com'))
        ->toBe(EmailVerificationStatus::Unverifiable);
});

it('does not retry a client error', function (): void {
    Http::fake(['*' => Http::response(['error' => 'Too Many Requests'], 429)]);

    expect(app(EmailVerificationManager::class)->driver('bouncer')->verify('user@example.com'))
        ->toBe(EmailVerificationStatus::Unverifiable);

    Http::assertSentCount(1);
});

it('retries a server error before giving up', function (): void {
    Http::fake(['*' => Http::response(null, 500)]);

    expect(app(EmailVerificationManager::class)->driver('bouncer')->verify('user@example.com'))
        ->toBe(EmailVerificationStatus::Unverifiable);

    Http::assertSentCount(2);
});

it('retries a connection failure before returning unverifiable', function (): void {
    Http::fake(['*' => Http::failedConnection('Connection failed.')]);
    Log::shouldReceive('warning')
        ->once()
        ->with('Bouncer API connection timeout.');

    expect(app(EmailVerificationManager::class)->driver('bouncer')->verify('user@example.com'))
        ->toBe(EmailVerificationStatus::Unverifiable);

    Http::assertSentCount(2);
});

it('handles an unexpected client exception', function (): void {
    Http::fake(fn() => throw new RuntimeException('Unexpected failure.'));
    Log::shouldReceive('error')
        ->once()
        ->with('Unexpected Bouncer verification error.', [
            'exception' => RuntimeException::class,
            'message'   => 'Unexpected failure.',
        ]);

    expect(app(EmailVerificationManager::class)->driver('bouncer')->verify('user@example.com'))
        ->toBe(EmailVerificationStatus::Unverifiable);
});

it('honours a configured attempt budget', function (): void {
    config([
        'laravel-email-verification.retry.times'              => 3,
        'laravel-email-verification.retry.sleep_milliseconds' => 0,
    ]);
    Http::fake(['*' => Http::response(null, 500)]);

    expect(app(EmailVerificationManager::class)->driver('bouncer')->verify('user@example.com'))
        ->toBe(EmailVerificationStatus::Unverifiable);

    Http::assertSentCount(3);
});
