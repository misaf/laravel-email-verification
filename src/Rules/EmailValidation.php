<?php

declare(strict_types=1);

namespace Misaf\LaravelEmailValidation\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Illuminate\Translation\PotentiallyTranslatedString;
use Misaf\LaravelEmailValidation\EmailVerifierManager;
use Misaf\LaravelEmailValidation\Enums\EmailVerificationStatus;

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
            $fail(__('laravel-email-validation::validation.email.invalid'));

            return;
        }

        $allowedDomains = $this->allowedDomains();
        $domain = $this->getEmailHost($value);

        if ( ! $this->isAllowedDomain($domain, $allowedDomains)) {
            $fail(__('laravel-email-validation::validation.email.domain_not_allowed', [
                'domain'  => $domain,
                'allowed' => implode(', ', $allowedDomains),
            ]));

            return;
        }

        $status = app()->make(EmailVerifierManager::class)->verifier($this->verifier)->verify($value);

        match ($status) {
            EmailVerificationStatus::Deliverable   => null,
            EmailVerificationStatus::Undeliverable => $fail(__('laravel-email-validation::validation.email.undeliverable')),
            EmailVerificationStatus::Risky         => $fail(__('laravel-email-validation::validation.email.risky')),
            EmailVerificationStatus::Unverifiable  => $fail(__('laravel-email-validation::validation.email.service_unavailable')),
        };
    }

    private function getEmailHost(string $email): string
    {
        if ( ! Str::contains($email, '@')) {
            return '';
        }

        return Str::lower(Str::after($email, '@'));
    }

    /**
     * An empty allow-list imposes no domain restriction.
     *
     * @param  list<string>  $allowedDomains
     */
    private function isAllowedDomain(string $domain, array $allowedDomains): bool
    {
        if ([] === $allowedDomains) {
            return true;
        }

        return in_array($domain, $allowedDomains, true);
    }

    /**
     * @return list<string>
     */
    private function allowedDomains(): array
    {
        return array_values(array_filter(
            Config::array('laravel-email-validation.allowed_domains', []),
            static fn(mixed $item): bool => is_string($item),
        ));
    }
}
