## Laravel Email Verification Bouncer

The `misaf/laravel-email-verification-bouncer` package is the optional Bouncer API driver for the provider-neutral `misaf/laravel-email-verification` core.

### Standards

- Keep driver code inside `the package root` using the `Misaf\LaravelEmailVerificationBouncer` namespace.
- This package owns only `BouncerEmailVerification`, its configuration, tests, and `bouncer` driver registration.
- Depend one-way on `misaf/laravel-email-verification` and implement its `EmailVerification` contract. Never move Bouncer dependencies or HTTP behavior into the core package.
- Read `host`, `api_key`, `timeout.*`, and `retry.*` from `email-verification-bouncer` with typed configuration access. Never log the API key.
- Call the real-time verify endpoint with `x-api-key` header auth; keep the server-side timeout below the client timeout.
- Map Bouncer statuses explicitly: `deliverable` to `Deliverable`, `undeliverable` to `Undeliverable`, and `risky` to `Risky`.
- Map `unknown`, unsupported states, malformed payloads, failed responses, timeouts, and exceptions to `Unverifiable`. Never report an ambiguous provider result as deliverable.
- Own the retry predicate and the timeout and retry configuration here. Retry only a connection failure or a server-side 5xx; never retry a 4xx, which burns paid quota without changing the answer.
- Register the driver from a deferred `callAfterResolving()` callback so this package never depends on being registered after the core one.
- Test every response path with `Http::fake()`; tests must never call the live provider.
- Keep the architecture presets plus `arch()->expect('Misaf\LaravelEmailVerificationBouncer')->not->toUse([...])`.
