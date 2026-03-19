# Changelog

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
