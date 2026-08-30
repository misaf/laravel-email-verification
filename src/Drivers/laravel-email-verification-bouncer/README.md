# Laravel Email Verification — Bouncer Driver

A [Bouncer](https://usebouncer.com) deliverability driver for
[`misaf/laravel-email-verification`](https://github.com/misaf/laravel-email-verification).

## Features

- Registers the `bouncer` driver with the core manager
- Uses Bouncer's real-time verification API (`GET /v1.1/email/verify`)
- `x-api-key` header authentication — the key is never sent as a query parameter
- Server-side timeout below the HTTP client timeout, so slow verifications come back as a clean result instead of a client abort
- Configurable timeouts and a retry budget owned by this package — only connection failures and 5xx responses are retried, so a 4xx never burns paid quota on an answer that cannot change
- Explicit mapping for every Bouncer verification status
- Safe unverifiable results for provider failures, exhausted credits, rate limits, and unexpected responses

## Requirements

- PHP 8.4+
- Laravel 13
- `misaf/laravel-email-verification`

## Installation

```bash
composer require misaf/laravel-email-verification-bouncer
```

The service provider auto-registers and adds a `bouncer` driver to the
email verification manager. Point the core package at it:

```env
EMAIL_VERIFICATION_DRIVER=bouncer
BOUNCER_HOST=https://api.usebouncer.com/v1.1/email/verify
BOUNCER_API_KEY=your-key
```

Publish the config to override credentials:

```bash
php artisan vendor:publish --tag=email-verification-bouncer-config
```

An install command is also available, which publishes the config and walks you
through setup:

```bash
php artisan email-verification-bouncer:install
```

## Configuration

`config/email-verification-bouncer.php`:

- `host` — the Bouncer real-time verify endpoint, normally `https://api.usebouncer.com/v1.1/email/verify`
- `api_key` — the private Bouncer API key (generated in the Bouncer dashboard under *API*)
- `timeout.server` — the verification budget asked of Bouncer (`BOUNCER_SERVER_TIMEOUT`, default `5`)
- `timeout.client` — how long this application waits for the response (`BOUNCER_CLIENT_TIMEOUT`, default `6`). Keep it above `timeout.server`.
- `retry.times` — total attempts per verification (`BOUNCER_RETRY_TIMES`, default `2`)
- `retry.sleep_milliseconds` — pause between attempts (`BOUNCER_RETRY_SLEEP`, default `100`)

Only transient faults are retried: a connection failure, or a server-side 5xx.
A 4xx is never retried — a bad key or a rate limit cannot resolve itself, and
retrying it would only burn paid API quota.

```env
BOUNCER_HOST=https://api.usebouncer.com/v1.1/email/verify
BOUNCER_API_KEY=your-key

BOUNCER_CLIENT_TIMEOUT=6
BOUNCER_SERVER_TIMEOUT=5
BOUNCER_RETRY_TIMES=2
BOUNCER_RETRY_SLEEP=100
```

Timeouts and retry behavior are configured here, not in the core package: the
core knows nothing about how a provider communicates.

## Verification Outcomes

| Bouncer status | Core status | Validation result |
| --- | --- | --- |
| `deliverable` | `Deliverable` | Pass |
| `risky` | `Risky` | Fail |
| `undeliverable` | `Undeliverable` | Fail |
| `unknown` or unsupported | `Unverifiable` | Fail |

Malformed payloads, unsuccessful HTTP responses (including 402 no-credits and
429 rate-limit responses), timeouts, and exceptions also produce
`Unverifiable`. They are never treated as deliverable.

## Usage

Once `EMAIL_VERIFICATION_DRIVER` points at `bouncer`, the core `EmailValidation` rule
uses this driver with no further changes. To use it for a single rule
regardless of the configured default:

```php
use Misaf\LaravelEmailVerification\Rules\EmailValidation;

$request->validate([
    'email' => ['bail', 'email:rfc,strict', new EmailValidation('bouncer')],
]);
```

### Verifying an address directly

```php
use Misaf\LaravelEmailVerification\Enums\EmailVerificationStatus;
use Misaf\LaravelEmailVerification\Facades\EmailVerification;

$status = EmailVerification::driver('bouncer')->verify('user@example.com');

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
`src/Drivers/laravel-email-verification-bouncer` and its tests run alongside the
core package.

## License

MIT. See [LICENSE](LICENSE).
