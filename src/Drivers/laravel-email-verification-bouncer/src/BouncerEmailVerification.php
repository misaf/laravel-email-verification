<?php

declare(strict_types=1);

namespace Misaf\LaravelEmailVerificationBouncer;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Misaf\LaravelEmailVerification\Contracts\EmailVerification;
use Misaf\LaravelEmailVerification\Enums\EmailVerificationStatus;
use Misaf\LaravelEmailVerification\Support\TransientFault;
use Throwable;

/**
 * Verifies deliverability through the Bouncer API (https://usebouncer.com).
 */
final class BouncerEmailVerification implements EmailVerification
{
    /**
     * Server-side verification budget; must stay below the HTTP client timeout.
     */
    private const int SERVER_TIMEOUT = 5;

    private const int CLIENT_TIMEOUT = 6;

    public function __construct(
        private string $host,
        private string $apiKey,
        private int $retryTimes = 2,
        private int $retrySleepMilliseconds = 100,
    ) {}

    public function verify(string $email): EmailVerificationStatus
    {
        try {
            $response = Http::timeout(self::CLIENT_TIMEOUT)
                ->retry(
                    $this->retryTimes,
                    $this->retrySleepMilliseconds,
                    TransientFault::shouldRetry(...),
                )
                ->withHeaders([
                    'accept'    => 'application/json',
                    'x-api-key' => $this->apiKey,
                ])
                ->get($this->host, [
                    'email'   => $email,
                    'timeout' => self::SERVER_TIMEOUT,
                ]);

            $payload = $response->ok() ? $response->json() : null;
            $status = is_array($payload) ? ($payload['status'] ?? null) : null;

            if ( ! is_string($status)) {
                Log::warning('Bouncer API returned an unexpected response.', ['status' => $response->status()]);

                return EmailVerificationStatus::Unverifiable;
            }

            return match ($status) {
                'deliverable'   => EmailVerificationStatus::Deliverable,
                'undeliverable' => EmailVerificationStatus::Undeliverable,
                'risky'         => EmailVerificationStatus::Risky,
                default         => EmailVerificationStatus::Unverifiable,
            };
        } catch (ConnectionException) {
            Log::warning('Bouncer API connection timeout.');
        } catch (RequestException $e) {
            $status = $e->response->status();

            // A rejected key stays broken until someone rotates it, so it earns
            // an error. Rate limits and server faults clear on their own.
            $level = in_array($status, [401, 403], true) ? 'error' : 'warning';

            Log::log($level, 'Bouncer API request error.', ['status' => $status]);
        } catch (Throwable $e) {
            Log::error('Unexpected Bouncer verification error.', [
                'exception' => $e::class,
                'message'   => $e->getMessage(),
            ]);
        }

        return EmailVerificationStatus::Unverifiable;
    }
}
