<?php

declare(strict_types=1);

use ElSchneider\StatamicMaintenanceMode\MaintenanceModeConfig;
use Illuminate\Cookie\CookieValuePrefix;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;

beforeEach(function () {
    // Ensure we're not in maintenance mode
    Artisan::call('up');

    // Create a pages collection for testing
    Collection::make('pages')->routes('/{slug}')->save();
});

afterEach(function () {
    // Clean up maintenance mode
    Artisan::call('up');

    // Clean up config file
    $configPath = base_path('content/maintenance-mode.yaml');
    if (File::exists($configPath)) {
        File::delete($configPath);
    }
});

it('returns 503 when maintenance mode is active', function () {
    Artisan::call('down');

    $response = $this->get('/some-page');
    $response->assertStatus(503);
});

it('returns 200 for CP routes during maintenance', function () {
    Artisan::call('down');

    $cpRoute = config('statamic.cp.route', 'cp');
    $response = $this->get('/'.$cpRoute);

    // CP should redirect to login, not 503
    expect($response->status())->not->toBe(503);
});

it('allows super users to bypass maintenance mode', function () {
    $entry = Entry::make()
        ->collection('pages')
        ->slug('test-page')
        ->data(['title' => 'Test Page']);
    $entry->save();

    Artisan::call('down');

    $user = makeSuperUser();

    // The middleware reads session cookies directly (before session middleware runs),
    // so we need to set up a real session cookie rather than using actingAs()
    $guardName = config('statamic.users.guards.cp', 'web');
    $userIdKey = 'login_'.$guardName.'_'.sha1(Illuminate\Auth\SessionGuard::class);

    // Use file session driver and create session with user ID
    config(['session.driver' => 'file']);
    $sessionId = Str::random(40);
    $session = $this->app['session']->driver();
    $session->setId($sessionId);
    $session->put($userIdKey, $user->id());
    $session->save();

    // Create encrypted session cookie value with Laravel's prefix
    $cookieName = config('session.cookie', 'laravel_session');
    $prefixedValue = CookieValuePrefix::create($cookieName, $this->app['encrypter']->getKey()).$sessionId;
    $encryptedValue = $this->app['encrypter']->encrypt($prefixedValue, false);

    $response = $this->call('GET', '/test-page', [], [$cookieName => $encryptedValue]);
    expect($response->status())->toBe(200);
});

it('allows whitelisted pages during maintenance', function () {
    $entry = Entry::make()
        ->collection('pages')
        ->slug('privacy')
        ->data(['title' => 'Privacy Policy']);
    $entry->save();

    $config = app(MaintenanceModeConfig::class);
    $config->setWhitelistEntries([$entry->id()]);
    $config->save();

    // Clear the singleton so fresh config is loaded
    $this->app->forgetInstance(MaintenanceModeConfig::class);

    Artisan::call('down');

    $response = $this->get('/privacy');
    expect($response->status())->toBe(200);
});

it('renders Laravel default 503 template when no entry configured', function () {
    Artisan::call('down');

    $response = $this->get('/non-existent');
    $response->assertStatus(503);
    $response->assertSee('Service Unavailable');
});

it('saves configuration via CP endpoint', function () {
    $user = makeSuperUser();

    $response = $this->actingAs($user)->post(cp_route('utilities.maintenance-mode.store'), [
        'maintenance_entry' => ['entry-123'],
        'whitelist_entries' => ['entry-456', 'entry-789'],
    ]);

    $response->assertJson(['success' => true]);

    // Reload the config
    $this->app->forgetInstance(MaintenanceModeConfig::class);

    $config = app(MaintenanceModeConfig::class);
    expect($config->maintenanceEntryId())->toBe('entry-123');
    expect($config->whitelistEntryIds())->toBe(['entry-456', 'entry-789']);
});

it('activates maintenance mode via CP endpoint', function () {
    $user = makeSuperUser();

    $response = $this->actingAs($user)->post(cp_route('utilities.maintenance-mode.activate'));

    $response->assertJson(['success' => true, 'isActive' => true]);
    expect(app()->isDownForMaintenance())->toBeTrue();
});

it('deactivates maintenance mode via CP endpoint', function () {
    Artisan::call('down');

    $user = makeSuperUser();

    $response = $this->actingAs($user)->post(cp_route('utilities.maintenance-mode.deactivate'));

    $response->assertJson(['success' => true, 'isActive' => false]);
    expect(app()->isDownForMaintenance())->toBeFalse();
});
