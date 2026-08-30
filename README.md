# Laravel Email Verification

Provider-neutral email domain and deliverability validation for Laravel.

- A validation rule with an optional domain allow-list plus pluggable deliverability verification
- Driver-based deliverability via a Laravel `Manager`
- Localized failure messages (en, de, fa)

It does **not** validate syntax — pair it with Laravel's built-in `email` rule.

## Requirements

PHP 8.4+, Laravel 13.

## Installation

```bash
composer require misaf/laravel-email-verification
```

This gives you the domain allow-list and the `null` driver, which treats every
address as deliverable — fine for local and testing, but it performs no real
verification. For that, install a driver package (one, both, or none):

| Package | Driver | Provider |
| --- | --- | --- |
| *(core)* | `null` | none — always `Deliverable` |
| `misaf/laravel-email-verification-emailable` | `emailable` | [Emailable](https://emailable.com) |
| `misaf/laravel-email-verification-bouncer` | `bouncer` | [Bouncer](https://usebouncer.com) |

```bash
composer require misaf/laravel-email-verification-emailable
composer require misaf/laravel-email-verification-bouncer
```

All service providers are auto-registered. See each driver's README for its
credentials and options.

Publish the config:

```bash
php artisan vendor:publish --tag=email-verification-config
# or
php artisan email-verification:install
```

## Usage

```php
use Misaf\LaravelEmailVerification\Rules\EmailValidation;

$request->validate([
    'email' => [
        'bail',
        'email:rfc,strict,spoof,filter,filter_unicode',
        new EmailValidation(),
    ],
]);
```

Laravel's `email` rule runs first and `bail` stops there, so `EmailValidation`
only ever sees — and only ever spends quota on — a well-formed address.

It is a plain `ValidationRule`, so it works anywhere Laravel accepts one — form
requests, `Validator::make()`, and manual validator instances.

`new EmailValidation()` uses the configured default driver; pass a name to
override per use: `new EmailValidation('bouncer')`.

### Verifying an address directly

```php
use Misaf\LaravelEmailVerification\Facades\EmailVerification;
use Misaf\LaravelEmailVerification\Enums\EmailVerificationStatus;

$status = EmailVerification::verify('user@example.com');         // default driver
$status = EmailVerification::driver('bouncer')->verify($email);  // specific driver

if ($status === EmailVerificationStatus::Deliverable) {
    // ...
}
```

The facade is the driver boundary: deliverability verification only. It does
**not** apply `allowed_domains` — that belongs to the rule, which runs the
allow-list first and short-circuits before spending quota. Use the facade for a
driver's verdict on any address (a queued re-check, say) and the rule for user
input.

## Configuration

`config/email-verification.php`:

- `default` — the driver name (`EMAIL_VERIFICATION_DRIVER`).
- `allowed_domains` — domains the rule accepts, compared case-insensitively.
  Leave empty to allow any domain. Invalid entries throw an
  `InvalidArgumentException`.

```php
'allowed_domains' => [
    'example.com',
    'example.org',
],
```

That is the whole core configuration. Endpoints, credentials, timeouts, and
retries belong to the driver package.

Both drivers can coexist; `default` picks the one used when none is named:

```env
EMAIL_VERIFICATION_DRIVER=emailable
```

## Verification Outcomes

| Status | Meaning | Validation result |
| --- | --- | --- |
| `Deliverable` | Positively classified as deliverable | Pass |
| `Risky` | May accept mail, but has quality concerns | Fail |
| `Undeliverable` | Positively classified as invalid | Fail |
| `Unverifiable` | Verification failed or gave no reliable result | Fail |

Provider failures and unknown states are never reported as deliverable.

## Registering a custom driver

```php
use Misaf\LaravelEmailVerification\Contracts\EmailVerification as EmailVerificationContract;
use Misaf\LaravelEmailVerification\EmailVerificationManager;

app(EmailVerificationManager::class)->extend('my-provider', fn (): EmailVerificationContract => new MyProviderVerification());
```

From a package service provider, defer the registration so provider discovery
order cannot matter:

```php
$this->callAfterResolving(
    EmailVerificationManager::class,
    fn (EmailVerificationManager $manager) => $manager->extend(
        'my-provider',
        fn (): EmailVerificationContract => new MyProviderVerification(),
    ),
);
```

## Localization

Messages come from the `email-verification` translation namespace, e.g.
`email-verification::validation.email.risky`. To override them:

```bash
php artisan vendor:publish --tag=email-verification-translations
```

## Testing

```bash
composer test       # Pest
composer analyse    # PHPStan / Larastan
composer format     # Pint
```

## License

MIT. See [LICENSE](LICENSE).
