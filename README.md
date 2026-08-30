# Laravel Email Verification

Provider-neutral email domain and deliverability validation for Laravel applications.

## Responsibilities

This package deliberately does **not** validate email syntax — Laravel already
does that well. Pair the two rules:

| Concern | Owner |
| --- | --- |
| Email syntax (RFC, spoof, DNS-safe filtering) | Laravel's built-in `email` rule |
| Domain allow-list | `Misaf\LaravelEmailVerification\Rules\EmailValidation` |
| Deliverability verification | `EmailValidation`, through the configured driver |

```text
Laravel email validation rule
        ↓
syntax validation

Laravel Email Verification
        ↓
domain allow-list
        ↓
deliverability driver
```

The allow-list runs first and short-circuits, so a blocked domain never reaches
the provider and never spends paid API quota.

## Features

- A `ValidationRule` that enforces an optional domain allow-list plus pluggable deliverability verification
- Driver-based deliverability via a Laravel `Manager`
- Configurable allowed domains (empty by default — no restriction)
- Localized failure messages (en, de, fa)
- Explicit deliverable, risky, undeliverable, and unverifiable outcomes

The core package is provider-neutral: it ships only the `null` driver, which
performs no external check. Real deliverability verification comes from driver
packages that register themselves via the manager's `extend`. Install the
driver(s) you want — one, both, or none.

The core has no email-provider SDK dependencies. It uses Laravel components and
Spatie Laravel Package Tools for package registration, so it can be reused by
any Laravel application without pulling in a provider-specific ecosystem.

## Requirements

- PHP 8.4+
- Laravel 13

## Installation

The core package is required in every case:

```bash
composer require misaf/laravel-email-verification
```

On its own this gives you the domain allow-list plus the `null` driver, which
treats every address as deliverable — useful for local and testing
environments, but it performs no real verification.

### First-party drivers

To actually verify deliverability, add one or both driver packages. They are
independent of each other and can be installed together:

```bash
composer require misaf/laravel-email-verification-emailable
composer require misaf/laravel-email-verification-bouncer
```

| Package | Driver name | Provider | Config file |
| --- | --- | --- | --- |
| *(core)* | `null` | none — always `Deliverable` | `email-verification.php` |
| `misaf/laravel-email-verification-emailable` | `emailable` | [Emailable](https://emailable.com) | `email-verification-emailable.php` |
| `misaf/laravel-email-verification-bouncer` | `bouncer` | [Bouncer](https://usebouncer.com) | `email-verification-bouncer.php` |

Each driver package requires the core and is listed under the core's composer
`suggest`, so `composer require misaf/laravel-email-verification` will prompt you
with what is available.

All service providers are auto-registered.

Publish the config to customise the allowed domains and deliverability wiring:

```bash
php artisan vendor:publish --tag=email-verification-config
```

The translations can be published as well, if you want to override the failure
messages:

```bash
php artisan vendor:publish --tag=email-verification-translations
```

An install command is also available, which publishes the config and walks you
through setup:

```bash
php artisan email-verification:install
```

Each driver package publishes its own config under a matching tag, e.g.:

```bash
php artisan vendor:publish --tag=email-verification-emailable-config
php artisan vendor:publish --tag=email-verification-bouncer-config
```

### Using both drivers together

Both drivers register under distinct names on the same manager, so they
coexist. `default` picks the one used when no driver is named, and any rule or
facade call can override it per use:

```env
EMAIL_VERIFICATION_DRIVER=emailable

EMAILABLE_HOST=https://api.emailable.com/v1/verify
EMAILABLE_API_KEY=...

BOUNCER_HOST=https://api.usebouncer.com/v1.1/email/verify
BOUNCER_API_KEY=...
```

```php
new EmailValidation();            // the configured default — "emailable" above
new EmailValidation('bouncer');   // this rule only, regardless of the default
```

## Configuration

`config/email-verification.php`:

- `default` — the deliverability driver name (`EMAIL_VERIFICATION_DRIVER`). The core package provides only `null`; installing a driver package makes its driver name (`emailable`, `bouncer`) available here.
- `allowed_domains` — the domains the rule accepts. Comparison is
  case-insensitive. Every entry must be a non-empty string without surrounding
  whitespace; invalid configuration throws an `InvalidArgumentException`.
  Leave the list empty to allow any domain.

```php
'allowed_domains' => [
    'example.com',
    'example.org',
],
```

That is the whole core configuration. Endpoints, credentials, timeouts, and
retry behavior belong to the driver package that performs the request — see the
driver's own README and config file.

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

Laravel's `email` rule runs first and rejects anything that is not a
syntactically valid address; `bail` stops there, so `EmailValidation` only ever
sees a well-formed address and only ever spends quota on one.

The rule is a plain `ValidationRule`, so it works anywhere Laravel accepts one
— form requests, `Validator::make()`, Filament fields, and so on:

```php
TextInput::make('email')
    ->email()
    ->rules(['bail', 'email:rfc,strict', new EmailValidation()]);
```

`new EmailValidation()` uses the configured default driver. Pass a driver name to override per use: `new EmailValidation('bouncer')`.

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
use Misaf\LaravelEmailVerification\Facades\EmailVerification;
use Misaf\LaravelEmailVerification\Enums\EmailVerificationStatus;

$status = EmailVerification::verify('user@example.com');           // default driver
$status = EmailVerification::driver('bouncer')->verify($email);   // specific driver

if ($status === EmailVerificationStatus::Deliverable) {
    // ...
}
```

### Registering a custom driver

```php
use Misaf\LaravelEmailVerification\Contracts\EmailVerification as EmailVerificationContract;
use Misaf\LaravelEmailVerification\EmailVerificationManager;

app(EmailVerificationManager::class)->extend('my-provider', fn (): EmailVerificationContract => new MyProviderVerification());
```

From a package service provider, defer the registration instead of resolving
the manager during `register()`, so your package works regardless of the order
Laravel discovers it in:

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

Failure messages come from the `email-verification` translation namespace, e.g.
`email-verification::validation.email.risky`. Publish the translations to
override them:

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
