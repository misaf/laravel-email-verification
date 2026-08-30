# Changelog

All notable changes to `misaf/laravel-email-verification` and its first-party
driver packages are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## 2.0.0 - 2026-08-30

An architecture cleanup that moves every HTTP concern out of the core package
and normalizes the Laravel-facing names. It contains breaking changes and no
compatibility shims.

### Fixed

- The package configuration file is now actually merged and publishable. It
  previously resolved to a file name that does not exist, so `config()` returned
  `null` for every key and `vendor:publish` had nothing to publish. The same
  applied to both driver packages.
- Validation failures now return translated messages. They previously used the
  `laravel-email-verification::` namespace while the translations were
  registered under `email-verification::`, so users saw the raw translation key.
- A driver package registered before the core package no longer loses its
  driver. Registration is now deferred with `callAfterResolving()` instead of
  resolving the manager during `register()`, which built a throwaway manager
  when the order happened to be reversed.

### Changed

- **Breaking:** the Laravel-facing names now follow the Spatie Laravel Package
  Tools short-name convention throughout. The Composer and GitHub package names
  are unchanged, but the config files, config keys, translation namespace,
  publish tags, and install commands all drop the `laravel-` prefix:

  | | Before | After |
  | --- | --- | --- |
  | Core config | `config/laravel-email-verification.php` | `config/email-verification.php` |
  | Emailable config | `config/laravel-email-verification-emailable.php` | `config/email-verification-emailable.php` |
  | Bouncer config | `config/laravel-email-verification-bouncer.php` | `config/email-verification-bouncer.php` |
  | Config keys | `laravel-email-verification.*` | `email-verification.*` |
  | Translations | `laravel-email-verification::` | `email-verification::` |
  | Publish tags | `laravel-email-verification-config` | `email-verification-config` |
  | Install command | `laravel-email-verification:install` | `email-verification:install` |

- **Breaking:** `EmailValidation` no longer performs any email-syntax checking.
  Pair it with Laravel's own `email` rule, which should run first under `bail`.
  Only the non-string guard for the rule's own type contract remains.
- **Breaking:** retry configuration moved from `laravel-email-verification.retry`
  into each driver package (`email-verification-bouncer.retry`,
  `email-verification-emailable.retry`), with per-provider env vars.
- **Breaking:** provider timeouts are now configuration rather than constants,
  under each driver package's `timeout.server` and `timeout.client`.
- **Breaking:** the `email.service_unavailable` translation key is now
  `email.unverifiable`, matching the `EmailVerificationStatus` case it reports.
- The core configuration file is now just `default` and `allowed_domains`.
- Active development and package splitting now target the `2.x` branch.

### Removed

- **Breaking:** `Misaf\LaravelEmailVerification\Support\TransientFault`. Each
  driver package now owns its own retry predicate.
- The core no longer requires `illuminate/http` or `ext-mbstring`; it makes no
  HTTP requests and no longer uses an mbstring function.

## 1.1.0 - 2026-08-30

### Added

- Configurable retry budget (`retry.times`, `retry.sleep_milliseconds`) shared
  by the HTTP drivers.
- Shared `TransientFault` retry predicate, retrying only connection failures
  and server-side 5xx responses.
- The `EmailValidation` rule now rejects an address with no domain part using
  the translated `email.invalid` message.

### Changed

- Transient, self-resolving failures log at warning, while a rejected key and
  unexpected exceptions still log at error.
- The emailable driver sends its own server-side timeout parameter.

## 1.0.0 - 2026-08-27

Initial release.
