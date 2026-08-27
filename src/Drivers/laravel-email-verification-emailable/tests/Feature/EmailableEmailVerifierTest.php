<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Misaf\LaravelEmailVerification\EmailVerifierManager;
use Misaf\LaravelEmailVerification\Enums\EmailVerificationStatus;
use Misaf\LaravelEmailVerificationEmailable\EmailableEmailVerifier;

beforeEach(function (): void {
    config([
        'laravel-email-verification-emailable.host'    => 'https://api.emailable.test/verify',
        'laravel-email-verification-emailable.api_key' => 'test-key',
    ]);
});

it('registers the emailable driver on the manager', function (): void {
    expect(app(EmailVerifierManager::class)->driver('emailable'))
        ->toBeInstanceOf(EmailableEmailVerifier::class);
});

it('maps a deliverable response', function (): void {
    Http::fake(['*' => Http::response(['state' => 'deliverable'], 200)]);

    expect(app(EmailVerifierManager::class)->driver('emailable')->verify('user@example.com'))
        ->toBe(EmailVerificationStatus::Deliverable);
});

it('maps an undeliverable response', function (): void {
    Http::fake(['*' => Http::response(['state' => 'undeliverable'], 200)]);

    expect(app(EmailVerifierManager::class)->driver('emailable')->verify('user@example.com'))
        ->toBe(EmailVerificationStatus::Undeliverable);
});

it('maps a risky response', function (): void {
    Http::fake(['*' => Http::response(['state' => 'risky'], 200)]);

    expect(app(EmailVerifierManager::class)->driver('emailable')->verify('user@example.com'))
        ->toBe(EmailVerificationStatus::Risky);
});

it('maps an unknown response to unverifiable', function (): void {
    Http::fake(['*' => Http::response(['state' => 'unknown'], 200)]);

    expect(app(EmailVerifierManager::class)->driver('emailable')->verify('user@example.com'))
        ->toBe(EmailVerificationStatus::Unverifiable);
});

it('treats a failed request as unverifiable', function (): void {
    Http::fake(['*' => Http::response(null, 500)]);

    expect(app(EmailVerifierManager::class)->driver('emailable')->verify('user@example.com'))
        ->toBe(EmailVerificationStatus::Unverifiable);
});

it('treats an unexpected payload as unverifiable', function (): void {
    Http::fake(['*' => Http::response(['unexpected' => true], 200)]);
    Log::shouldReceive('error')
        ->once()
        ->with('Emailable API returned an unexpected response.', ['status' => 200]);

    expect(app(EmailVerifierManager::class)->driver('emailable')->verify('user@example.com'))
        ->toBe(EmailVerificationStatus::Unverifiable);
});

it('does not retry a client error', function (): void {
    Http::fake(['*' => Http::response(['error' => 'Too Many Requests'], 429)]);

    expect(app(EmailVerifierManager::class)->driver('emailable')->verify('user@example.com'))
        ->toBe(EmailVerificationStatus::Unverifiable);

    Http::assertSentCount(1);
});

it('retries a server error before giving up', function (): void {
    Http::fake(['*' => Http::response(null, 500)]);

    expect(app(EmailVerifierManager::class)->driver('emailable')->verify('user@example.com'))
        ->toBe(EmailVerificationStatus::Unverifiable);

    Http::assertSentCount(2);
});
