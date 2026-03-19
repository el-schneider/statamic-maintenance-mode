<?php

declare(strict_types=1);

namespace Tests;

use ElSchneider\StatamicMaintenanceMode\ServiceProvider;
use Statamic\Facades\CP\Nav;
use Statamic\Testing\AddonTestCase;

abstract class TestCase extends AddonTestCase
{
    protected string $addonServiceProvider = ServiceProvider::class;

    protected function setUp(): void
    {
        parent::setUp();

        // Statamic v6 calls Nav::clearCachedUrls() when saving collections
        Nav::shouldReceive('clearCachedUrls')->zeroOrMoreTimes();
    }

    protected function resolveApplicationConfiguration($app): void
    {
        parent::resolveApplicationConfiguration($app);

        $app['config']->set('statamic.users.repository', 'file');
        $app['config']->set('statamic.editions.pro', true);
        $app['config']->set('view.paths', [__DIR__.'/views']);
    }
}
