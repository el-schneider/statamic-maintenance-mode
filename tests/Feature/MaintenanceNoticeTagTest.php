<?php

declare(strict_types=1);

use ElSchneider\StatamicMaintenanceMode\Tags\MaintenanceNotice;
use Statamic\Tags\Loader;

beforeEach(function () {
    // Reset the static property before each test
    $reflection = new ReflectionClass(MaintenanceNotice::class);
    $property = $reflection->getProperty('scriptInjected');
    $property->setAccessible(true);
    $property->setValue(null, false);
});

it('returns empty string when frontend notice is disabled', function () {
    config(['statamic.maintenance-mode.show_frontend_notice' => false]);

    $output = renderTag('maintenance_notice');

    expect($output)->toBe('');
});

it('returns badge markup when frontend notice is enabled', function () {
    config(['statamic.maintenance-mode.show_frontend_notice' => true]);

    $output = renderTag('maintenance_notice');

    expect($output)->toContain('data-maintenance-notice');
    expect($output)->toContain('Maintenance Mode Active');
    expect($output)->toContain('display:none');
});

it('includes script that fetches status endpoint', function () {
    config(['statamic.maintenance-mode.show_frontend_notice' => true]);

    $output = renderTag('maintenance_notice');

    expect($output)->toContain('<script>');
    expect($output)->toContain('/!/statamic-maintenance-mode/status');
    expect($output)->toContain('credentials:\'include\'');
});

it('uses custom action prefix in script URL', function () {
    config(['statamic.maintenance-mode.show_frontend_notice' => true]);
    config(['statamic.routes.action' => '_actions']);

    $output = renderTag('maintenance_notice');

    expect($output)->toContain('/_actions/statamic-maintenance-mode/status');
    expect($output)->not->toContain('/!/statamic-maintenance-mode/status');
});

it('only injects script once even with multiple tags', function () {
    config(['statamic.maintenance-mode.show_frontend_notice' => true]);

    $output1 = renderTag('maintenance_notice');
    $output2 = renderTag('maintenance_notice');

    expect($output1)->toContain('<script>');
    expect($output2)->not->toContain('<script>');
});

it('renders custom content when used as pair tag', function () {
    config(['statamic.maintenance-mode.show_frontend_notice' => true]);

    $customContent = '<div class="custom">Custom Notice</div>';
    $output = renderTag('maintenance_notice', [], $customContent, true);

    expect($output)->toContain('data-maintenance-notice');
    expect($output)->toContain('Custom Notice');
    expect($output)->toContain('class="custom"');
});

function renderTag(string $name, array $params = [], ?string $content = null, bool $isPair = false): string
{
    $tag = app(Loader::class)->load($name, [
        'parser' => null,
        'params' => $params,
        'content' => $content ?? '',
        'context' => [],
    ]);

    $tag->isPair = $isPair;

    return (string) $tag->index();
}
