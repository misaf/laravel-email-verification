## Laravel Email Verification

The `misaf/laravel-email-verification` package provides provider-neutral email-domain and deliverability validation for Laravel applications.

### Standards

- Keep core code inside `the package root` using the `Misaf\LaravelEmailVerification` namespace.
- This package owns `EmailVerification`, `EmailVerificationManager`, `EmailVerificationStatus`, the `EmailVerification` facade, `EmailValidation`, and `NullEmailVerification`.
- Keep the core package standalone. Never import a concrete verification provider, another domain module, .
- Provider packages depend on this package and register drivers through `EmailVerificationManager::extend()`; the dependency must never point from core to provider.
- Preserve status semantics: `Deliverable` is positively deliverable, `Undeliverable` is positively invalid, `Risky` is uncertain or unsafe to accept, and `Unverifiable` means no reliable result. Never treat unknown states, malformed responses, timeouts, or provider errors as deliverable.
- Keep the `null` driver as the provider-neutral default. It makes no external request and reports addresses as deliverable.
- An empty `email-verification.allowed_domains` list imposes no domain restriction. Use Laravel's `email` rule before `EmailValidation`; this package never validates email syntax itself.
- Keep the `en`, `de`, and `fa` validation translation keys synchronized.
- Keep focused Pest coverage for domain restrictions, mixed input, driver registration, and every validation status.
- Keep the architecture presets plus `arch()->expect('Misaf\LaravelEmailVerification\\')->not->toUse([...])`. The trailing separator keeps the sibling provider namespaces out of the expectation.
- Keep HTTP concerns out of the core. Endpoints, credentials, timeouts, retries, and response mapping belong to the provider packages; the core must not use `Illuminate\Http\Client`.
