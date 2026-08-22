---
name: laravel-email-validation-bouncer-development
description: "Create, modify, review, or test the optional Bouncer driver in the package root. Trigger for BouncerEmailVerifier, BouncerServiceProvider, Bouncer API configuration, email-verification response mapping, HTTP retries or timeouts, and Bouncer driver tests."
---

# Laravel Email Validation Bouncer

## Workflow

Use this skill together with `laravel-email-validation-development`, `laravel-best-practices`, and `pest-testing` whenever tests change. Before code changes, use Laravel Boost `application-info` and `search-docs`; consult current official Bouncer API documentation (https://docs.usebouncer.com) before changing response semantics.

## Module Boundary

Treat `the package root` as an optional concrete provider.

- Use namespace `Misaf\LaravelEmailValidationBouncer`.
- Own only `BouncerEmailVerifier`, its config, tests, and driver registration in `BouncerServiceProvider`.
- Depend on `misaf/laravel-email-validation` and implement its `EmailVerifier` contract.
- Never move Bouncer HTTP logic, credentials, or dependencies into the core package.
- Do not depend on other packages you do not need.

## Driver Semantics

- Register the driver as `bouncer` through `EmailVerifierManager::extend()`.
- Read `host` and `api_key` from `laravel-email-validation-bouncer` using typed configuration access.
- Call Bouncer's real-time endpoint (`GET /v1.1/email/verify`) with the email as a query parameter and authenticate via the `x-api-key` header.
- Keep the server-side verification `timeout` query parameter below the HTTP client timeout so slow verifications return a clean result instead of aborting the client request.
- Map `deliverable` to `Deliverable`, `undeliverable` to `Undeliverable`, and `risky` to `Risky`.
- Map `unknown`, unrecognized states, malformed payloads, unsuccessful responses (including 402 no credits and 429 rate limits), timeouts, and exceptions to `Unverifiable`.
- Never report an ambiguous or failed verification as `Deliverable`. Never log the API key or include it in exception context.

## Testing And Verification

- Use `Http::fake()`; tests must never call the live Bouncer API.
- Cover driver registration, header/query request shape, every recognized response status, unknown statuses, unsuccessful responses, and malformed payloads.
- Keep the Pest architecture presets and assert the driver depends only on the core contract.
- Run `php artisan test --compact tests/` (Bouncer driver).
- Run targeted PHPStan analysis for `the package root/src`.
- If PHP files changed, run `vendor/bin/pint --dirty --format agent`.
