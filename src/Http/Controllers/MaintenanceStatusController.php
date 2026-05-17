<?php

declare(strict_types=1);

namespace ElSchneider\StatamicMaintenanceMode\Http\Controllers;

use ElSchneider\StatamicMaintenanceMode\Permissions as MaintenancePermissions;
use Illuminate\Foundation\Http\MaintenanceModeBypassCookie;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Statamic\Facades\User;

class MaintenanceStatusController
{
    public function __invoke(Request $request): JsonResponse
    {
        // Not in maintenance mode - never show
        if (! app()->isDownForMaintenance()) {
            return response()->json(['show' => false]);
        }

        $canBypass = $this->isAuthenticatedCpUser() || $this->hasValidBypassCookie($request);

        return response()->json(['show' => $canBypass]);
    }

    protected function isAuthenticatedCpUser(): bool
    {
        $guardName = config('statamic.users.guards.cp', 'web');
        $authUser = Auth::guard($guardName)->user();

        if (! $authUser) {
            return false;
        }

        $user = User::find($authUser->getAuthIdentifier());

        return $user && ($user->isSuper() || $user->hasPermission(MaintenancePermissions::BYPASS_MAINTENANCE_MODE));
    }

    protected function hasValidBypassCookie(Request $request): bool
    {
        $data = app()->maintenanceMode()->data();

        if (! isset($data['secret'])) {
            return false;
        }

        $cookie = $request->cookie('laravel_maintenance');

        if (! $cookie) {
            return false;
        }

        return MaintenanceModeBypassCookie::isValid($cookie, $data['secret']);
    }
}
