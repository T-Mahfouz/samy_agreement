<?php

use App\Models\Category;
use App\Models\ClientProfile;
use App\Models\Offer;
use App\Models\Payment;
use App\Models\ProviderProfile;
use App\Models\Tender;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

function providerUser(string $status = 'approved'): User
{
    $user = User::factory()->create(['role' => 'provider', 'status' => $status === 'approved' ? 'active' : 'pending']);
    ProviderProfile::create(['user_id' => $user->id, 'company_name' => 'مورد', 'status' => $status]);

    return $user;
}

function aTender(): Tender
{
    $u = User::factory()->create(['role' => 'client']);
    $c = ClientProfile::create(['user_id' => $u->id, 'company_name' => 'عميل']);

    return Tender::create([
        'client_id' => $c->id, 'tender_no' => 'T'.uniqid(), 'serial_no' => 'S'.uniqid(),
        'name' => 'منافسة', 'type' => 'general', 'status' => 'active', 'brochure_price' => 500, 'commission_rate' => 1,
    ]);
}

it('shows the provider dashboard', function () {
    $this->actingAs(providerUser())->get('/provider/dashboard')->assertOk();
});

it('blocks non-providers from the provider portal', function () {
    $client = User::factory()->create(['role' => 'client']);
    $this->actingAs($client)->get('/provider/dashboard')->assertForbidden();
});

it('lets an approved provider submit an offer', function () {
    Storage::fake('local');
    $user = providerUser('approved');
    $tender = aTender();

    $this->actingAs($user)->post("/provider/tenders/{$tender->id}/offer", [
        'technical_file' => UploadedFile::fake()->create('t.pdf', 50, 'application/pdf'),
        'financial_file' => UploadedFile::fake()->create('f.pdf', 50, 'application/pdf'),
        'financial_value' => 5000,
        'declaration_accepted' => true,
    ])->assertRedirect()->assertSessionHas('success');

    $offer = Offer::where('tender_id', $tender->id)->where('provider_id', $user->providerProfile->id)->first();
    expect($offer)->not->toBeNull();
    expect($offer->status)->toBe('submitted');
    expect($offer->technical_file)->not->toBeNull();
});

it('prevents a pending provider from submitting an offer', function () {
    Storage::fake('public');
    $user = providerUser('pending');
    $tender = aTender();

    $this->actingAs($user)->post("/provider/tenders/{$tender->id}/offer", [
        'technical_file' => UploadedFile::fake()->create('t.pdf', 50, 'application/pdf'),
        'financial_file' => UploadedFile::fake()->create('f.pdf', 50, 'application/pdf'),
        'financial_value' => 5000,
        'declaration_accepted' => true,
    ]);

    expect(Offer::where('tender_id', $tender->id)->count())->toBe(0);
});

it('prevents submitting two offers on the same tender', function () {
    Storage::fake('public');
    $user = providerUser('approved');
    $tender = aTender();
    Offer::create(['tender_id' => $tender->id, 'provider_id' => $user->providerProfile->id, 'status' => 'submitted']);

    $this->actingAs($user)->post("/provider/tenders/{$tender->id}/offer", [
        'technical_file' => UploadedFile::fake()->create('t.pdf', 50, 'application/pdf'),
        'financial_file' => UploadedFile::fake()->create('f.pdf', 50, 'application/pdf'),
        'financial_value' => 5000,
        'declaration_accepted' => true,
    ]);

    expect(Offer::where('tender_id', $tender->id)->count())->toBe(1);
});

it('lets a provider upload a brochure-fee receipt', function () {
    Storage::fake('public');
    $user = providerUser('approved');
    $tender = aTender();

    $this->actingAs($user)->post("/provider/tenders/{$tender->id}/brochure-payment", [
        'receipt_file' => UploadedFile::fake()->create('r.pdf', 50, 'application/pdf'),
    ])->assertRedirect();

    $pay = Payment::where('type', 'brochure_fee')->where('tender_id', $tender->id)->first();
    expect($pay)->not->toBeNull();
    expect($pay->status)->toBe('pending');
    expect((float) $pay->amount)->toBe(500.0);
});

