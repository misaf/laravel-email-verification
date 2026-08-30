# Laravel Email Verification — Emailable Driver

An [Emailable](https://emailable.com) deliverability driver for
[`misaf/laravel-email-verification`](https://github.com/misaf/laravel-email-verification).

## Features

- Registers the `emailable` driver with the core manager
- Uses Emailable's verification API (`GET /v1/verify`)
- A server-side timeout below the HTTP client timeout, so a slow verification comes back as a clean result instead of a client abort
- Configurable timeouts and a retry budget owned by this package — only connection failures and 5xx responses are retried, so a 4xx never burns paid quota on an answer that cannot change
- Explicit mapping for every Emailable verification state
- Safe unverifiable results for provider failures, malformed payloads, timeouts, and unexpected responses

## Requirements

- PHP 8.4+
- Laravel 13
- `misaf/laravel-email-verification`

## Installation

```bash
composer require misaf/laravel-email-verification-emailable
```

The service provider auto-registers and adds an `emailable` driver to the
email verification manager. Point the core package at it:

```env
EMAIL_VERIFICATION_DRIVER=emailable
EMAILABLE_HOST=https://api.emailable.com/v1/verify
EMAILABLE_API_KEY=your-key
```

Publish the config to override credentials:

```bash
php artisan vendor:publish --tag=email-verification-emailable-config
```

An install command is also available. It publishes the same config file and
then offers to open the repository on GitHub:

```bash
php artisan email-verification-emailable:install
```

## Configuration

`config/email-verification-emailable.php`:

- `host` — the Emailable verification endpoint, normally `https://api.emailable.com/v1/verify`
- `api_key` — the private Emailable API key
- `timeout.server` — the verification budget asked of Emailable (`EMAILABLE_SERVER_TIMEOUT`, default `5`)
- `timeout.client` — how long this application waits for the response (`EMAILABLE_CLIENT_TIMEOUT`, default `6`). Keep it above `timeout.server`.
- `retry.times` — total attempts per verification (`EMAILABLE_RETRY_TIMES`, default `2`)
- `retry.sleep_milliseconds` — pause between attempts (`EMAILABLE_RETRY_SLEEP`, default `100`)

Only transient faults are retried: a connection failure, or a server-side 5xx.
A 4xx is never retried — a bad key or a rate limit cannot resolve itself, and
retrying it would only burn paid API quota.

```env
EMAILABLE_HOST=https://api.emailable.com/v1/verify
EMAILABLE_API_KEY=your-key

EMAILABLE_CLIENT_TIMEOUT=6
EMAILABLE_SERVER_TIMEOUT=5
EMAILABLE_RETRY_TIMES=2
EMAILABLE_RETRY_SLEEP=100
```

Timeouts and retry behavior are configured here, not in the core package: the
core knows nothing about how a provider communicates.

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

Once `EMAIL_VERIFICATION_DRIVER` points at `emailable`, the core `EmailValidation` rule
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
use Misaf\LaravelEmailVerification\Facades\EmailVerification;

$status = EmailVerification::driver('emailable')->verify('user@example.com');

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
`Drivers/laravel-email-verification-emailable` and its tests run alongside the
core package.

## License

MIT. See [LICENSE](LICENSE).
