---
name: laravel-email-validation-emailable-development
description: "Create, modify, review, or test the optional Emailable driver in the package root. Trigger for EmailableEmailVerifier, EmailableServiceProvider, Emailable API configuration, email-verification response mapping, HTTP retries or timeouts, and Emailable driver tests."
---

# Laravel Email Validation Emailable

## Workflow

Use this skill together with `laravel-email-validation-development`, `laravel-best-practices`, and `pest-testing` whenever tests change. Before code changes, use Laravel Boost `application-info` and `search-docs`; consult current official Emailable API documentation before changing response semantics.

## Module Boundary

Treat `the package root` as an optional concrete provider.

- Use namespace `Misaf\LaravelEmailValidationEmailable`.
- Own only `EmailableEmailVerifier`, its config, tests, and driver registration in `EmailableServiceProvider`.
- Depend on `misaf/laravel-email-validation` and implement its `EmailVerifier` contract.
- Never move Emailable HTTP logic, credentials, or dependencies into the core package.
- Do not depend on other packages you do not need.

## Driver Semantics

- Register the driver as `emailable` through `EmailVerifierManager::extend()`.
- Read `host` and `api_key` from `laravel-email-validation-emailable` using typed configuration access.
- Map `deliverable` to `Deliverable`, `undeliverable` to `Undeliverable`, and `risky` to `Risky`.
- Map `unknown`, unrecognized states, malformed payloads, unsuccessful responses, timeouts, and exceptions to `Unverifiable`.
- Never report an ambiguous or failed verification as `Deliverable`.
- Preserve bounded request timeouts and retries. Never log the API key or include it in exception context.

## Testing And Verification

- Use `Http::fake()`; tests must never call the live Emailable API.
- Cover driver registration, every recognized response state, unknown states, unsuccessful responses, and malformed payloads.
- Keep the Pest architecture presets and assert the driver depends only on the core contract.
- Run `php artisan test --compact tests/ (Emailable driver)`.
- Run targeted PHPStan analysis for `the package root/src`.
- If PHP files changed, run `vendor/bin/pint --dirty --format agent`.
