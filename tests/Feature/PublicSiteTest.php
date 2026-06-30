<?php

use App\Models\ClientProfile;
use App\Models\Tender;
use App\Models\User;

function publishedTender(array $overrides = []): Tender
{
    $user = User::factory()->create(['role' => 'client']);
    $client = ClientProfile::create(['user_id' => $user->id, 'company_name' => 'جهة']);

    return Tender::create(array_merge([
        'client_id' => $client->id,
        'tender_no' => 'PT-'.uniqid(),
        'serial_no' => 'PS-'.uniqid(),
        'name' => 'منافسة عامة للاختبار',
        'type' => 'general',
        'status' => 'active',
        'brochure_price' => 500,
        'published_at' => now(),
    ], $overrides));
}

it('shows the public tenders listing to guests', function () {
    publishedTender();

    $this->get('/')->assertOk();
    $this->get('/?status=active')->assertOk();
    $this->get('/?q=للاختبار')->assertOk();
});

it('opens a published tender details page', function () {
    $tender = publishedTender();

    $this->get("/tenders/{$tender->id}")->assertOk();
});

it('hides unpublished tenders from the public', function () {
    $tender = publishedTender(['published_at' => null]);

    $this->get("/tenders/{$tender->id}")->assertNotFound();
});
