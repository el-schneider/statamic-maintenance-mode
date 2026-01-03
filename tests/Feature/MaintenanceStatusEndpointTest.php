<?php

declare(strict_types=1);

use Illuminate\Foundation\Http\MaintenanceModeBypassCookie;
use Illuminate\Support\Facades\Artisan;

beforeEach(function () {
    Artisan::call('up');
});

afterEach(function () {
    Artisan::call('up');
});

it('returns show false when not in maintenance mode', function () {
    $response = $this->get('/!/statamic-maintenance-mode/status');

    $response->assertStatus(200);
    $response->assertJson(['show' => false]);
});

it('returns show false for unauthenticated users during maintenance', function () {
    Artisan::call('down');

    $response = $this->get('/!/statamic-maintenance-mode/status');

    $response->assertStatus(200);
    $response->assertJson(['show' => false]);
});

it('returns show true for authenticated super users during maintenance', function () {
    Artisan::call('down');

    $user = makeSuperUser();

    $response = $this->actingAs($user)->get('/!/statamic-maintenance-mode/status');

    $response->assertStatus(200);
    $response->assertJson(['show' => true]);
});

it('returns show true for users with CP access during maintenance', function () {
    Artisan::call('down');

    $user = makeUserWithPermission('access cp');

    $response = $this->actingAs($user)->get('/!/statamic-maintenance-mode/status');

    $response->assertStatus(200);
    $response->assertJson(['show' => true]);
});

it('returns show false for users without CP access during maintenance', function () {
    Artisan::call('down');

    $user = makeUserWithPermission('view entries');

    $response = $this->actingAs($user)->get('/!/statamic-maintenance-mode/status');

    $response->assertStatus(200);
    $response->assertJson(['show' => false]);
});

it('returns show true when user has valid bypass cookie during maintenance', function () {
    Artisan::call('down', ['--secret' => 'test-secret-123']);

    $bypassCookie = MaintenanceModeBypassCookie::create('test-secret-123');

    $response = $this->withCookie('laravel_maintenance', $bypassCookie->getValue())->get('/!/statamic-maintenance-mode/status');

    $response->assertStatus(200);
    $response->assertJson(['show' => true]);
});

it('uses configured CP guard for authentication', function () {
    config(['statamic.users.guards.cp' => 'web']);

    Artisan::call('down');

    $user = makeSuperUser();

    $response = $this->actingAs($user, 'web')->get('/!/statamic-maintenance-mode/status');

    $response->assertStatus(200);
    $response->assertJson(['show' => true]);
});
