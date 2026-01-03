<?php

declare(strict_types=1);

namespace ElSchneider\StatamicMaintenanceMode\Http\Controllers;

use ElSchneider\StatamicMaintenanceMode\MaintenanceModeConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Statamic\Facades\Blueprint;
use Statamic\Facades\CP\Toast;
use Statamic\Http\Controllers\CP\CpController;

class MaintenanceModeController extends CpController
{
    public function index()
    {
        $config = app(MaintenanceModeConfig::class);

        // Get all available collections
        $collections = \Statamic\Facades\Collection::handles()->all();

        // Only include entries fields if collections exist
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

        $blueprint = Blueprint::makeFromFields($blueprintFields);

        // Wrap single entry in array for entries fieldtype, filter nulls
        $maintenanceEntryValue = $config->maintenanceEntryId() ? [$config->maintenanceEntryId()] : [];
        $whitelistValue = $config->whitelistEntryIds();

        $fields = $blueprint->fields()->addValues([
            'maintenance_entry' => $maintenanceEntryValue,
            'whitelist_entries' => $whitelistValue,
        ])->preProcess();

        return view('statamic-maintenance-mode::cp.utility', [
            'title' => __('Maintenance Mode'),
            'isActive' => app()->isDownForMaintenance(),
            'maintenanceEntry' => $config->maintenanceEntryId(),
            'whitelistEntries' => $config->whitelistEntryIds(),
            'blueprint' => $blueprint->toPublishArray(),
            'meta' => $fields->meta(),
            'values' => $fields->values(),
            'hasCollections' => ! empty($collections),
        ]);
    }

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
        Artisan::call('down', [
            '--retry' => 60,
        ]);

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
