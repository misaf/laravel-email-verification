---
name: laravel-email-verification-development
description: "Create, modify, review, or test the provider-neutral Laravel Email Verification core package in the package root. Trigger for EmailVerification, EmailVerificationManager, EmailVerificationStatus, EmailValidation, NullEmailVerification, custom email-verification drivers, allowed email domains, deliverability validation, or the email-verification configuration and translations."
---

# Laravel Email Verification

## Workflow

Use `laravel-best-practices` for Laravel PHP and `pest-testing` whenever tests change. Before code changes, use Laravel Boost `application-info` and `search-docs`. Use `laravel-email-verification-emailable-development` as well when changing the Emailable provider.

## Module Boundary

Treat `the package root` as the standalone, provider-neutral core.

- Use namespace `Misaf\LaravelEmailVerification`.
- Own `Contracts\EmailVerification`, `EmailVerificationManager`, `EmailVerificationStatus`, the `EmailVerification` facade, `EmailValidation`, and `NullEmailVerification`.
- Do not import or require a concrete provider, another domain package.
- Keep provider dependencies one-way: a provider package requires this core package and registers a driver through `EmailVerificationManager::extend()`.
- Keep public contracts, enum semantics, manager APIs, config keys, and translation keys stable.

## Verification Semantics

- `Deliverable` means the provider positively classified the address as deliverable.
- `Undeliverable` means the provider positively classified the address as invalid or non-deliverable.
- `Risky` means the address may accept mail but is not safe enough to pass the validation rule.
- `Unverifiable` means verification failed or the provider could not determine a reliable result. Never silently convert an unknown provider state, malformed payload, timeout, or provider error to `Deliverable`.
- Keep the `null` driver as the default provider-neutral fallback. It performs no external request and returns `Deliverable`.

## Validation Rule

- Treat an empty `email-verification.allowed_domains` list as unrestricted.
- Normalize the extracted domain before comparing it with the configured list.
- Use `EmailValidation` after Laravel's `email` rule; this rule owns domain policy and deliverability, never email syntax.
- Resolve drivers through the manager and return localized messages from the `email-verification` translation namespace.
- Keep `en`, `de`, and `fa` translation keys synchronized whenever validation outcomes change.

## Testing And Verification

- Cover non-string input, restricted and unrestricted domains, the null driver, custom driver registration, and every status that affects validation.
- Prove a blocked domain never reaches the driver; provider verification costs paid API quota.
- Keep provider-specific response mapping, HTTP behavior, and retry policy out of this package and its tests.
- Keep the Pest architecture presets and assert the core stays provider-neutral.
- Run `php artisan test --compact `.
- Run targeted PHPStan analysis for `the package root/src`.
- If PHP files changed, run `vendor/bin/pint --dirty --format agent`.
