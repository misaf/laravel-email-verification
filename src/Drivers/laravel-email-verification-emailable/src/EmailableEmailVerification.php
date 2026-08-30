<?php

declare(strict_types=1);

namespace Misaf\LaravelEmailVerificationEmailable;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Misaf\LaravelEmailVerification\Contracts\EmailVerification;
use Misaf\LaravelEmailVerification\Enums\EmailVerificationStatus;
use Throwable;

/**
 * Verifies deliverability through the Emailable API (https://emailable.com).
 */
final class EmailableEmailVerification implements EmailVerification
{
    public function __construct(
        private string $host,
        private string $apiKey,
        private int $serverTimeout = 5,
        private int $clientTimeout = 6,
        private int $retryTimes = 2,
        private int $retrySleepMilliseconds = 100,
    ) {}

    public function verify(string $email): EmailVerificationStatus
    {
        try {
            $response = Http::timeout($this->clientTimeout)
                ->retry(
                    $this->retryTimes,
                    $this->retrySleepMilliseconds,
                    $this->shouldRetry(...),
                )
                ->get($this->host, [
                    'api_key' => $this->apiKey,
                    'email'   => $email,
                    'timeout' => $this->serverTimeout,
                ]);

            $payload = $response->ok() ? $response->json() : null;
            $state = is_array($payload) ? ($payload['state'] ?? null) : null;

            if ( ! is_string($state)) {
                Log::warning('Emailable API returned an unexpected response.', ['status' => $response->status()]);

                return EmailVerificationStatus::Unverifiable;
            }

            return match ($state) {
                'deliverable'   => EmailVerificationStatus::Deliverable,
                'undeliverable' => EmailVerificationStatus::Undeliverable,
                'risky'         => EmailVerificationStatus::Risky,
                default         => EmailVerificationStatus::Unverifiable,
            };
        } catch (ConnectionException) {
            Log::warning('Emailable API connection timeout.');
        } catch (RequestException $e) {
            $status = $e->response->status();

            // A rejected key stays broken until someone rotates it, so it earns
            // an error. Rate limits and server faults clear on their own.
            $level = in_array($status, [401, 403], true) ? 'error' : 'warning';

            Log::log($level, 'Emailable API request error.', ['status' => $status]);
        } catch (Throwable $e) {
            Log::error('Unexpected Emailable verification error.', [
                'exception' => $e::class,
                'message'   => $e->getMessage(),
            ]);
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
