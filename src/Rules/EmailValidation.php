<?php

declare(strict_types=1);

namespace Misaf\LaravelEmailVerification\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Illuminate\Translation\PotentiallyTranslatedString;
use InvalidArgumentException;
use Misaf\LaravelEmailVerification\Enums\EmailVerificationStatus;
use Misaf\LaravelEmailVerification\Facades\EmailVerification;

final class EmailValidation implements ValidationRule
{
    /**
     * @param string|null $driver
     */
    public function __construct(private ?string $driver = null) {}

    /**
     * @param Closure(string, ?string=): PotentiallyTranslatedString $fail
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

        $status = EmailVerification::driver($this->driver)->verify($value);

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
     * @param array<array-key, mixed> $allowedDomains
     */
    private function isAllowedDomain(string $domain, array $allowedDomains): bool
    {
        if ([] === $allowedDomains) {
            return true;
        }

        return in_array($domain, $allowedDomains, true);
    }

    /** @return array<array-key, string> */
    private function allowedDomains(): array
    {
        $allowedDomains = Config::array('laravel-email-verification.allowed_domains', []);

        foreach ($allowedDomains as $key => $domain) {
            if ( ! is_string($domain) || '' === $domain || mb_trim($domain) !== $domain) {
                throw new InvalidArgumentException(
                    sprintf('The laravel-email-verification.allowed_domains value at key [%s] must be a non-empty string without surrounding whitespace.', $key),
                );
            }

            $allowedDomains[$key] = Str::lower($domain);
        }

        return $allowedDomains;
    }
}
