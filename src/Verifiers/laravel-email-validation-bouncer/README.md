# Laravel Email Validation — Bouncer Driver

A [Bouncer](https://usebouncer.com) deliverability driver for
[`misaf/laravel-email-validation`](https://github.com/misaf/laravel-email-validation).

## Features

- Registers the `bouncer` verifier driver with the core manager
- Uses Bouncer's real-time verification API (`GET /v1.1/email/verify`)
- `x-api-key` header authentication — the key is never sent as a query parameter
- Server-side timeout below the HTTP client timeout, so slow verifications come back as a clean result instead of a client abort
- Explicit mapping for every Bouncer verification status
- Safe unverifiable results for provider failures, exhausted credits, rate limits, and unexpected responses

## Requirements

- PHP 8.3+
- Laravel 13
- `misaf/laravel-email-validation`

## Installation

```bash
composer require misaf/laravel-email-validation-bouncer
```

The service provider auto-registers and adds a `bouncer` driver to the
email verifier manager. Point the core package at it:

```env
EMAIL_VERIFIER_DRIVER=bouncer
BOUNCER_HOST=https://api.usebouncer.com/v1.1/email/verify
BOUNCER_API_KEY=your-key
```

Publish the config to override credentials:

```bash
php artisan vendor:publish --tag=laravel-email-validation-bouncer-config
```

## Configuration

`config/laravel-email-validation-bouncer.php`:

- `host` — the Bouncer real-time verify endpoint, normally `https://api.usebouncer.com/v1.1/email/verify`
- `api_key` — the private Bouncer API key (generated in the Bouncer dashboard under *API*)

The credentials remain separate from the provider-neutral core configuration.

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

## Direct Usage

```php
use Misaf\LaravelEmailValidation\Enums\EmailVerificationStatus;
use Misaf\LaravelEmailValidation\Facades\EmailVerifier;

$status = EmailVerifier::driver('bouncer')->verify('user@example.com');

if ($status === EmailVerificationStatus::Deliverable) {
    // The provider positively classified the address as deliverable.
}
```

## Testing

```bash
composer test
composer analyse
```

## License

MIT. See [LICENSE](LICENSE).
