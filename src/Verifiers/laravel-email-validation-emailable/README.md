# Laravel Email Validation — Emailable Driver

An [Emailable](https://emailable.com) deliverability driver for
[`misaf/laravel-email-validation`](https://github.com/misaf/laravel-email-validation).

## Features

- Registers the `emailable` verifier driver with the core manager
- Bounded HTTP timeout and retry behavior
- Explicit mapping for Emailable verification states
- Safe unverifiable results for provider failures and unexpected responses

## Requirements

- PHP 8.3+
- Laravel 13
- `misaf/laravel-email-validation`

## Installation

```bash
composer require misaf/laravel-email-validation-emailable
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
php artisan vendor:publish --tag=laravel-email-validation-emailable-config
```

## Configuration

`config/laravel-email-validation-emailable.php`:

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

## Direct Usage

```php
use Misaf\LaravelEmailValidation\Enums\EmailVerificationStatus;
use Misaf\LaravelEmailValidation\Facades\EmailVerifier;

$status = EmailVerifier::driver('emailable')->verify('user@example.com');

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
