# Laravel Email Verification — Emailable Driver

An [Emailable](https://emailable.com) deliverability driver for
[`misaf/laravel-email-verification`](https://github.com/misaf/laravel-email-verification).

## Features

- Registers the `emailable` verifier driver with the core manager
- Bounded HTTP timeout and retry behavior
- Explicit mapping for Emailable verification states
- Safe unverifiable results for provider failures and unexpected responses

## Requirements

- PHP 8.4+
- Laravel 13
- `misaf/laravel-email-verification`

## Installation

```bash
composer require misaf/laravel-email-verification-emailable
```

The service provider auto-registers and adds an `emailable` driver to the
email verifier manager. Point the core package at it:

```env
EMAIL_VERIFIER_DRIVER=emailable
EMAILABLE_HOST=https://api.emailable.com/v1/verify
EMAILABLE_API_KEY=your-key
```

Publish the config to override credentials:

```bash
php artisan vendor:publish --tag=laravel-email-verification-emailable-config
```

An install command is also available, which publishes the config and walks you
through setup:

```bash
php artisan laravel-email-verification-emailable:install
```

## Configuration

`config/laravel-email-verification-emailable.php`:

- `host` — the Emailable verification endpoint, normally `https://api.emailable.com/v1/verify`
- `api_key` — the private Emailable API key

The credentials remain separate from the provider-neutral core configuration.

## Verification Outcomes

| Emailable state | Core status | Validation result |
| --- | --- | --- |
| `deliverable` | `Deliverable` | Pass |
| `risky` | `Risky` | Fail |
| `undeliverable` | `Undeliverable` | Fail |
| `unknown` or unsupported | `Unverifiable` | Fail |

Malformed payloads, unsuccessful HTTP responses, timeouts, and exceptions also
produce `Unverifiable`. They are never treated as deliverable.

## Usage

Once `EMAIL_VERIFIER_DRIVER` points at `emailable`, the core `EmailValidation` rule
uses this driver with no further changes. To use it for a single rule
regardless of the configured default:

```php
use Misaf\LaravelEmailVerification\Rules\EmailValidation;

$request->validate([
    'email' => ['bail', 'email:rfc,strict', new EmailValidation('emailable')],
]);
```

### Verifying an address directly

```php
use Misaf\LaravelEmailVerification\Enums\EmailVerificationStatus;
use Misaf\LaravelEmailVerification\Facades\EmailVerifier;

$status = EmailVerifier::driver('emailable')->verify('user@example.com');

if ($status === EmailVerificationStatus::Deliverable) {
    // The provider positively classified the address as deliverable.
}
```

## Contributing

This repository is a read-only split of the
[`misaf/laravel-email-verification`](https://github.com/misaf/laravel-email-verification)
monorepo, published for installation via Composer. Its contents are generated,
so commits made here are overwritten by the next split.

Open issues and pull requests against the monorepo, where this driver lives at
`src/Drivers/laravel-email-verification-emailable` and its tests run alongside the
core package.

## License

MIT. See [LICENSE](LICENSE).
