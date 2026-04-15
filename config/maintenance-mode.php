<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Selectable Collections
    |--------------------------------------------------------------------------
    |
    | The collection handles that can be selected as maintenance pages or
    | whitelisted pages in the control panel utility.
    |
    */
    'collections' => ['pages'],

    /*
    |--------------------------------------------------------------------------
    | Down Command Options
    |--------------------------------------------------------------------------
    |
    | Options passed to Laravel's `artisan down` command when activating
    | maintenance mode.
    |
    | - retry: Seconds for the Retry-After header (default: 60)
    | - secret: Set to `true` to generate a random bypass URL, or provide a
    |           custom string. Visitors who access the bypass URL receive a
    |           cookie allowing them to browse normally during maintenance.
    | - refresh: Seconds before the browser auto-refreshes (optional)
    |
    */
    'down_options' => [
        'retry' => 60,
        'secret' => true,
        // 'refresh' => 15,
    ],

    /*
    |--------------------------------------------------------------------------
    | Show Frontend Notice
    |--------------------------------------------------------------------------
    |
    | When enabled, the {{ maintenance_notice }} tag will display a notice
    | to authenticated users who can bypass maintenance mode. This helps
    | remind content editors that the site is in maintenance mode.
    |
    */
    'show_frontend_notice' => true,

    /*
    |--------------------------------------------------------------------------
    | Only Super Users have Maintenance mode menu
    |--------------------------------------------------------------------------
    |
    | Controls whether to display the maintenance mode in utilities nav for all
    | users or superusers only
    |
    */
    'show_menu_for_supers_only' => false,

    /*
    |--------------------------------------------------------------------------
    | Permissions Allowed to Bypass Maintenance Mode
    |--------------------------------------------------------------------------
    |
    | Super users and users with at least one of the below permissions will be
    | allowed to bypass maintenance mode
    | By default, users with "access cp" permissions can bypass maintenance mode
    |
    */
    'allow_bypass_for_perms' => ['access cp'],
];
