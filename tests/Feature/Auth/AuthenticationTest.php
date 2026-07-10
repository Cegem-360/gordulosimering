<?php

declare(strict_types=1);

use App\Models\User;
use Tests\TestCase;

test('login screen can be rendered', function (): void {
    /** @var TestCase $this */
    $response = $this->get('/login');

    $response->assertStatus(200);
});

test('users can authenticate using the login screen', function (): void {
    /** @var TestCase $this */
    $user = User::factory()->create();

    $response = $this
        ->withSession(['_token' => 'test-token'])
        ->post('/login', [
            '_token' => 'test-token',
            'email' => $user->email,
            'password' => 'password',
        ]);

    $response->assertRedirect(route('index', absolute: false));
    $this->assertAuthenticatedAs($user);
});

test('users can not authenticate with invalid password', function (): void {
    /** @var TestCase $this */
    $user = User::factory()->create();

    $response = $this
        ->withSession(['_token' => 'test-token'])
        ->post('/login', [
            '_token' => 'test-token',
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});

test('users can logout', function (): void {
    /** @var TestCase $this */
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->withSession(['_token' => 'test-token'])
        ->post('/logout', ['_token' => 'test-token']);

    $this->assertGuest();
    $response->assertRedirect('/');
});
