<?php

declare(strict_types=1);

namespace ElSchneider\StatamicMaintenanceMode\Http\Middleware;

use Closure;
use ElSchneider\StatamicMaintenanceMode\MaintenanceModeConfig;
use Illuminate\Cookie\CookieValuePrefix;
use Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance as LaravelMiddleware;
use Statamic\Facades\User;
use Statamic\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

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

        if ($this->isMaintenanceStatusRoute($request)) {
            return $next($request);
        }

        if ($this->isAuthenticatedCpUser($request)) {
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

    protected function isMaintenanceStatusRoute($request): bool
    {
        $actionPrefix = mb_trim(config('statamic.routes.action', '!/'), '/');
        $expectedPath = $actionPrefix.'/statamic-maintenance-mode/status';

        return $request->path() === $expectedPath;
    }

    protected function isAuthenticatedCpUser($request): bool
    {
        // Session may not be started yet in global middleware
        // We need to manually bootstrap the session from the cookie
        try {
            $sessionName = config('session.cookie', 'laravel_session');
            $encryptedSessionId = $request->cookies->get($sessionName);

            if (! $encryptedSessionId) {
                return false;
            }

            // Decrypt the session ID (cookies are encrypted by default)
            $decryptedValue = $this->app['encrypter']->decrypt($encryptedSessionId, false);

            // Laravel prefixes cookie values with a 40-char HMAC + pipe
            $sessionId = CookieValuePrefix::remove($decryptedValue);

            // Start session with the cookie's session ID
            $session = $this->app['session']->driver();
            $session->setId($sessionId);
            $session->start();

            // Check for user ID in session directly
            $guardName = config('statamic.users.guards.cp', 'web');
            $userIdKey = 'login_'.$guardName.'_'.sha1(\Illuminate\Auth\SessionGuard::class);
            $userId = $session->get($userIdKey);

            if (! $userId) {
                return false;
            }

            // Find the user and check permissions
            $user = User::find($userId);

            return $user && ($user->isSuper() || $user->hasPermission('access cp'));
        } catch (Throwable) {
            return false;
        }
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

        // Let Laravel's exception handler render its default 503 view
        throw new HttpException(503, 'Service Unavailable', null, $this->getHeaders($data));
    }

    protected function getDownData(): array
    {
        return $this->app->maintenanceMode()->data();
    }
}
