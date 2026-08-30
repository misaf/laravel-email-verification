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

/**
 * Enforces the configured domain allow-list, then deliverability through the
 * resolved driver. Email syntax is Laravel's job — pair this rule with the
 * framework's own `email` rule, which should run first under `bail`.
 */
final class EmailValidation implements ValidationRule
{
    public function __construct(private ?string $driver = null) {}

    /**
     * @param Closure(string, ?string=): PotentiallyTranslatedString $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ( ! is_string($value)) {
            $fail(__('email-verification::validation.email.invalid'));

            return;
        }

        $domain = $this->domain($value);

        if ( ! $this->isAllowedDomain($domain)) {
            $fail(__('email-verification::validation.email.domain_not_allowed', [
                'domain' => $domain,
            ]));

            return;
        }

        $status = EmailVerification::driver($this->driver)->verify($value);

        match ($status) {
            EmailVerificationStatus::Deliverable   => null,
            EmailVerificationStatus::Undeliverable => $fail(__('email-verification::validation.email.undeliverable')),
            EmailVerificationStatus::Risky         => $fail(__('email-verification::validation.email.risky')),
            EmailVerificationStatus::Unverifiable  => $fail(__('email-verification::validation.email.unverifiable')),
        };
    }

    private function domain(string $email): string
    {
        return Str::lower(Str::after($email, '@'));
    }

    private function isAllowedDomain(string $domain): bool
    {
        $allowedDomains = $this->allowedDomains();

        if ([] === $allowedDomains) {
            return true;
        }

        return in_array($domain, $allowedDomains, true);
    }

    /** @return array<array-key, string> */
    private function allowedDomains(): array
    {
        $allowedDomains = Config::array('email-verification.allowed_domains', []);

        foreach ($allowedDomains as $key => $domain) {
            if ( ! is_string($domain) || '' === $domain || trim($domain) !== $domain) {
                throw new InvalidArgumentException(
                    sprintf('The email-verification.allowed_domains value at key [%s] must be a non-empty string without surrounding whitespace.', $key),
                );
            }

            $allowedDomains[$key] = Str::lower($domain);
        }

        return $allowedDomains;
    }
}