it('blocks a brochure-fee request after the deadline has passed', function () {
    Storage::fake('public');
    $user = providerUser('approved');
    $tender = aTender();
    $tender->update(['offers_deadline' => now()->subDay()->format('Y-m-d'), 'offers_deadline_time' => '12:00:00']);

    $this->actingAs($user)->post("/provider/tenders/{$tender->id}/brochure-payment", [
        'receipt_file' => UploadedFile::fake()->create('r.pdf', 50, 'application/pdf'),
    ])->assertSessionHas('error');

    expect(Payment::where('type', 'brochure_fee')->where('tender_id', $tender->id)->count())->toBe(0);
});

it('lets a provider upload a commission receipt for an awarded offer', function () {
    Storage::fake('public');
    $user = providerUser('approved');
    $tender = aTender();
    $offer = Offer::create([
        'tender_id' => $tender->id, 'provider_id' => $user->providerProfile->id,
        'financial_value' => 10000, 'is_awarded' => true, 'status' => 'awarded',
    ]);

    $this->actingAs($user)->post("/provider/offers/{$offer->id}/commission-payment", [
        'receipt_file' => UploadedFile::fake()->create('r.pdf', 50, 'application/pdf'),
    ])->assertRedirect();

    $pay = Payment::where('type', 'commission')->where('offer_id', $offer->id)->first();
    expect($pay)->not->toBeNull();
    expect((float) $pay->amount)->toBe(100.0);
});

it('prevents submitting an offer after the deadline has passed', function () {
    Storage::fake('local');
    $user = providerUser('approved');
    $tender = aTender();
    $tender->update(['offers_deadline' => now()->subDay()->format('Y-m-d'), 'offers_deadline_time' => '12:00:00']);

    $this->actingAs($user)->post("/provider/tenders/{$tender->id}/offer", [
        'technical_file' => UploadedFile::fake()->create('t.pdf', 50, 'application/pdf'),
        'financial_file' => UploadedFile::fake()->create('f.pdf', 50, 'application/pdf'),
        'financial_value' => 5000,
        'declaration_accepted' => true,
    ])->assertSessionHas('error');

    expect(Offer::where('tender_id', $tender->id)->count())->toBe(0);
});

it('lets the owning provider download their offer file but blocks other providers', function () {
    Storage::fake('local');
    $user = providerUser('approved');
    $tender = aTender();
    $path = "offers/{$tender->id}/t.pdf";
    Storage::disk('local')->put($path, '%PDF-1.4 demo');
    $offer = Offer::create([
        'tender_id' => $tender->id, 'provider_id' => $user->providerProfile->id,
        'technical_file' => $path, 'status' => 'submitted',
    ]);

    $this->actingAs($user)->get("/offers/{$offer->id}/files/technical")->assertOk();

    $other = providerUser('approved');
    $this->actingAs($other)->get("/offers/{$offer->id}/files/technical")->assertForbidden();
});

it('defaults to the first category and lets a provider browse any category', function () {
    $catA = Category::create(['name' => 'إنشاءات', 'is_active' => true, 'sort_order' => 1]);
    $catB = Category::create(['name' => 'تقنية المعلومات', 'is_active' => true, 'sort_order' => 2]);

    $client = ClientProfile::create(['user_id' => User::factory()->create(['role' => 'client'])->id, 'company_name' => 'عميل']);
    $mk = fn (string $name, int $cat) => Tender::create([
        'client_id' => $client->id, 'tender_no' => 'T'.uniqid(), 'serial_no' => 'S'.uniqid(),
        'name' => $name, 'type' => 'general', 'status' => 'active', 'brochure_price' => 0,
        'commission_rate' => 1, 'category_id' => $cat, 'published_at' => now(),
    ]);
    $mk('منافسة إنشاءات', $catA->id);
    $mk('منافسة تقنية', $catB->id);

    $user = User::factory()->create(['role' => 'provider', 'status' => 'active']);
    ProviderProfile::create(['user_id' => $user->id, 'company_name' => 'مورد', 'status' => 'approved', 'main_category_id' => $catA->id]);

    $this->actingAs($user)->get('/')->assertInertia(fn (Assert $page) => $page
        ->component('public/Tenders/Index')
        ->where('filters.category_id', $catA->id)
        ->has('tenders.data', 1)
        ->where('tenders.data.0.name', 'منافسة إنشاءات')
    );

    $this->actingAs($user)->get('/?category_id='.$catB->id)->assertInertia(fn (Assert $page) => $page
        ->has('tenders.data', 1)
        ->where('tenders.data.0.name', 'منافسة تقنية')
    );
});
