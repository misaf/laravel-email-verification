## Laravel Email Validation Bouncer

The `misaf/laravel-email-validation-bouncer` package is the optional Bouncer API driver for the provider-neutral `misaf/laravel-email-validation` core.

### Standards

- Keep driver code inside `the package root` using the `Misaf\LaravelEmailValidationBouncer` namespace.
- This package owns only `BouncerEmailVerifier`, its configuration, tests, and `bouncer` driver registration.
- Depend one-way on `misaf/laravel-email-validation` and implement its `EmailVerifier` contract. Never move Bouncer dependencies or HTTP behavior into the core package.
- Read `host` and `api_key` from `laravel-email-validation-bouncer` with typed configuration access. Never log the API key.
- Call the real-time verify endpoint with `x-api-key` header auth; keep the server-side timeout below the client timeout.
- Map Bouncer statuses explicitly: `deliverable` to `Deliverable`, `undeliverable` to `Undeliverable`, and `risky` to `Risky`.
- Map `unknown`, unsupported states, malformed payloads, failed responses, timeouts, and exceptions to `Unverifiable`. Never report an ambiguous provider result as deliverable.
- Preserve bounded HTTP timeouts and retries.
- Test every response path with `Http::fake()`; tests must never call the live provider.
- Keep the architecture presets plus `arch()->expect('Misaf\LaravelEmailValidationBouncer')->not->toUse([...])`.
