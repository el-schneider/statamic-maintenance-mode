<?php

declare(strict_types=1);

pest()->extend(Tests\TestCase::class)->in('Feature');

function makeSuperUser()
{
    return \Statamic\Facades\User::make()
        ->makeSuper()
        ->email('super@example.com')
        ->save();
}

function makeUserWithPermission(string $permission)
{
    $role = \Statamic\Facades\Role::make('test_role')
        ->permissions([$permission])
        ->save();

    return \Statamic\Facades\User::make()
        ->email('user@example.com')
        ->assignRole($role)
        ->save();
}
