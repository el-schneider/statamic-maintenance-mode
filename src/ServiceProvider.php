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

    protected $vite = [
        'input' => [
            'resources/js/addon.js',
        ],
        'publicDirectory' => 'resources/dist',
    ];

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
                ->inertia('MaintenanceMode', fn () => $this->getUtilityData())
                ->routes(function ($router) {
                    $router->post('/', [MaintenanceModeController::class, 'store'])->name('store');
                    $router->post('/activate', [MaintenanceModeController::class, 'activate'])->name('activate');
                    $router->post('/deactivate', [MaintenanceModeController::class, 'deactivate'])->name('deactivate');
                });
        });
    }

    protected function getUtilityData(): array
    {
        $config = app(MaintenanceModeConfig::class);
        $collections = config('statamic.maintenance-mode.collections', ['pages']);

        $blueprintFields = [];
        if (! empty($collections)) {
            $blueprintFields = [
                'maintenance_entry' => [
                    'type' => 'entries',
                    'display' => __('Maintenance Page'),
                    'instructions' => __('Select an entry to display during maintenance. If not set, a default template will be used.'),
                    'max_items' => 1,
                    'create' => false,
                    'collections' => $collections,
                ],
                'whitelist_entries' => [
                    'type' => 'entries',
                    'display' => __('Whitelisted Pages'),
                    'instructions' => __('These pages will remain accessible during maintenance mode.'),
                    'create' => false,
                    'collections' => $collections,
                ],
            ];
        }

        $blueprint = \Statamic\Facades\Blueprint::makeFromFields($blueprintFields);

        $maintenanceEntryValue = $config->maintenanceEntryId() ? [$config->maintenanceEntryId()] : [];
        $whitelistValue = $config->whitelistEntryIds();

        $fields = $blueprint->fields()->addValues([
            'maintenance_entry' => $maintenanceEntryValue,
            'whitelist_entries' => $whitelistValue,
        ])->preProcess();

        return [
            'title' => __('Maintenance Mode'),
            'isActive' => app()->isDownForMaintenance(),
            'secretUrl' => $this->getSecretUrl(),
            'hasCollections' => ! empty($collections),
            'blueprint' => $blueprint->toPublishArray(),
            'meta' => $fields->meta(),
            'values' => $fields->values(),
            'activateUrl' => cp_route('utilities.maintenance-mode.activate'),
            'deactivateUrl' => cp_route('utilities.maintenance-mode.deactivate'),
            'storeUrl' => cp_route('utilities.maintenance-mode.store'),
        ];
    }

    protected function getSecretUrl(): ?string
    {
        if (! app()->isDownForMaintenance()) {
            return null;
        }

        $data = app()->maintenanceMode()->data();
        $secret = $data['secret'] ?? null;

        return $secret ? url($secret) : null;
    }
}
