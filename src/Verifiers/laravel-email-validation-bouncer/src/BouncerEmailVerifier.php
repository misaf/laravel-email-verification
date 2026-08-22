<?php

declare(strict_types=1);

namespace Misaf\LaravelEmailValidationBouncer;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Misaf\LaravelEmailValidation\Contracts\EmailVerifier;
use Misaf\LaravelEmailValidation\Enums\EmailVerificationStatus;
use Throwable;

/**
 * Verifies deliverability through the Bouncer API (https://usebouncer.com).
 */
final class BouncerEmailVerifier implements EmailVerifier
{
    /**
     * Server-side verification budget; must stay below the HTTP client timeout.
     */
    private const SERVER_TIMEOUT = 5;

    private const CLIENT_TIMEOUT = 6;

    public function __construct(
        private string $host,
        private string $apiKey,
    ) {}

    public function verify(string $email): EmailVerificationStatus
    {
        try {
            $response = Http::timeout(self::CLIENT_TIMEOUT)
                ->retry(2, 100)
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
                Log::error('Bouncer API returned an unexpected response.', ['email' => $email, 'response' => $payload]);

                return EmailVerificationStatus::Unverifiable;
            }

            return match ($status) {
                'deliverable'   => EmailVerificationStatus::Deliverable,
                'undeliverable' => EmailVerificationStatus::Undeliverable,
                'risky'         => EmailVerificationStatus::Risky,
                default         => EmailVerificationStatus::Unverifiable,
            };
        } catch (ConnectionException $e) {
            Log::error('Bouncer API connection timeout.', ['exception' => $e]);
        } catch (RequestException $e) {
            Log::error('Bouncer API request error.', ['exception' => $e]);
        } catch (Throwable $e) {
            Log::error('Unexpected Bouncer verification error.', ['exception' => $e]);
        }

        return EmailVerificationStatus::Unverifiable;
    }
}
