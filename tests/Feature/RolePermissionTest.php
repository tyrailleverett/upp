<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Role;

it('can assign a role to a user', function (): void {
    Role::create(['name' => 'super_admin']);
    $user = User::factory()->create();

    $user->assignRole('super_admin');

    expect($user->hasRole('super_admin'))->toBeTrue();
});

it('grants super_admin all abilities via Gate::before', function (): void {
    Role::create(['name' => 'super_admin']);
    $user = User::factory()->create();
    $user->assignRole('super_admin');

    Gate::define('do-anything', fn () => false);

    $this->actingAs($user);

    expect(Gate::allows('do-anything'))->toBeTrue();
});

it('does not grant non-super_admin users all abilities', function (): void {
    $user = User::factory()->create();

    Gate::define('do-anything', fn () => false);

    $this->actingAs($user);

    expect(Gate::allows('do-anything'))->toBeFalse();
});
