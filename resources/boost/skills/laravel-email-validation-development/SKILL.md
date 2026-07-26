---
name: laravel-email-validation-development
description: "Create, modify, review, or test the provider-neutral Laravel Email Validation core package in the package root. Trigger for EmailVerifier, EmailVerifierManager, EmailVerificationStatus, EmailValidation, NullEmailVerifier, custom email-verification drivers, allowed email domains, deliverability validation, or the laravel-email-validation configuration and translations."
---

# Laravel Email Validation

## Workflow

Use `laravel-best-practices` for Laravel PHP and `pest-testing` whenever tests change. Before code changes, use Laravel Boost `application-info` and `search-docs`. Use `laravel-email-validation-emailable-development` as well when changing the Emailable provider.

## Module Boundary

Treat `the package root` as the standalone, provider-neutral core.

- Use namespace `Misaf\LaravelEmailValidation`.
- Own `Contracts\EmailVerifier`, `EmailVerifierManager`, `EmailVerificationStatus`, the `EmailVerifier` facade, `EmailValidation`, and `NullEmailVerifier`.
- Do not import or require a concrete provider, another domain package.
- Keep provider dependencies one-way: a provider package requires this core package and registers a driver through `EmailVerifierManager::extend()`.
- Keep public contracts, enum semantics, manager APIs, config keys, and translation keys stable.

## Verification Semantics

- `Deliverable` means the provider positively classified the address as deliverable.
- `Undeliverable` means the provider positively classified the address as invalid or non-deliverable.
- `Risky` means the address may accept mail but is not safe enough to pass the validation rule.
- `Unverifiable` means verification failed or the provider could not determine a reliable result. Never silently convert an unknown provider state, malformed payload, timeout, or provider error to `Deliverable`.
- Keep the `null` driver as the default provider-neutral fallback. It performs no external request and returns `Deliverable`.

## Validation Rule

- Treat an empty `allowed_domains` list as unrestricted.
- Normalize the extracted domain before comparing it with the configured list.
- Use `EmailValidation` after Laravel's syntax-oriented email rule; this rule owns domain policy and deliverability, not RFC syntax validation.
- Resolve drivers through the manager and return localized messages from the `laravel-email-validation` translation namespace.
- Keep `en`, `de`, and `fa` translation keys synchronized whenever validation outcomes change.

## Testing And Verification

- Cover non-string input, restricted and unrestricted domains, the null driver, custom driver registration, and every status that affects validation.
- Keep provider-specific response mapping out of this package's tests.
- Keep the Pest architecture presets and assert the core stays provider-neutral.
- Run `php artisan test --compact `.
- Run targeted PHPStan analysis for `the package root/src`.
- If PHP files changed, run `vendor/bin/pint --dirty --format agent`.
