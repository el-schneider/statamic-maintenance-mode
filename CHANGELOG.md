# Changelog

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
