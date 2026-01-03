<?php

declare(strict_types=1);

namespace ElSchneider\StatamicMaintenanceMode;

use ElSchneider\StatamicMaintenanceMode\Http\Controllers\MaintenanceModeController;
use ElSchneider\StatamicMaintenanceMode\Http\Controllers\MaintenanceStatusController;
use ElSchneider\StatamicMaintenanceMode\Http\Middleware\PreventRequestsDuringMaintenance;
use Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance as LaravelMiddleware;
use Illuminate\Support\Facades\Route;
use Statamic\Facades\Utility;
use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    protected $viewNamespace = 'statamic-maintenance-mode';

    public function bootAddon()
    {
        $this->loadJsonTranslationsFrom(__DIR__.'/../lang');

        $this->publishes([
            __DIR__.'/../config/maintenance-mode.php' => config_path('statamic/maintenance-mode.php'),
        ], 'statamic-maintenance-mode-config');

        $this->registerUtility();
        $this->registerActionRoutes(function () {
            Route::get('status', MaintenanceStatusController::class)
                ->name('status');
        });
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/maintenance-mode.php', 'statamic.maintenance-mode');

        $this->app->singleton(MaintenanceModeConfig::class, function () {
            return new MaintenanceModeConfig;
        });

        // Replace Laravel's maintenance middleware with ours
        $this->app->bind(LaravelMiddleware::class, PreventRequestsDuringMaintenance::class);

        parent::register();
    }

    protected function registerUtility(): void
    {
        Utility::extend(function () {
            Utility::register('maintenance-mode')
                ->title(__('Maintenance Mode'))
                ->navTitle(__('Maintenance'))
                ->description(__('Configure and activate maintenance mode'))
                ->icon('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>')
                ->routes(function ($router) {
                    $router->get('/', [MaintenanceModeController::class, 'index'])->name('index');
                    $router->post('/', [MaintenanceModeController::class, 'store'])->name('store');
                    $router->post('/activate', [MaintenanceModeController::class, 'activate'])->name('activate');
                    $router->post('/deactivate', [MaintenanceModeController::class, 'deactivate'])->name('deactivate');
                });
        });
    }
}
