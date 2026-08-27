# Changelog

All notable changes to `misaf/laravel-email-verification` and its first-party
driver packages are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## 1.0.0 - 2026-08-27

### Changed

- **Breaking.** The package is renamed from `misaf/laravel-email-validation` to `misaf/laravel-email-verification`, and the driver packages from `misaf/laravel-email-validation-{bouncer,emailable}` to `misaf/laravel-email-verification-{bouncer,emailable}`.
- **Breaking.** The root namespace is renamed from `Misaf\LaravelEmailValidation` to `Misaf\LaravelEmailVerification` (and `…Bouncer` / `…Emailable` for the drivers).
- **Breaking.** `Misaf\LaravelEmailValidation\Verifiers` is renamed to `Misaf\LaravelEmailVerification\Drivers`, and the monorepo directory `src/Verifiers` to `src/Drivers`.
- **Breaking.** `EmailValidationServiceProvider` is renamed to `EmailVerificationServiceProvider`.
- **Breaking.** The published config files are renamed to `laravel-email-verification.php`, `laravel-email-verification-bouncer.php`, and `laravel-email-verification-emailable.php`, along with their `vendor:publish` tags and every `config()` key.

---

# Pre-rename history

The entries below belong to `misaf/laravel-email-validation`, the package this
one was renamed from. Its tags and releases were removed when version numbering
restarted at 1.0.0.

## 1.4.1 - 2026-08-22

### Added

- `EmailValidation` now rejects non-string input with a localized message via the new `validation.email.invalid` key (en, de, fa).
- The core package suggests `misaf/laravel-email-verification-bouncer` and `misaf/laravel-email-verification-emailable`, so the first-party drivers are discoverable on install.
- Feature coverage for both first-party drivers installed side by side: independent resolution from one manager, per-rule driver selection, and `default` selection.
- README documentation for the install paths (core alone, core plus one driver, core plus both) and for running both drivers together.

### Changed

- Both drivers now retry only faults a later attempt could resolve — connection failures and 5xx responses. A 4xx, including a 429 rate limit, is no longer retried and no longer burns paid API quota.
- PHPStan at level 10 now analyses the driver packages, which were previously excluded and unchecked.
- Driver package tooling is consolidated at the repository root. The driver packages no longer carry their own PHPStan, PHPUnit, or Pint configuration, Composer scripts, or development dependencies; all development happens in the monorepo.
- Driver READMEs point contributors at the monorepo and state the correct PHP requirement (8.4+, previously documented as 8.3+).

### Removed

- Debug logging of rejected email domains, which logged user-supplied input on an ordinary validation failure.

### Fixed

- A non-string value passed the attribute name to `$fail()`, surfacing the raw attribute name to the user instead of a message.
- The root `Unit` test suite pointed at the whole `tests` tree, so `tests/Feature` ran in both suites; `tests/ArchTest.php` is now registered explicitly so it stays collected.

## 1.4.0 - 2026-08-22

### Fixed

- Point the package split workflow's `package_directory` at `src/Drivers`.

## 1.0.0 - 2026-07-26

### Added

- Initial release: the `EmailValidation` rule with a domain allow-list, the driver-based `EmailVerifierManager` with the `null` driver, the `EmailVerifier` contract and facade, the `EmailVerificationStatus` enum, translations for en/de/fa, and the Bouncer and Emailable driver packages.
