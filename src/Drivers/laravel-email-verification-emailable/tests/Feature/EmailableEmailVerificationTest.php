<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Misaf\LaravelEmailVerification\EmailVerificationManager;
use Misaf\LaravelEmailVerification\Enums\EmailVerificationStatus;
use Misaf\LaravelEmailVerificationEmailable\EmailableEmailVerification;

beforeEach(function (): void {
    config([
        'laravel-email-verification-emailable.host'    => 'https://api.emailable.test/verify',
        'laravel-email-verification-emailable.api_key' => 'test-key',
    ]);
});

it('registers the emailable driver on the manager', function (): void {
    expect(app(EmailVerificationManager::class)->driver('emailable'))
        ->toBeInstanceOf(EmailableEmailVerification::class);
});

it('sends the expected request to the configured endpoint', function (): void {
    Http::fake(['*' => Http::response(['state' => 'deliverable'], 200)]);

    app(EmailVerificationManager::class)->driver('emailable')->verify('user@example.com');

    Http::assertSent(function ($request): bool {
        return 'GET' === $request->method()
            && str_starts_with($request->url(), 'https://api.emailable.test/verify?')
            && 'test-key' === $request['api_key']
            && 'user@example.com' === $request['email'];
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
    Log::shouldReceive('error')
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
    Log::shouldReceive('error')
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
        ->with('Unexpected Emailable verification error.', ['exception' => RuntimeException::class]);

    expect(app(EmailVerificationManager::class)->driver('emailable')->verify('user@example.com'))
        ->toBe(EmailVerificationStatus::Unverifiable);
});
