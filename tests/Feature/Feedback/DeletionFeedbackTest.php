<?php

declare(strict_types=1);

use App\Enums\DeletionReason;
use Illuminate\Support\Facades\URL;

it('shows the feedback form when using a valid signed url', function (): void {
    $url = URL::temporarySignedRoute(
        'feedback.account-deletion.create',
        now()->addDays(7),
        ['email' => 'user@example.com'],
    );

    $response = $this->get($url);

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('feedback/account-deletion')
        ->has('email')
        ->where('email', 'user@example.com')
    );
});

it('returns 403 for unsigned url', function (): void {
    $response = $this->get(route('feedback.account-deletion.create', ['email' => 'user@example.com']));

    $response->assertForbidden();
});

it('returns 403 for expired signed url', function (): void {
    $url = URL::temporarySignedRoute(
        'feedback.account-deletion.create',
        now()->subMinute(),
        ['email' => 'user@example.com'],
    );

    $response = $this->get($url);

    $response->assertForbidden();
});

it('stores feedback and redirects on valid submission', function (): void {
    $response = $this->post(route('feedback.account-deletion.store'), [
        'email' => 'user@example.com',
        'reason' => DeletionReason::TooExpensive->value,
        'comment' => 'It was too pricey for my budget.',
    ]);

    $response->assertRedirect(route('login'));
    $this->assertDatabaseHas('account_deletion_feedback', [
        'email' => 'user@example.com',
        'reason' => DeletionReason::TooExpensive->value,
        'comment' => 'It was too pricey for my budget.',
    ]);
});

it('stores feedback without a comment', function (): void {
    $response = $this->post(route('feedback.account-deletion.store'), [
        'email' => 'user@example.com',
        'reason' => DeletionReason::NotUseful->value,
    ]);

    $response->assertRedirect(route('login'));
    $this->assertDatabaseHas('account_deletion_feedback', [
        'email' => 'user@example.com',
        'reason' => DeletionReason::NotUseful->value,
        'comment' => null,
    ]);
});

it('fails validation when required fields are missing', function (): void {
    $response = $this->postJson(route('feedback.account-deletion.store'), []);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['email', 'reason']);
});

it('fails validation with an invalid reason', function (): void {
    $response = $this->postJson(route('feedback.account-deletion.store'), [
        'email' => 'user@example.com',
        'reason' => 'invalid_reason',
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['reason']);
});
