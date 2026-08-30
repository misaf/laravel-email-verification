<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Misaf\LaravelEmailVerification\EmailVerificationManager;
use Misaf\LaravelEmailVerification\Enums\EmailVerificationStatus;

beforeEach(function (): void {
    config([
        'email-verification-emailable.host'    => 'https://api.emailable.test/verify',
        'email-verification-emailable.api_key' => 'test-key',
    ]);
});

it('sends the expected request to the configured endpoint', function (): void {
    Http::fake(['*' => Http::response(['state' => 'deliverable'], 200)]);

    app(EmailVerificationManager::class)->driver('emailable')->verify('user@example.com');

    Http::assertSent(function ($request): bool {
        return 'GET' === $request->method()
            && str_starts_with($request->url(), 'https://api.emailable.test/verify?')
            && 'test-key' === $request['api_key']
            && 'user@example.com' === $request['email']
            && 5 === $request['timeout'];
    });
});

it('maps a deliverable response', function (): void {
    Http::fake(['*' => Http::response(['state' => 'deliverable'], 200)]);

    expect(app(EmailVerificationManager::class)->driver('emailable')->verify('user@example.com'))
        ->toBe(EmailVerificationStatus::Deliverable);
});

it('maps an undeliverable response', function (): void {
    Http::fake(['*' => Http::response(['state' => 'undeliverable'], 200)]);

    expect(app(EmailVerificationManager::class)->driver('emailable')->verify('user@example.com'))
        ->toBe(EmailVerificationStatus::Undeliverable);
});

it('maps a risky response', function (): void {
    Http::fake(['*' => Http::response(['state' => 'risky'], 200)]);

    expect(app(EmailVerificationManager::class)->driver('emailable')->verify('user@example.com'))
        ->toBe(EmailVerificationStatus::Risky);
});

it('maps an unknown response to unverifiable', function (): void {
    Http::fake(['*' => Http::response(['state' => 'unknown'], 200)]);

    expect(app(EmailVerificationManager::class)->driver('emailable')->verify('user@example.com'))
        ->toBe(EmailVerificationStatus::Unverifiable);
});

it('treats a failed request as unverifiable', function (): void {
    Http::fake(['*' => Http::response(null, 500)]);

    expect(app(EmailVerificationManager::class)->driver('emailable')->verify('user@example.com'))
        ->toBe(EmailVerificationStatus::Unverifiable);
});

it('treats an unexpected payload as unverifiable', function (): void {
    Http::fake(['*' => Http::response(['unexpected' => true], 200)]);
    Log::shouldReceive('warning')
        ->once()
        ->with('Emailable API returned an unexpected response.', ['status' => 200]);

    expect(app(EmailVerificationManager::class)->driver('emailable')->verify('user@example.com'))
        ->toBe(EmailVerificationStatus::Unverifiable);
});

it('does not retry a client error', function (): void {
    Http::fake(['*' => Http::response(['error' => 'Too Many Requests'], 429)]);

    expect(app(EmailVerificationManager::class)->driver('emailable')->verify('user@example.com'))
        ->toBe(EmailVerificationStatus::Unverifiable);

    Http::assertSentCount(1);
});

it('retries a server error before giving up', function (): void {
    Http::fake(['*' => Http::response(null, 500)]);

    expect(app(EmailVerificationManager::class)->driver('emailable')->verify('user@example.com'))
        ->toBe(EmailVerificationStatus::Unverifiable);

    Http::assertSentCount(2);
});

it('retries a connection failure before returning unverifiable', function (): void {
    Http::fake(['*' => Http::failedConnection('Connection failed.')]);
    Log::shouldReceive('warning')
        ->once()
        ->with('Emailable API connection timeout.');

    expect(app(EmailVerificationManager::class)->driver('emailable')->verify('user@example.com'))
        ->toBe(EmailVerificationStatus::Unverifiable);

    Http::assertSentCount(2);
});

it('handles an unexpected client exception', function (): void {
    Http::fake(fn() => throw new RuntimeException('Unexpected failure.'));
    Log::shouldReceive('error')
        ->once()
        ->with('Unexpected Emailable verification error.', [
            'exception' => RuntimeException::class,
            'message'   => 'Unexpected failure.',
        ]);

    expect(app(EmailVerificationManager::class)->driver('emailable')->verify('user@example.com'))
        ->toBe(EmailVerificationStatus::Unverifiable);
});

it('honours the package attempt budget', function (): void {
    config([
        'email-verification-emailable.retry.times'              => 3,
        'email-verification-emailable.retry.sleep_milliseconds' => 0,
    ]);
    Http::fake(['*' => Http::response(null, 500)]);

    expect(app(EmailVerificationManager::class)->driver('emailable')->verify('user@example.com'))
        ->toBe(EmailVerificationStatus::Unverifiable);

    Http::assertSentCount(3);
});

it('sends the configured server-side timeout', function (): void {
    config(['email-verification-emailable.timeout.server' => 9]);
    Http::fake(['*' => Http::response(['state' => 'deliverable'], 200)]);

    app(EmailVerificationManager::class)->driver('emailable')->verify('user@example.com');

    Http::assertSent(fn($request): bool => 9 === $request['timeout']);
});

it('applies the configured client timeout', function (): void {
    config(['email-verification-emailable.timeout.client' => 11]);

    $timeout = null;
    Http::fake(function ($request, array $options) use (&$timeout) {
        $timeout = $options['timeout'] ?? null;

        return Http::response(['state' => 'deliverable'], 200);
    });

    app(EmailVerificationManager::class)->driver('emailable')->verify('user@example.com');

    expect($timeout)->toBe(11);
});
