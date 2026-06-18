<img src="images/mm_banner.png" alt="Auto Alt Text">

# Statamic Maintenance Mode

> Manage maintenance mode through Statamic's control panel using Laravel's native maintenance system

## Requirements

- Statamic 5 or 6
- PHP 8.3+

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

The addon automatically detects your Statamic version and uses the appropriate UI — an Inertia Vue component on v6, a Blade view on v5.

## Usage

Navigate to **Utilities > Maintenance** in the control panel to configure and activate maintenance mode.

Access is controlled through Statamic permissions:

- `access maintenance-mode utility` shows the utility in the control panel and allows managing maintenance mode.
- `bypass maintenance mode` allows a user to browse the frontend while maintenance mode is active.

Super users can always access both.

When upgrading, grant `bypass maintenance mode` to any non-super roles that previously relied on `access cp` to browse during maintenance.

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

## Static Caching

When using Statamic's [full measure static caching](https://statamic.dev/static-caching#full-measure), your web server serves pre-rendered HTML files directly, bypassing PHP entirely. This means Laravel's maintenance mode check won't run for cached pages.

To ensure maintenance mode works correctly, modify your server config to skip static file serving when the `down` file exists. This forces requests through PHP where Laravel can handle maintenance mode properly.

**Nginx:**

```nginx
set $try_static_files "";

if (-f $document_root/../storage/framework/down) {
    set $try_static_files "skip";
}

if (-f "${document_root}/static${uri}_${query_string}.html") {
    set $try_static_files "${try_static_files}+exists";
}

if ($try_static_files = "+exists") {
    rewrite ^(.*)$ /static$1_$query_string.html last;
}
```

**Apache (.htaccess):**

```apache
RewriteCond %{DOCUMENT_ROOT}/../storage/framework/down !-f
RewriteCond %{DOCUMENT_ROOT}/static%{REQUEST_URI}_%{QUERY_STRING}\.html -s
RewriteRule .* static%{REQUEST_URI}_%{QUERY_STRING}.html [L,T=text/html]
```

Half measure static caching works without server configuration since requests still pass through PHP.
