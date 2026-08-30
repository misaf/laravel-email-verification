# Laravel Email Verification — Emailable Driver

An [Emailable](https://emailable.com) deliverability driver for
[`misaf/laravel-email-verification`](https://github.com/misaf/laravel-email-verification).

## Requirements

PHP 8.4+, Laravel 13, `misaf/laravel-email-verification`.

## Installation

```bash
composer require misaf/laravel-email-verification-emailable
```

The service provider auto-registers an `emailable` driver on the core manager.
Point the core package at it:

```env
EMAIL_VERIFICATION_DRIVER=emailable
EMAILABLE_HOST=https://api.emailable.com/v1/verify
EMAILABLE_API_KEY=your-key
```

Publish the config:

```bash
php artisan vendor:publish --tag=email-verification-emailable-config
# or
php artisan email-verification-emailable:install
```

## Usage

With `EMAIL_VERIFICATION_DRIVER=emailable`, the core `EmailValidation` rule uses
this driver with no further changes. To use it for a single rule regardless of
the default:

```php
use Misaf\LaravelEmailVerification\Rules\EmailValidation;

$request->validate([
    'email' => ['bail', 'email:rfc,strict', new EmailValidation('emailable')],
]);
```

Or verify an address directly:

```php
use Misaf\LaravelEmailVerification\Enums\EmailVerificationStatus;
use Misaf\LaravelEmailVerification\Facades\EmailVerification;

$status = EmailVerification::driver('emailable')->verify('user@example.com');

if ($status === EmailVerificationStatus::Deliverable) {
    // ...
}
```

## Configuration

`config/email-verification-emailable.php`:

- `host` — the verification endpoint, normally `https://api.emailable.com/v1/verify`
- `api_key` — your private Emailable API key
- `timeout.server` — the budget asked of Emailable (`EMAILABLE_SERVER_TIMEOUT`, default `5`)
- `timeout.client` — how long this app waits (`EMAILABLE_CLIENT_TIMEOUT`, default `6`); keep it above `timeout.server`
- `retry.times` — attempts per verification (`EMAILABLE_RETRY_TIMES`, default `2`)
- `retry.sleep_milliseconds` — pause between attempts (`EMAILABLE_RETRY_SLEEP`, default `100`)

```env
EMAILABLE_CLIENT_TIMEOUT=6
EMAILABLE_SERVER_TIMEOUT=5
EMAILABLE_RETRY_TIMES=2
EMAILABLE_RETRY_SLEEP=100
```

Only transient faults are retried — connection failures and 5xx. A 4xx is never
retried, so a bad key or rate limit cannot burn paid quota.

## Verification Outcomes

| Emailable state | Core status | Validation result |
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
`Drivers/laravel-email-verification-emailable`.

## License

MIT. See [LICENSE](LICENSE).
