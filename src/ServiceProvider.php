<?php

declare(strict_types=1);

namespace ElSchneider\StatamicMaintenanceMode;

use ElSchneider\StatamicMaintenanceMode\Http\Controllers\MaintenanceModeController;
use ElSchneider\StatamicMaintenanceMode\Http\Controllers\MaintenanceStatusController;
use ElSchneider\StatamicMaintenanceMode\Http\Middleware\PreventRequestsDuringMaintenance;
use Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance as LaravelMiddleware;
use Illuminate\Support\Facades\Route;
use Statamic\CP\Utilities\Utility;
use Statamic\Facades\Permission;
use Statamic\Facades\Utility as UtilityFacade;
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

        $this->registerPermissions();
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

        // Only load Vite assets when Statamic v6+ is available (inertia component)
        if ($this->supportsInertia()) {
            $this->vite = [
                'input' => [
                    'resources/js/addon.js',
                ],
                'publicDirectory' => 'resources/dist',
            ];
        }

        parent::register();
    }

    protected function registerPermissions(): void
    {
        Permission::extend(function ($permissions) {
            $permissions->group('utilities', function () use ($permissions) {
                $permissions->register(Permissions::BYPASS_MAINTENANCE_MODE, function ($permission) {
                    $permission
                        ->label(__('Bypass maintenance mode'))
                        ->description(__('View the site while maintenance mode is active.'));
                });
            });
        });
    }

    protected function registerUtility(): void
    {
        UtilityFacade::extend(function () {
            $utility = UtilityFacade::register('maintenance-mode')
                ->title(__('Maintenance Mode'))
                ->navTitle(__('Maintenance'))
                ->description(__('Configure and activate maintenance mode'))
                ->icon($this->supportsInertia() ? 'construction-barrier' : 'hammer-wrench')
                ->routes(function ($router) {
                    $router->post('/', [MaintenanceModeController::class, 'store'])->name('store');
                    $router->post('/activate', [MaintenanceModeController::class, 'activate'])->name('activate');
                    $router->post('/deactivate', [MaintenanceModeController::class, 'deactivate'])->name('deactivate');
                });

            if ($this->supportsInertia()) {
                $utility->inertia('MaintenanceMode', fn ($request) => $this->getUtilityData());
            } else {
                $utility->view('statamic-maintenance-mode::cp.utility', fn ($request) => $this->getUtilityData());
            }
        });
    }

    protected function supportsInertia(): bool
    {
        return method_exists(Utility::class, 'inertia');
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
