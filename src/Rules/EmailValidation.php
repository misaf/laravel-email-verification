<?php

declare(strict_types=1);

namespace Misaf\LaravelEmailValidation\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Misaf\LaravelEmailValidation\EmailVerifierManager;
use Misaf\LaravelEmailValidation\Enums\EmailVerificationStatus;

final class EmailValidation implements ValidationRule
{
    /** @var list<string> */
    private array $allowedDomains;

    /**
     * @param string|null $verifier Deliverability driver name; null uses the configured default.
     */
    public function __construct(private ?string $verifier = null)
    {
        $raw = Config::array('laravel-email-validation.allowed_domains', []);

        $stringsOnly = array_filter(
            $raw,
            static fn($item): bool => is_string($item),
        );

        $this->allowedDomains = array_values($stringsOnly);
    }

    /**
     * @param string $attribute
     * @param mixed $value
     * @param Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     * @return void
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ( ! is_string($value)) {
            $fail($attribute);

            return;
        }

        $domain = $this->getEmailHost($value);

        if ( ! $this->isAllowedDomain($domain)) {
            Log::error('Email domain not allowed.', ['attribute' => $attribute, 'email' => $value, 'domain' => $domain]);
            $fail(__('laravel-email-validation::validation.email.domain_not_allowed', [
                'domain'  => $domain,
                'allowed' => implode(', ', $this->allowedDomains),
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
        return Str::lower(Str::after($email, '@'));
    }

    /**
     * An empty allow-list imposes no domain restriction.
     */
    private function isAllowedDomain(string $domain): bool
    {
        if ([] === $this->allowedDomains) {
            return true;
        }

        return in_array($domain, $this->allowedDomains, true);
    }
}
