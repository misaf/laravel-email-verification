# Changelog

All notable changes to `misaf/laravel-email-verification` and its first-party
driver packages are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## 1.0.0 - 2026-08-27

Initial release.

### Added

- `EmailValidation` validation rule, enforcing an optional domain allow-list
  followed by pluggable deliverability verification.
- `EmailVerifierManager`, a Laravel `Manager` that resolves deliverability
  drivers and accepts custom ones via `extend()`.
- `EmailVerifier` contract and facade, for verifying an address outside the
  validation layer.
- `EmailVerificationStatus` enum with explicit `Deliverable`, `Risky`,
  `Undeliverable`, and `Unverifiable` outcomes.
- `null` driver, which performs no external check and treats every address as
  deliverable.
- Configurable `allowed_domains` allow-list, compared case-insensitively and
  unrestricted when left empty.
- Translated failure messages for English, German, and Persian.
- `misaf/laravel-email-verification-emailable`, adding the `emailable` driver
  backed by the [Emailable](https://emailable.com) API.
- `misaf/laravel-email-verification-bouncer`, adding the `bouncer` driver
  backed by the [Bouncer](https://usebouncer.com) API.
