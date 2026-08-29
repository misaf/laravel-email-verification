<?php

declare(strict_types=1);

namespace Misaf\LaravelEmailVerificationBouncer;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Misaf\LaravelEmailVerification\Contracts\EmailVerification;
use Misaf\LaravelEmailVerification\Enums\EmailVerificationStatus;
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
    ) {}

    public function verify(string $email): EmailVerificationStatus
    {
        try {
            $response = Http::timeout(self::CLIENT_TIMEOUT)
                ->retry(2, 100, $this->shouldRetry(...))
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
                Log::error('Bouncer API returned an unexpected response.', ['status' => $response->status()]);

                return EmailVerificationStatus::Unverifiable;
            }

            return match ($status) {
                'deliverable'   => EmailVerificationStatus::Deliverable,
                'undeliverable' => EmailVerificationStatus::Undeliverable,
                'risky'         => EmailVerificationStatus::Risky,
                default         => EmailVerificationStatus::Unverifiable,
            };
        } catch (ConnectionException) {
            Log::error('Bouncer API connection timeout.');
        } catch (RequestException $e) {
            Log::error('Bouncer API request error.', ['status' => $e->response->status()]);
        } catch (Throwable $e) {
            Log::error('Unexpected Bouncer verification error.', ['exception' => $e::class]);
        }

        return EmailVerificationStatus::Unverifiable;
    }

    /**
     * Retry only faults that a later attempt could plausibly resolve: a
     * connection-level failure, or a server-side 5xx. Retrying a 4xx — a bad
     * key, a malformed address, or a 429 rate limit — burns paid API quota
     * without any chance of a different answer.
     */
    private function shouldRetry(Throwable $exception): bool
    {
        if ($exception instanceof ConnectionException) {
            return true;
        }

        return $exception instanceof RequestException
            && $exception->response->serverError();
    }
}
