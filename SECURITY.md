# Security Policy

## Supported Versions

Security fixes are provided for the current major release:

| Version | Supported |
| --- | --- |
| 2.x | Yes |
| 1.x | No |

Users should install the latest release in the supported major line before
reporting a vulnerability.

## Reporting a Vulnerability

Please do not disclose suspected vulnerabilities in a public issue, discussion,
or pull request. Use GitHub's
[private vulnerability reporting](https://github.com/misaf/laravel-email-verification/security/advisories/new)
to send a confidential report.

Include as much of the following as possible:

- the affected package and version;
- the affected configuration or driver;
- a clear description of the impact and attack scenario;
- minimal reproduction steps or a proof of concept;
- any known mitigations; and
- whether the vulnerability has been disclosed elsewhere.

Do not include real API credentials, customer email addresses, or other
third-party data. Use redacted values and a minimal test application where
possible.

The maintainer will assess the report, may ask for additional information, and
will coordinate remediation and disclosure with the reporter. Please allow time
for a fix to be prepared and released before publishing details.

This policy covers the core package and the first-party Bouncer and Emailable
driver packages maintained in this repository. Vulnerabilities in Laravel,
provider APIs, or other third-party dependencies should also be reported to the
responsible upstream project.
