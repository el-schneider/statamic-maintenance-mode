<?php

declare(strict_types=1);

namespace ElSchneider\StatamicMaintenanceMode\Http\Controllers;

use ElSchneider\StatamicMaintenanceMode\MaintenanceModeConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Statamic\Facades\CP\Toast;
use Statamic\Http\Controllers\CP\CpController;

class MaintenanceModeController extends CpController
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'maintenance_entry' => ['nullable', 'array'],
            'maintenance_entry.*' => ['string'],
            'whitelist_entries' => ['nullable', 'array'],
            'whitelist_entries.*' => ['string'],
        ]);

        $maintenanceEntry = $validated['maintenance_entry'][0] ?? null;
        $whitelistEntries = $validated['whitelist_entries'] ?? [];

        $config = app(MaintenanceModeConfig::class);
        $config->setMaintenanceEntry($maintenanceEntry);
        $config->setWhitelistEntries($whitelistEntries);
        $config->save();

        Toast::success(__('Settings saved'));

        return response()->json(['success' => true]);
    }

    public function activate()
    {
        $options = config('statamic.maintenance-mode.down_options', []);
        $args = [];

        if (! empty($options['retry'])) {
            $args['--retry'] = $options['retry'];
        }

        if (! empty($options['secret'])) {
            if ($options['secret'] === true) {
                $args['--with-secret'] = true;
            } else {
                $args['--secret'] = $options['secret'];
            }
        }

        if (! empty($options['refresh'])) {
            $args['--refresh'] = $options['refresh'];
        }

        Artisan::call('down', $args);

        Toast::success(__('Maintenance mode activated'));

        return response()->json([
            'success' => true,
            'isActive' => true,
        ]);
    }

    public function deactivate()
    {
        Artisan::call('up');

        Toast::success(__('Maintenance mode deactivated'));

        return response()->json([
            'success' => true,
            'isActive' => false,
        ]);
    }
}
