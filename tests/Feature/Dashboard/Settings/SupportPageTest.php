<?php

declare(strict_types=1);

use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

it('redirects guests to the login page', function (): void {
    $response = $this->get(route('settings.support'));

    $response->assertRedirect(route('login'));
});

it('redirects unverified users to the verification notice', function (): void {
    $user = User::factory()->unverified()->create();

    $response = $this->actingAs($user)->get(route('settings.support'));

    $response->assertRedirect(route('verification.notice'));
});

it('renders the support settings page', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('settings.support'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('dashboard/settings/support')
        ->has('tickets.data')
    );
});

it('includes user tickets in the page props', function (): void {
    $user = User::factory()->create();
    SupportTicket::factory()->count(3)->for($user)->create();

    $response = $this->actingAs($user)->get(route('settings.support'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('dashboard/settings/support')
        ->has('tickets.data', 3)
    );
});

it('paginates support tickets with ten results per page', function (): void {
    $user = User::factory()->create();
    SupportTicket::factory()->count(11)->for($user)->create();

    $firstPage = $this->actingAs($user)->get(route('settings.support'));

    $firstPage->assertOk();
    $firstPage->assertInertia(fn ($page) => $page
        ->component('dashboard/settings/support')
        ->where('tickets.per_page', 10)
        ->where('tickets.current_page', 1)
        ->where('tickets.last_page', 2)
        ->has('tickets.data', 10)
    );

    $secondPage = $this->actingAs($user)->get(route('settings.support', ['page' => 2]));

    $secondPage->assertOk();
    $secondPage->assertInertia(fn ($page) => $page
        ->component('dashboard/settings/support')
        ->where('tickets.current_page', 2)
        ->has('tickets.data', 1)
    );
});

it('caches paginated support tickets for each page', function (): void {
    $user = User::factory()->create();

    SupportTicket::factory()->count(11)->for($user)->create();

    $this->actingAs($user)->get(route('settings.support'))->assertOk();
    $this->actingAs($user)->get(route('settings.support', ['page' => 2]))->assertOk();

    expect(Cache::has("user:{$user->id}:support-tickets:v0:page:1"))->toBeTrue();
    expect(Cache::has("user:{$user->id}:support-tickets:v0:page:2"))->toBeTrue();
});
