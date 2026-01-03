<!-- statamic:hide -->

# Statamic Maintenance Mode

## How to Install

You can search for this addon in the `Tools > Addons` section of the Statamic control panel and click **install**, or run the following command from your project root:

```bash
composer require el-schneider/statamic-maintenance-mode
```

## How to Use

1. Navigate to **Utilities > Maintenance** in the Statamic Control Panel
2. (Optional) Select a Statamic entry to display as the maintenance page
3. (Optional) Select entries that should remain accessible during maintenance
4. Click **Save** to store your configuration
5. Click **Activate** to enable maintenance mode

When maintenance mode is active:

- Visitors see a 503 response with your configured maintenance page (or a default template)
- CP users with "access cp" permission can browse the site normally
- Whitelisted pages remain accessible to everyone

Maintenance mode uses Laravel's built-in system (`php artisan down`/`up`), so you can also manage it via CLI.

## Configuration

Publish the config file to customize the addon:

```bash
php artisan vendor:publish --tag=statamic-maintenance-mode-config
```

Then edit `config/statamic/maintenance-mode.php`:

```php
return [
    // Collections available for selection in the utility panel
    'collections' => ['pages'],

    // Options passed to Laravel's `artisan down` command
    'down_options' => [
        'retry' => 60,
        'secret' => true,  // or a custom string like 'my-secret-bypass'
    ],
];
```

### Bypass URL for External Visitors

Set `secret` to generate a bypass URL you can share with clients or stakeholders:

- `true` - generates a random secret URL
- `'my-custom-secret'` - uses your custom string

When someone visits the bypass URL, they receive a cookie that lets them browse the site normally during maintenance. The bypass URL is displayed in the control panel with a copy button when maintenance mode is active.

## Customizing the Maintenance Page

By default, visitors see Laravel's built-in 503 error page. You have two options to customize it:

### Option 1: Use a Statamic Entry (Recommended)

Select any Statamic entry as your maintenance page in the utility panel. This gives you full control using your existing templates and content.

### Option 2: Publish Laravel's Error Views

Run the following command to publish Laravel's error templates:

```bash
php artisan vendor:publish --tag=laravel-errors
```

Then edit `resources/views/errors/503.blade.php` to customize the maintenance page.

## Upgrading from v2.x

Version 3.0 changes how configuration is stored. Instead of Statamic globals, configuration is now stored in `content/maintenance-mode.yaml`.

**After upgrading:**

1. Navigate to **Utilities > Maintenance** in the CP
2. Reconfigure your maintenance entry and whitelist entries
3. Click **Save**

Your existing globals-based configuration will not be migrated automatically.

## Issues and Support

If you encounter any issues or have questions, please create an issue on the [GitHub](https://github.com/el-schneider/statamic-maintenance-mode/issues) repository for this addon.

## License

This addon is released under the [MIT License](LICENSE.md).
