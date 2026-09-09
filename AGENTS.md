# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a Statamic CMS addon (`el-schneider/statamic-maintenance-mode`) that enables maintenance mode with page whitelisting. When active, visitors see a maintenance page while users with CP access can browse normally.

**Statamic Compatibility:** 5.0+ and 6.0+

## Common Commands

```bash
# Run tests
./vendor/bin/pest

# Run a single test file
./vendor/bin/pest tests/Feature/MaintenanceModeTest.php

# Run tests with filter
./vendor/bin/pest --filter="test name"

# Code style (Laravel preset with strict types)
./vendor/bin/pint

# Check code style without fixing
./vendor/bin/pint --test
```

## Testing

Uses Pest 4.0 with Statamic's `AddonTestCase` (via Orchestra Testbench 10.0). Tests run against an SQLite in-memory database configured in `phpunit.xml`. The base `TestCase` extends `Statamic\Testing\AddonTestCase` and configures flat-file users repository.

## Manual Testing

Sandboxes, URLs, ports, and credentials are machine-specific and live in an untracked `AGENTS.local.md` (in this directory or a parent). Read it before running the addon in a Statamic app.

Verify CP and frontend behavior in a real browser; code review alone is not proof.

## Asset Builds

Do not commit rebuilt `resources/dist` assets for routine dependency/test maintenance. The `Build assets for distribution` GitHub Action rebuilds and commits dist files on `main` when frontend sources or package files change.

## Dependency Security

Keep `.npmrc` hardening intact. Do not bypass `min-release-age=7` unless explicitly applying a known security fix that has not aged past the release window.
