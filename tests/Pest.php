<?php

declare(strict_types=1);

pest()->extend(Tests\TestCase::class)->in('Feature');

function makeSuperUser()
{
    return Statamic\Facades\User::make()
        ->makeSuper()
        ->email('super@example.com')
        ->save();
}

function makeUserWithPermission(string $permission, string $email)
{
    $role = Statamic\Facades\Role::make(str_replace('@', '_', $email))
        ->permissions([$permission])
        ->save();

    return Statamic\Facades\User::make()
        ->email($email)
        ->assignRole($role)
        ->save();
}

function authenticatedSessionCookie($user): array
{
    $app = app();
    $guardName = config('statamic.users.guards.cp', 'web');
    $userIdKey = 'login_'.$guardName.'_'.sha1(Illuminate\Auth\SessionGuard::class);

    $sessionId = Illuminate\Support\Str::random(40);
    $session = $app['session']->driver();
    $session->setId($sessionId);
    $session->start();
    $session->flush();
    $session->put($userIdKey, $user->id());
    $session->save();

    $cookieName = config('session.cookie', 'laravel_session');
    $prefixedValue = Illuminate\Cookie\CookieValuePrefix::create($cookieName, $app['encrypter']->getKey()).$sessionId;
    $encryptedValue = $app['encrypter']->encrypt($prefixedValue, false);

    return [$cookieName => $encryptedValue];
}
