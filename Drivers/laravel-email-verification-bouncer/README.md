# Laravel Email Verification — Bouncer Driver

A [Bouncer](https://usebouncer.com) deliverability driver for
[`misaf/laravel-email-verification`](https://github.com/misaf/laravel-email-verification).

## Requirements

PHP 8.4+, Laravel 13, `misaf/laravel-email-verification`.

## Installation

```bash
composer require misaf/laravel-email-verification-bouncer
```

The service provider auto-registers a `bouncer` driver on the core manager.
Point the core package at it:

```env
EMAIL_VERIFICATION_DRIVER=bouncer
BOUNCER_HOST=https://api.usebouncer.com/v1.1/email/verify
BOUNCER_API_KEY=your-key
```

Publish the config:

```bash
php artisan vendor:publish --tag=email-verification-bouncer-config
# or
php artisan email-verification-bouncer:install
```

## Usage

With `EMAIL_VERIFICATION_DRIVER=bouncer`, the core `EmailValidation` rule uses
this driver with no further changes. To use it for a single rule regardless of
the default:

```php
use Misaf\LaravelEmailVerification\Rules\EmailValidation;

$request->validate([
    'email' => ['bail', 'email:rfc,strict', new EmailValidation('bouncer')],
]);
```

Or verify an address directly:

```php
use Misaf\LaravelEmailVerification\Enums\EmailVerificationStatus;
use Misaf\LaravelEmailVerification\Facades\EmailVerification;

$status = EmailVerification::driver('bouncer')->verify('user@example.com');

if ($status === EmailVerificationStatus::Deliverable) {
    // ...
}
```

## Configuration

`config/email-verification-bouncer.php`:

- `host` — the verification endpoint, normally `https://api.usebouncer.com/v1.1/email/verify`
- `api_key` — your private Bouncer API key
- `timeout.server` — the budget asked of Bouncer (`BOUNCER_SERVER_TIMEOUT`, default `5`)
- `timeout.client` — how long this app waits (`BOUNCER_CLIENT_TIMEOUT`, default `6`); keep it above `timeout.server`
- `retry.times` — attempts per verification (`BOUNCER_RETRY_TIMES`, default `2`)
- `retry.sleep_milliseconds` — pause between attempts (`BOUNCER_RETRY_SLEEP`, default `100`)

```env
BOUNCER_CLIENT_TIMEOUT=6
BOUNCER_SERVER_TIMEOUT=5
BOUNCER_RETRY_TIMES=2
BOUNCER_RETRY_SLEEP=100
```

Only transient faults are retried — connection failures and 5xx. A 4xx is never
retried, so a bad key or rate limit cannot burn paid quota.

## Verification Outcomes

| Bouncer status | Core status | Validation result |
| --- | --- | --- |
| `deliverable` | `Deliverable` | Pass |
| `risky` | `Risky` | Fail |
| `undeliverable` | `Undeliverable` | Fail |
| `unknown` or unsupported | `Unverifiable` | Fail |

Malformed payloads, failed HTTP responses, timeouts, and exceptions also produce
`Unverifiable`. They are never treated as deliverable.

## Contributing

This repository is a read-only split of the
[monorepo](https://github.com/misaf/laravel-email-verification); commits made
here are overwritten by the next split. Open issues and pull requests against
the monorepo, where this driver lives at
`Drivers/laravel-email-verification-bouncer`.

## License

MIT. See [LICENSE](LICENSE).
