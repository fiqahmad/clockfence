# Security Policy

## Reporting a vulnerability

Please **do not open a public issue**. Report privately through [GitHub security advisories](https://github.com/fiqahmad/clockfence/security/advisories/new).

Include what you found, how to reproduce it, and the impact. You will get an acknowledgement within 7 days and a fix or plan within 30 days for confirmed issues. Reporters are credited in the advisory unless they prefer otherwise.

## What counts

Besides the usual (authentication, authorisation, injection, data exposure), **any way to record a Clock without being at the Site counts as a security issue**, for example:

- Reusing, sharing or forging a Clock token
- Clocking at a Site other than your home Site
- Skipping the Geo-check, or opening the camera step without one
- Exposing distance, coordinates or tokens in any page, API response or export
- Overriding your own Attendance

Known limitation, not a vulnerability: a browser cannot detect a mocked GPS location. See the README.

## Supported versions

Until v1.0, only the latest `main` receives fixes. After v1.0, the latest minor release does.
