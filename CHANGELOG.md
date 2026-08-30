# Changelog

All notable changes to `misaf/laravel-email-verification` and its first-party
driver packages are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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
