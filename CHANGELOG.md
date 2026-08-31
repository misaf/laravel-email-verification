# Changelog

All notable changes to `misaf/laravel-email-verification` and its first-party
driver packages are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.1.1] - 2026-08-31

### Changed

- Removed redundant PHPDoc comments from the core contract, null driver,
  `EmailValidation` rule, both first-party drivers, and the package
  registration tests.
- The Bouncer README and configuration header now describe the deliverability
  API without the redundant real-time qualifier.
- Fixed a copy-paste error in the Bouncer README that attributed the Emailable
  API key to the Bouncer driver.

No runtime behavior changed in this release.

## [2.1.0] - 2026-08-30

### Added

- The install command now publishes the core configuration file.

### Changed

- **Breaking for source checkouts:** the first-party Bouncer and Emailable
  packages moved from `src/Drivers/` to the top-level `Drivers/` directory.
  Composer path repositories, monorepo tooling, CI, test discovery, and package
  export rules now use the new paths.
- The root README is now a shorter quick start, with detailed provider setup and
  HTTP behavior kept in each driver package's README.
- Documentation now distinguishes `EmailValidation`, which enforces the domain
  allow-list, from direct manager or facade verification, which does not.
- Empty test scaffolding directories were removed.

## [2.0.0] - 2026-08-30

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
  into each driver package (`email-verification-bouncer.retry` and
  `email-verification-emailable.retry`), with per-provider environment variables.
- **Breaking:** provider timeouts are now configuration rather than constants,
  under each driver package's `timeout.server` and `timeout.client` keys.
- **Breaking:** the `email.service_unavailable` translation key is now
  `email.unverifiable`, matching the `EmailVerificationStatus` case it reports.
- The core configuration file is now limited to `default` and `allowed_domains`.
- Active development and package splitting now target the `2.x` branch.

### Removed

- **Breaking:** `Misaf\LaravelEmailVerification\Support\TransientFault`. Each
  driver package now owns its own retry predicate.
- The core no longer requires `illuminate/http` or `ext-mbstring`; it makes no
  HTTP requests and no longer uses an mbstring function.

## [1.1.1] - 2026-08-30

This tag points to the same commit as `1.1.0`; there are no additional package,
driver, documentation, or build changes in this release.

## [1.1.0] - 2026-08-30

### Added

- Configurable retry budget (`retry.times` and `retry.sleep_milliseconds`)
  shared by the HTTP drivers.
- Shared `TransientFault` retry predicate, retrying only connection failures
  and server-side 5xx responses.
- `EmailValidation` now rejects an address with no domain part using the
  translated `email.invalid` message.
- Strict `allowed_domains` validation rejects non-string, blank, and
  whitespace-padded entries. Domain comparisons remain case-insensitive.

### Changed

- **Breaking:** the core `EmailVerifier` contract, `EmailVerifierManager`, null
  implementation, facade surface, and first-party provider implementations were
  renamed to `EmailVerification` terminology.
- **Breaking:** manager selection now uses Laravel's native `driver()` API; the
  custom `verifier()` method was removed, and `EmailValidation`'s constructor
  argument is now named `$driver`.
- **Breaking:** the default-driver environment variable changed from
  `EMAIL_VERIFIER_DRIVER` to `EMAIL_VERIFICATION_DRIVER`.
- Transient, self-resolving failures log at warning, while rejected credentials
  and unexpected exceptions still log at error.
- The Emailable driver sends its own server-side timeout parameter.
- Driver suites now use package-owned test cases, and architecture tests prevent
  dependencies between first-party drivers.
- Direct unused Symfony requirements were removed from the core package.

### Removed

- Package version reporting through Laravel's `about` command.

## [1.0.1] - 2026-08-27

### Changed

- Refined Composer package descriptions and discovery keywords for the core and
  first-party driver packages.
- Expanded the Emailable driver documentation with its request, timeout, error,
  logging, and privacy behavior.
- Updated the Claude issue workflow action dependency.

No runtime behavior changed in this release.

## [1.0.0] - 2026-08-27

Initial release.

[2.1.1]: https://github.com/misaf/laravel-email-verification/compare/v2.1.0...v2.1.1
[2.1.0]: https://github.com/misaf/laravel-email-verification/compare/v2.0.0...v2.1.0
[2.0.0]: https://github.com/misaf/laravel-email-verification/compare/v1.1.1...v2.0.0
[1.1.1]: https://github.com/misaf/laravel-email-verification/compare/v1.1.0...v1.1.1
[1.1.0]: https://github.com/misaf/laravel-email-verification/compare/v1.0.1...v1.1.0
[1.0.1]: https://github.com/misaf/laravel-email-verification/compare/v1.0.0...v1.0.1
[1.0.0]: https://github.com/misaf/laravel-email-verification/releases/tag/v1.0.0
