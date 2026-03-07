<?php

declare(strict_types=1);

it('has a manifest with required PWA fields and icons', function (): void {
    $manifestPath = public_path('manifest.json');

    expect($manifestPath)->toBeFile();

    $manifest = json_decode(file_get_contents($manifestPath), true);

    expect($manifest)
        ->toHaveKeys(['name', 'short_name', 'start_url', 'display', 'theme_color', 'background_color', 'icons']);

    $iconSizes = collect($manifest['icons'])->pluck('sizes')->all();

    expect($iconSizes)->toContain('192x192')
        ->and($iconSizes)->toContain('512x512');
});

it('has a service worker file with a fetch listener', function (): void {
    $swPath = public_path('sw.js');

    expect($swPath)->toBeFile();

    $contents = file_get_contents($swPath);

    expect($contents)->toContain('addEventListener("fetch"');
});

it('includes manifest link and theme-color meta tag in HTML', function (): void {
    $user = App\Models\User::factory()->create();

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertOk()
        ->assertSee('rel="manifest"', false)
        ->assertSee('href="/manifest.json"', false)
        ->assertSee('name="theme-color"', false)
        ->assertSee('content="#1b1b1f"', false);
});
