## Summary

Describe the outcome and the reason for the change.

## Scope

- Host application or package:
- Public APIs or configuration affected:
- Cross-package dependencies affected:

## Verification

Run the smallest relevant test scope first with `php artisan test --parallel` by
default, then expand only when necessary. Omit `--parallel` only for debugging,
concurrency investigation, non-isolatable shared resources, unsupported
environments, or intentionally non-parallel coverage, profiling, mutation
testing, and benchmarking commands.

List the tests, static analysis, formatting, and manual checks run.

## Checklist

- [ ] The change stays within the owning host or package boundary.
- [ ] No unnecessary cross-package dependency or concrete provider coupling was added.
- [ ] Public behavior, configuration, and examples are documented.
- [ ] New or changed behavior has focused automated coverage.
- [ ] Relevant tests and static analysis pass.
- [ ] PHP changes were formatted with `vendor/bin/pint --dirty --format agent`.
- [ ] Upgrade instructions were added when manual action is required.
