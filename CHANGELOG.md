# Changelog

## v0.3.1 - 2026-06-16

### What's fixed

- Fixed route trimming compatibility for PHP 8.3.

### Maintenance

- Hardened dependency maintenance by adding Composer and npm audits to CI.
- Removed Dependabot auto-merge.
- Switched Composer minimum stability to stable.
- Updated npm dependency lockfile and Vite tooling to resolve audit advisories.
- Disabled Pint's `mb_str_functions` rule because it can rewrite code to PHP 8.4-only functions while the addon supports PHP 8.2+.
- Documented dependency and asset build guardrails for future agents.

## v0.3.0 - 2026-05-17

### Breaking changes

- Non-super users now need the new `bypass maintenance mode` permission to browse the frontend while maintenance mode is active. Previously, any user with `access cp` could bypass maintenance mode. Grant `bypass maintenance mode` to existing roles that should keep frontend access during maintenance.

### What's new

- Added a dedicated `bypass maintenance mode` Statamic permission.

### Maintenance

- Raised supported Statamic versions to security-patched releases: `^5.73.21 || ^6.15`.
- Updated npm lockfile dependencies to resolve reported advisories.

## v0.2.0 - 2026-03-19

### What's new

- **Statamic v6 support** — the addon now works with both Statamic v5 and v6 from a single package (`"statamic/cms": "^5.0 || ^6.0"`)
- **Vue3 utility page** on Statamic v6 via Inertia with SPA transitions, command palette integration, and PublishContainer
- **Native Statamic icons** — uses built-in icons per version instead of inline SVG
- **CI test matrix** covering Statamic v5 and v6

## v0.1.0 - 2026-01-04

### What's new

- Maintenance mode integration for Statamic with Laravel's maintenance mode options
- Custom tag (`{{ maintenance_notice }}`) to display maintenance notice in templates
- Collection selection for customizing maintenance pages
- Translation support
- Static caching instructions for Nginx and Apache

### What's fixed

- Authenticated users now correctly skip maintenance mode
- Session cookies handling for super users
