## Laravel Email Validation Emailable

The `misaf/laravel-email-validation-emailable` package is the optional Emailable API driver for the provider-neutral `misaf/laravel-email-validation` core.

### Standards

- Keep driver code inside `the package root` using the `Misaf\LaravelEmailValidationEmailable` namespace.
- This package owns only `EmailableEmailVerifier`, its configuration, tests, and `emailable` driver registration.
- Depend one-way on `misaf/laravel-email-validation` and implement its `EmailVerifier` contract. Never move Emailable dependencies or HTTP behavior into the core package.
- Read `host` and `api_key` from `laravel-email-validation-emailable` with typed configuration access. Never log the API key.
- Map Emailable states explicitly: `deliverable` to `Deliverable`, `undeliverable` to `Undeliverable`, and `risky` to `Risky`.
- Map `unknown`, unsupported states, malformed payloads, failed responses, timeouts, and exceptions to `Unverifiable`. Never report an ambiguous provider result as deliverable.
- Preserve bounded HTTP timeouts and retries.
- Test every response path with `Http::fake()`; tests must never call the live provider.
- Keep the architecture presets plus `arch()->expect('Misaf\LaravelEmailValidationEmailable')->not->toUse([...])`.
