# Laravel Email Validation

A standalone, reusable email validation rule for Laravel applications.

## Features

- A `ValidationRule` that enforces an optional domain allow-list plus pluggable deliverability verification
- Driver-based deliverability via a Laravel `Manager`
- Configurable allowed domains (empty by default — no restriction)
- Localized failure messages (en, de, fa)
- Explicit deliverable, risky, undeliverable, and unverifiable outcomes

The core package ships only the `null` driver (no external check). Concrete
providers live in their own packages that register a driver via the manager's
`extend`, e.g. `misaf/laravel-email-validation-emailable`.

The package depends only on framework packages, so it can be reused by any Laravel application without pulling in a wider ecosystem.

## Requirements

- PHP 8.3+
- Laravel 13

## Installation

```bash
composer require misaf/laravel-email-validation
```

The service provider is auto-registered.

Publish the config to customise the allowed domains and deliverability wiring:

```bash
php artisan vendor:publish --tag=laravel-email-validation-config
```

## Configuration

`config/laravel-email-validation.php`:

- `allowed_domains` — array of accepted domains. Leave empty to allow any domain. Defaults to the comma-separated `EMAIL_ALLOWED_DOMAINS` env value.
- `default` — the deliverability driver name (`EMAIL_VERIFIER_DRIVER`). The core package provides only `null`; installing a driver package makes its driver name available here.

## Usage

```php
use Misaf\LaravelEmailValidation\Rules\EmailValidation;

TextInput::make('email')
    ->email()
    ->rules([
        'bail',
        'email:rfc,strict,spoof,filter,filter_unicode',
        new EmailValidation(),
    ]);
```

`new EmailValidation()` uses the configured default driver. Pass a driver name to override per use: `new EmailValidation('my-driver')`.

## Verification Outcomes

| Status | Meaning | Validation result |
| --- | --- | --- |
| `Deliverable` | The provider positively classified the address as deliverable | Pass |
| `Risky` | The address may accept mail but has deliverability or quality concerns | Fail |
| `Undeliverable` | The provider positively classified the address as invalid | Fail |
| `Unverifiable` | Verification failed or produced no reliable result | Fail |

The `null` driver performs no external verification and always returns
`Deliverable`. Unknown provider states and provider failures must never be
reported as deliverable by concrete drivers.

### Verifying an address directly

```php
use Misaf\LaravelEmailValidation\Facades\EmailVerifier;
use Misaf\LaravelEmailValidation\Enums\EmailVerificationStatus;

$status = EmailVerifier::verify('user@example.com');            // default driver
$status = EmailVerifier::driver('my-driver')->verify($email);  // specific driver

if ($status === EmailVerificationStatus::Deliverable) {
    // ...
}
```

### Registering a custom driver

```php
use Misaf\LaravelEmailValidation\Contracts\EmailVerifier as EmailVerifierContract;
use Misaf\LaravelEmailValidation\EmailVerifierManager;

app(EmailVerifierManager::class)->extend('my-provider', fn (): EmailVerifierContract => new MyProviderVerifier());
```

## Testing

```bash
composer test
composer analyse
```

## License

MIT. See [LICENSE](LICENSE).
