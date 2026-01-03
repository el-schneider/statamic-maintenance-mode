# Statamic Maintenance Mode

> Manage maintenance mode through Statamic's control panel using Laravel's native maintenance system

## Features

- **Laravel Native:** Uses Laravel's built-in `artisan down`/`up` commands, supporting all standard options like retry headers, refresh intervals, and secret bypass URLs
- **Flexible Display:** Show any Statamic entry as your maintenance page, or use Laravel's default 503 error template
- **Bypass URLs:** Generate shareable secret URLs that grant access during maintenance
- **Page Whitelisting:** Keep specific entries accessible while the rest of the site is down
- **CP and CLI:** Manage maintenance mode through the control panel or terminal interchangeably

## Installation

```bash
composer require el-schneider/statamic-maintenance-mode
```

## Usage

Navigate to **Utilities > Maintenance** in the control panel to configure and activate maintenance mode.

Control panel users with "access cp" permission can browse the frontend during maintenance.

## Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --tag=statamic-maintenance-mode-config
```

Refer to the published configuration file (`config/statamic/maintenance-mode.php`) for available options.

## Customizing the Error Page

When no Statamic entry is selected, visitors see Laravel's default 503 error page.

To customize it, publish Laravel's error templates:

```bash
php artisan vendor:publish --tag=laravel-errors
```

Then edit `resources/views/errors/503.blade.php`.

## Upgrading from v2.x

Version 3.0 stores configuration in `content/maintenance-mode.yaml` instead of Statamic globals. After upgrading, reconfigure your settings in **Utilities > Maintenance**.
