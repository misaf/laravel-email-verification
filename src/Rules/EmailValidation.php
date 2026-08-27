<?php

declare(strict_types=1);

namespace Misaf\LaravelEmailVerification\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Illuminate\Translation\PotentiallyTranslatedString;
use Misaf\LaravelEmailVerification\EmailVerifierManager;
use Misaf\LaravelEmailVerification\Enums\EmailVerificationStatus;

final class EmailValidation implements ValidationRule
{
    /**
     * @param  string|null  $verifier  Deliverability driver name; null uses the configured default.
     */
    public function __construct(private ?string $verifier = null) {}

    /**
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ( ! is_string($value)) {
            $fail(__('laravel-email-verification::validation.email.invalid'));

            return;
        }

        $allowedDomains = $this->allowedDomains();
        $domain = $this->getEmailHost($value);

        if ( ! $this->isAllowedDomain($domain, $allowedDomains)) {
            $fail(__('laravel-email-verification::validation.email.domain_not_allowed', [
                'domain' => $domain,
            ]));

            return;
        }

        $status = app()->make(EmailVerifierManager::class)->verifier($this->verifier)->verify($value);

        match ($status) {
            EmailVerificationStatus::Deliverable   => null,
            EmailVerificationStatus::Undeliverable => $fail(__('laravel-email-verification::validation.email.undeliverable')),
            EmailVerificationStatus::Risky         => $fail(__('laravel-email-verification::validation.email.risky')),
            EmailVerificationStatus::Unverifiable  => $fail(__('laravel-email-verification::validation.email.service_unavailable')),
        };
    }

    private function getEmailHost(string $email): string
    {
        return Str::lower(Str::after($email, '@'));
    }

    /**
     * An empty allow-list imposes no domain restriction.
     *
     * @param  array<array-key, mixed>  $allowedDomains
     */
    private function isAllowedDomain(string $domain, array $allowedDomains): bool
    {
        if ([] === $allowedDomains) {
            return true;
        }

        return in_array($domain, $allowedDomains, true);
    }

    /** @return array<array-key, mixed> */
    private function allowedDomains(): array
    {
        return array_map(
            static fn(mixed $domain): mixed => is_string($domain) ? Str::lower($domain) : $domain,
            Config::array('laravel-email-verification.allowed_domains', []),
        );
    }
}
