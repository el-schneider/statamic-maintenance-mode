<?php

declare(strict_types=1);

namespace Tests;

use ElSchneider\StatamicMaintenanceMode\ServiceProvider;
use Statamic\Testing\AddonTestCase;

abstract class TestCase extends AddonTestCase
{
    protected string $addonServiceProvider = ServiceProvider::class;

    protected function resolveApplicationConfiguration($app): void
    {
        parent::resolveApplicationConfiguration($app);

        $app['config']->set('statamic.users.repository', 'file');
        $app['config']->set('statamic.editions.pro', true);
        $app['config']->set('statamic.maintenance-mode', require __DIR__.'/../config/maintenance-mode.php');
        $app['config']->set('view.paths', [__DIR__.'/views']);
    }
}
