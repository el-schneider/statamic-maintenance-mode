<?php

declare(strict_types=1);

namespace ElSchneider\StatamicMaintenanceMode\Http\Middleware;

use Closure;
use ElSchneider\StatamicMaintenanceMode\MaintenanceModeConfig;
use Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance as LaravelMiddleware;
use Statamic\Facades\User;
use Statamic\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class PreventRequestsDuringMaintenance extends LaravelMiddleware
{
    public function handle($request, Closure $next): Response
    {
        if (! $this->app->isDownForMaintenance()) {
            return $next($request);
        }

        // Statamic-specific bypasses checked before Laravel's handling
        if ($this->isCpRoute($request)) {
            return $next($request);
        }

        if ($this->isAuthenticatedCpUser()) {
            return $next($request);
        }

        if ($this->isWhitelistedPage($request)) {
            return $next($request);
        }

        // Let Laravel handle its features (except, secret URL, redirect, template)
        // When it would throw 503, we catch and render our custom page
        try {
            return parent::handle($request, $next);
        } catch (HttpException $e) {
            if ($e->getStatusCode() === 503) {
                return $this->maintenanceResponse($request);
            }

            throw $e;
        }
    }

    protected function isCpRoute($request): bool
    {
        $cpPath = config('statamic.cp.route', 'cp');
        $path = Str::start($request->path(), '/');

        return Str::startsWith($path, '/'.$cpPath);
    }

    protected function isAuthenticatedCpUser(): bool
    {
        $user = User::current();

        return $user && ($user->isSuper() || $user->hasPermission('access cp'));
    }

    protected function isWhitelistedPage($request): bool
    {
        $config = app(MaintenanceModeConfig::class);
        $whitelistUris = $config->whitelistUris();

        if (empty($whitelistUris)) {
            return false;
        }

        $path = Str::start($request->path(), '/');

        return in_array($path, $whitelistUris);
    }

    protected function maintenanceResponse($request): Response
    {
        $config = app(MaintenanceModeConfig::class);
        $entry = $config->maintenanceEntry();

        $data = $this->getDownData();

        if ($entry) {
            $content = $entry->toResponse($request)->getContent();

            return response($content, 503, $this->getHeaders($data));
        }

        $view = config('statamic.maintenance-mode.view', 'statamic-maintenance-mode::errors.503');

        return response(
            view($view)->render(),
            503,
            $this->getHeaders($data)
        );
    }

    protected function getDownData(): array
    {
        return $this->app->maintenanceMode()->data();
    }
}
