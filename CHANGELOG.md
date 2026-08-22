# Changelog

All notable changes to `misaf/laravel-email-validation` and its first-party
driver packages are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## 1.4.1 - 2026-08-22

### Added

- `EmailValidation` now rejects non-string input with a localized message via the new `validation.email.invalid` key (en, de, fa).
- The core package suggests `misaf/laravel-email-validation-bouncer` and `misaf/laravel-email-validation-emailable`, so the first-party drivers are discoverable on install.
- Feature coverage for both first-party drivers installed side by side: independent resolution from one manager, per-rule driver selection, and `default` selection.
- README documentation for the install paths (core alone, core plus one driver, core plus both) and for running both drivers together.

### Changed

- Both drivers now retry only faults a later attempt could resolve — connection failures and 5xx responses. A 4xx, including a 429 rate limit, is no longer retried and no longer burns paid API quota.
- `allowed_domains` entries are trimmed and lower-cased before comparison, in both the shipped config and the rule, so `EMAIL_ALLOWED_DOMAINS=example.com, Example.org` matches as written.
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

- Point the package split workflow's `package_directory` at `src/Verifiers`.

## 1.0.0 - 2026-07-26

### Added

- Initial release: the `EmailValidation` rule with a domain allow-list, the driver-based `EmailVerifierManager` with the `null` driver, the `EmailVerifier` contract and facade, the `EmailVerificationStatus` enum, translations for en/de/fa, and the Bouncer and Emailable driver packages.
