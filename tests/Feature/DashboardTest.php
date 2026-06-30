<?php

use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('guests are redirected to the login page', function () {
    $response = $this->get('/dashboard');
    $response->assertRedirect('/login');
});

test('authenticated users are redirected from /dashboard to their role dashboard', function () {
    $user = User::factory()->create(['role' => 'client']);
    $this->actingAs($user);

    $response = $this->get('/dashboard');
    $response->assertRedirect('/client/dashboard');
});