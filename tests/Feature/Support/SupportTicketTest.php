<?php

declare(strict_types=1);

use App\Enums\TicketTopic;
use App\Mail\SupportTicketReceivedMail;
use App\Mail\SupportTicketSubmittedMail;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

use function Pest\Laravel\assertDatabaseHas;

it('can submit a support ticket', function (): void {
    Mail::fake();

    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson(route('support.tickets.store'), [
        'title' => 'Cannot access dashboard',
        'description' => 'I keep getting a 500 error when trying to access the dashboard.',
        'topic' => TicketTopic::Technical->value,
    ]);

    $response->assertRedirect();
    assertDatabaseHas('support_tickets', [
        'user_id' => $user->id,
        'title' => 'Cannot access dashboard',
        'description' => 'I keep getting a 500 error when trying to access the dashboard.',
        'topic' => TicketTopic::Technical->value,
    ]);
});

it('sends a confirmation email to the user on submission', function (): void {
    Mail::fake();

    $user = User::factory()->create();

    $this->actingAs($user)->postJson(route('support.tickets.store'), [
        'title' => 'Account question',
        'description' => 'I have a question about my account.',
        'topic' => TicketTopic::Account->value,
    ]);

    Mail::assertQueued(SupportTicketSubmittedMail::class, function (SupportTicketSubmittedMail $mail) use ($user): bool {
        return $mail->hasTo($user->email);
    });
});

it('sends a notification email to the admin on submission', function (): void {
    Mail::fake();
    config(['support.admin_email' => 'admin@example.com']);

    $user = User::factory()->create();

    $this->actingAs($user)->postJson(route('support.tickets.store'), [
        'title' => 'General inquiry',
        'description' => 'I would like some information.',
        'topic' => TicketTopic::General->value,
    ]);

    Mail::assertQueued(SupportTicketReceivedMail::class, function (SupportTicketReceivedMail $mail): bool {
        return $mail->hasTo('admin@example.com');
    });
});

it('validates required fields', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson(route('support.tickets.store'), []);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['title', 'description', 'topic']);
});

it('rejects an invalid topic', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson(route('support.tickets.store'), [
        'title' => 'Test ticket',
        'description' => 'Test description.',
        'topic' => 'invalid_topic',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['topic']);
});

it('shows only the authenticated user tickets', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    SupportTicket::factory()->for($user)->create(['title' => 'My ticket']);
    SupportTicket::factory()->for($otherUser)->create(['title' => 'Other ticket']);

    $response = $this->actingAs($user)->getJson(route('support.tickets.index'));

    $response->assertOk();
    $response->assertJsonCount(1);
    $response->assertJsonFragment(['title' => 'My ticket']);
    $response->assertJsonMissing(['title' => 'Other ticket']);
});

it('prevents guests from accessing support tickets', function (): void {
    $response = $this->getJson(route('support.tickets.index'));

    $response->assertUnauthorized();
});

it('prevents guests from submitting support tickets', function (): void {
    $response = $this->postJson(route('support.tickets.store'), [
        'title' => 'Test',
        'description' => 'Test',
        'topic' => TicketTopic::General->value,
    ]);

    $response->assertUnauthorized();
});

it('prevents unverified users from submitting support tickets', function (): void {
    $user = User::factory()->unverified()->create();

    $response = $this->actingAs($user)->postJson(route('support.tickets.store'), [
        'title' => 'Test ticket',
        'description' => 'Test description.',
        'topic' => TicketTopic::General->value,
    ]);

    $response->assertForbidden();
});

it('prevents unverified users from listing support tickets', function (): void {
    $user = User::factory()->unverified()->create();

    $response = $this->actingAs($user)->getJson(route('support.tickets.index'));

    $response->assertForbidden();
});

it('returns tickets in descending date order', function (): void {
    $user = User::factory()->create();

    $older = SupportTicket::factory()->for($user)->create([
        'title' => 'Older ticket',
        'created_at' => now()->subDays(2),
    ]);

    $newer = SupportTicket::factory()->for($user)->create([
        'title' => 'Newer ticket',
        'created_at' => now()->subDay(),
    ]);

    $response = $this->actingAs($user)->getJson(route('support.tickets.index'));

    $response->assertOk();
    $titles = array_column($response->json(), 'title');
    expect($titles)->toBe(['Newer ticket', 'Older ticket']);
});

it('includes resolution when present', function (): void {
    $user = User::factory()->create();

    SupportTicket::factory()->for($user)->resolved()->create();

    $response = $this->actingAs($user)->getJson(route('support.tickets.index'));

    $response->assertOk();
    expect($response->json('0.resolution'))->not->toBeNull();
});

it('caches support tickets for the authenticated user', function (): void {
    $user = User::factory()->create();

    SupportTicket::factory()->for($user)->create(['title' => 'Cached ticket']);

    $this->actingAs($user)->getJson(route('support.tickets.index'))->assertOk();

    expect(Cache::has("user:{$user->id}:support-tickets"))->toBeTrue();
});

it('invalidates the ticket cache when a new ticket is created', function (): void {
    Mail::fake();

    $user = User::factory()->create();

    SupportTicket::factory()->for($user)->create(['title' => 'Existing ticket']);

    $this->actingAs($user)->getJson(route('support.tickets.index'))->assertOk();

    expect(Cache::has("user:{$user->id}:support-tickets"))->toBeTrue();

    $this->actingAs($user)->postJson(route('support.tickets.store'), [
        'title' => 'New ticket',
        'description' => 'This should bust the cache.',
        'topic' => TicketTopic::General->value,
    ])->assertRedirect();

    expect(Cache::has("user:{$user->id}:support-tickets"))->toBeFalse();
});

it('serves cached tickets on subsequent requests', function (): void {
    $user = User::factory()->create();

    SupportTicket::factory()->for($user)->create(['title' => 'First ticket']);

    $this->actingAs($user)->getJson(route('support.tickets.index'))->assertOk();

    SupportTicket::factory()->for($user)->create(['title' => 'Sneaky ticket']);

    $response = $this->actingAs($user)->getJson(route('support.tickets.index'));

    $response->assertOk();
    $response->assertJsonCount(1);
    $response->assertJsonMissing(['title' => 'Sneaky ticket']);
});
