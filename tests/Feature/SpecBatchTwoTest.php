<?php

use App\Models\User;

it('serves a dedicated admin login page', function () {
    $this->get('/admin/login')->assertOk();
});

it('redirects an admin to the admin login page after logout', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)->post('/logout')->assertRedirect('/admin/login');
    $this->assertGuest();
});

it('redirects a non-admin to the home page after logout', function () {
    $client = User::factory()->create(['role' => 'client']);

    $this->actingAs($client)->post('/logout')->assertRedirect('/');
});

it('sends an unauthenticated visitor of an admin page to the admin login', function () {
    $this->get('/admin/dashboard')->assertRedirect('/admin/login');
});
