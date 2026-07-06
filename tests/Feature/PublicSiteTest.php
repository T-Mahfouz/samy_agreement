<?php

use App\Models\ClientProfile;
use App\Models\Tender;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

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
    $this->get('/?name=للاختبار')->assertOk();
});

it('searches tenders by the separate reference / number / name fields', function () {
    $match = publishedTender(['name' => 'إنشاء مبنى إداري', 'reference_no' => 'REF-777', 'tender_no' => 'TN-100']);
    $other = publishedTender(['name' => 'صيانة طرق', 'reference_no' => 'REF-888', 'tender_no' => 'TN-200']);

    $this->get('/?name='.urlencode('مبنى'))->assertInertia(fn (Assert $p) => $p
        ->has('tenders.data', 1)->where('tenders.data.0.id', $match->id));

    $this->get('/?reference_no=REF-888')->assertInertia(fn (Assert $p) => $p
        ->has('tenders.data', 1)->where('tenders.data.0.id', $other->id));

    $this->get('/?tender_no=TN-100')->assertInertia(fn (Assert $p) => $p
        ->has('tenders.data', 1)->where('tenders.data.0.id', $match->id));
});

it('filters tenders by the offers-deadline range', function () {
    $soon = publishedTender(['offers_deadline' => '2026-08-01']);
    publishedTender(['offers_deadline' => '2026-12-01']);

    $this->get('/?deadline_from=2026-07-01&deadline_to=2026-09-01')->assertInertia(fn (Assert $p) => $p
        ->has('tenders.data', 1)->where('tenders.data.0.id', $soon->id));
});

it('filters tenders by publish-date recency', function () {
    $recent = publishedTender(['published_at' => now()->subDay()]);
    publishedTender(['published_at' => now()->subMonths(2)]);

    $this->get('/?published=week')->assertInertia(fn (Assert $p) => $p
        ->has('tenders.data', 1)->where('tenders.data.0.id', $recent->id));
});

it('orders tenders by the selected sort option', function () {
    $early = publishedTender(['offers_deadline' => '2026-08-01']);
    $late = publishedTender(['offers_deadline' => '2026-12-01']);

    $this->get('/?sort=deadline_asc')->assertInertia(fn (Assert $p) => $p->where('tenders.data.0.id', $early->id));
    $this->get('/?sort=deadline_desc')->assertInertia(fn (Assert $p) => $p->where('tenders.data.0.id', $late->id));
});

it('treats LIKE wildcards in search as literal text', function () {
    $literal = publishedTender(['reference_no' => 'REF_50']);
    publishedTender(['reference_no' => 'REFX50']);

    $this->get('/?reference_no='.urlencode('REF_50'))->assertInertia(fn (Assert $p) => $p
        ->has('tenders.data', 1)->where('tenders.data.0.id', $literal->id));
});

it('opens a published tender details page', function () {
    $tender = publishedTender();

    $this->get("/tenders/{$tender->id}")->assertOk();
});

it('hides unpublished tenders from the public', function () {
    $tender = publishedTender(['published_at' => null]);

    $this->get("/tenders/{$tender->id}")->assertNotFound();
});
