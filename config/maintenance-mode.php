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
];
