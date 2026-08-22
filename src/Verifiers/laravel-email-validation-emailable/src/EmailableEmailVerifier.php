<?php

declare(strict_types=1);

namespace Misaf\LaravelEmailValidationEmailable;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Misaf\LaravelEmailValidation\Contracts\EmailVerifier;
use Misaf\LaravelEmailValidation\Enums\EmailVerificationStatus;
use Throwable;

/**
 * Verifies deliverability through the Emailable API (https://emailable.com).
 */
final class EmailableEmailVerifier implements EmailVerifier
{
    public function __construct(
        private string $host,
        private string $apiKey,
    ) {}

    public function verify(string $email): EmailVerificationStatus
    {
        try {
            $response = Http::timeout(6)
                ->retry(2, 100, $this->shouldRetry(...))
                ->get($this->host, [
                    'api_key' => $this->apiKey,
                    'email'   => $email,
                ]);

            $payload = $response->ok() ? $response->json() : null;

            if ( ! is_array($payload) || ! isset($payload['state'])) {
                Log::error('Emailable API returned an unexpected response.', ['email' => $email, 'response' => $payload]);

                return EmailVerificationStatus::Unverifiable;
            }

            return match ($payload['state']) {
                'deliverable'   => EmailVerificationStatus::Deliverable,
                'undeliverable' => EmailVerificationStatus::Undeliverable,
                'risky'         => EmailVerificationStatus::Risky,
                default         => EmailVerificationStatus::Unverifiable,
            };
        } catch (ConnectionException $e) {
            Log::error('Emailable API connection timeout.', ['exception' => $e]);
        } catch (RequestException $e) {
            Log::error('Emailable API request error.', ['exception' => $e]);
        } catch (Throwable $e) {
            Log::error('Unexpected Emailable verification error.', ['exception' => $e]);
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
