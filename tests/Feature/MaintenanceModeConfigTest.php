<?php

declare(strict_types=1);

use ElSchneider\StatamicMaintenanceMode\MaintenanceModeConfig;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    // Clean up config file before each test
    $configPath = base_path('content/maintenance-mode.yaml');
    if (File::exists($configPath)) {
        File::delete($configPath);
    }
});

afterEach(function () {
    // Clean up config file after each test
    $configPath = base_path('content/maintenance-mode.yaml');
    if (File::exists($configPath)) {
        File::delete($configPath);
    }
});

it('can be instantiated', function () {
    $config = new MaintenanceModeConfig;
    expect($config)->toBeInstanceOf(MaintenanceModeConfig::class);
});

it('returns null for maintenance entry when not set', function () {
    $config = new MaintenanceModeConfig;
    expect($config->maintenanceEntryId())->toBeNull();
});

it('returns empty array for whitelist entries when not set', function () {
    $config = new MaintenanceModeConfig;
    expect($config->whitelistEntryIds())->toBe([]);
});

it('can set and retrieve maintenance entry', function () {
    $config = new MaintenanceModeConfig;
    $config->setMaintenanceEntry('entry-123');
    $config->save();

    // Create new instance to verify persistence
    $freshConfig = new MaintenanceModeConfig;
    expect($freshConfig->maintenanceEntryId())->toBe('entry-123');
});

it('can set and retrieve whitelist entries', function () {
    $config = new MaintenanceModeConfig;
    $config->setWhitelistEntries(['entry-456', 'entry-789']);
    $config->save();

    // Create new instance to verify persistence
    $freshConfig = new MaintenanceModeConfig;
    expect($freshConfig->whitelistEntryIds())->toBe(['entry-456', 'entry-789']);
});

it('saves config to yaml file', function () {
    $config = new MaintenanceModeConfig;
    $config->setMaintenanceEntry('entry-123');
    $config->save();

    expect(File::exists(base_path('content/maintenance-mode.yaml')))->toBeTrue();
});

it('can get and set arbitrary keys', function () {
    $config = new MaintenanceModeConfig;
    $config->set('custom.nested.key', 'value');
    expect($config->get('custom.nested.key'))->toBe('value');
});
