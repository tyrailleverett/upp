<?php

declare(strict_types=1);

use App\Models\User;

it('redirects guests with a pending two-factor challenge to the challenge route', function (): void {
    $user = User::factory()->create();

    enableTwoFactorForUser($user);

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ])
        ->assertRedirect('/two-factor-challenge')
        ->assertSessionHas('login.id', $user->getKey());

    $this->get(route('dashboard'))
        ->assertRedirect(route('two-factor.login'));
});
