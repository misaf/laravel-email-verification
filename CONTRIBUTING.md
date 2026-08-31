# Contributing

Thank you for considering a contribution to Laravel Email Verification and its
first-party drivers.

## Before You Start

Use [GitHub Issues](https://github.com/misaf/laravel-email-verification/issues)
to report reproducible bugs or propose substantial changes. Please search the
existing issues first. Questions and security vulnerabilities should not be
submitted as bug reports; report vulnerabilities according to
[SECURITY.md](SECURITY.md).

Small fixes and documentation improvements may be submitted directly as a pull
request.

## Development Setup

This repository requires PHP 8.4 and Composer. Fork and clone the repository,
then install its development dependencies:

```bash
composer install
```

The provider-neutral core lives in `src/`. The Bouncer and Emailable
integrations are independent packages under `Drivers/`. Each driver owns its
provider-specific configuration, HTTP behavior, tests, and documentation.

## Making Changes

- Keep the core provider-neutral; it must not depend on a provider SDK or
  `Illuminate\Http\Client`.
- Depend on `Contracts\EmailVerification` across package boundaries rather than
  a concrete driver.
- Keep provider endpoints, credentials, timeouts, retries, response mapping,
  and logging in the owning driver package.
- Prefer a little duplication between driver packages over introducing a
  shared provider abstraction.
- Use strict types, PSR-4 namespaces, four-space indentation, and the naming
  conventions already used by the package.
- Add behavior-focused Pest coverage beside the package that owns the changed
  behavior. Update architecture tests when a dependency boundary changes.
- Update the relevant README and `CHANGELOG.md` when public behavior,
  configuration, or upgrade requirements change.

Do not include API keys, real customer email addresses, or other sensitive data
in fixtures, logs, issues, or pull requests.

## Verification

Run the smallest relevant test first, then the complete checks:

```bash
vendor/bin/pest tests/Feature/EmailValidationTest.php
vendor/bin/pest --parallel
composer analyse
vendor/bin/pint --dirty --format agent
composer validate --strict
```

When changing a driver, run its focused tests as well, for example:

```bash
vendor/bin/pest Drivers/laravel-email-verification-bouncer/tests
vendor/bin/pest Drivers/laravel-email-verification-emailable/tests
```

## Pull Requests

Create a focused branch and use Conventional Commit-style commit messages, such
as `fix(rule): reject invalid domains` or `docs(emailable): clarify retries`.

Complete the pull request template. Explain the outcome and motivation, identify
public API, configuration, and cross-package effects, list the verification you
performed, and link related issues. Include upgrade instructions for breaking
or manual changes. Screenshots are only necessary for visual documentation
changes.

By submitting a contribution, you agree that it will be licensed under the
project's [MIT License](LICENSE).
