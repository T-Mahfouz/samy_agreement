<?php

use App\Models\Category;
use App\Models\ClientProfile;
use App\Models\Payment;
use App\Models\ProviderProfile;
use App\Models\Tender;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function b1Tender(array $overrides = []): Tender
{
    $u = User::factory()->create(['role' => 'client']);
    $client = ClientProfile::create(['user_id' => $u->id, 'company_name' => 'مستفيد']);

    return Tender::create(array_merge([
        'client_id' => $client->id,
        'tender_no' => 'B1-'.uniqid(), 'serial_no' => 'BS-'.uniqid(), 'reference_no' => 'BR-'.uniqid(),
        'name' => 'منافسة', 'type' => 'general', 'status' => 'active',
        'brochure_price' => 0, 'commission_rate' => 1, 'published_at' => now(),
        'offers_deadline' => now()->addDays(5)->toDateString(),
    ], $overrides));
}

// ── #1 Mobile mandatory ─────────────────────────────────────────────
it('rejects client registration without a mobile number', function () {
    $this->post('/register', [
        'role' => 'client', 'facility_name' => 'شركة',
        'email' => 'nomobile-client@test.com',
        'password' => 'password123', 'password_confirmation' => 'password123',
    ])->assertSessionHasErrors('mobile');
});

it('rejects provider registration without a mobile number', function () {
    Storage::fake('public');
    $this->post('/register', [
        'role' => 'provider', 'facility_name' => 'شركة',
        'email' => 'nomobile-prov@test.com',
        'password' => 'password123', 'password_confirmation' => 'password123',
        'attach_cr' => UploadedFile::fake()->create('cr.pdf', 100, 'application/pdf'),
    ])->assertSessionHasErrors('mobile');
});

it('rejects a client profile update without a mobile number', function () {
    $u = User::factory()->create(['role' => 'client']);
    ClientProfile::create(['user_id' => $u->id, 'company_name' => 'مستفيد']);

    $this->actingAs($u)->put('/client/profile', [
        'company_name' => 'مستفيد', 'email' => $u->email,
    ])->assertSessionHasErrors('mobile');
});

it('rejects a provider profile update without a mobile number', function () {
    $u = User::factory()->create(['role' => 'provider']);
    ProviderProfile::create(['user_id' => $u->id, 'company_name' => 'مورد', 'status' => 'approved']);

    $this->actingAs($u)->put('/provider/profile', [
        'company_name' => 'مورد', 'email' => $u->email,
    ])->assertSessionHasErrors('mobile');
});

// ── #6 Tender editing removed ───────────────────────────────────────
it('no longer exposes tender edit or update routes', function () {
    $tender = b1Tender();
    $u = $tender->client->user;

    $this->actingAs($u)->get("/client/tenders/{$tender->id}/edit")->assertNotFound();
    $this->actingAs($u)->put("/client/tenders/{$tender->id}", ['name' => 'x'])->assertNotFound();
});

// ── #8 Offer inspection gated by opening datetime ───────────────────
it('blocks inspecting offers before the opening datetime and allows after', function () {
    $tender = b1Tender(['offers_open' => now()->addDays(3)->toDateString(), 'offers_open_time' => '10:00:00']);
    $u = $tender->client->user;

    $this->actingAs($u)->get("/client/tenders/{$tender->id}/offers")->assertForbidden();

    $tender->update(['offers_open' => now()->subDay()->toDateString(), 'offers_open_time' => '10:00:00']);
    $this->actingAs($u)->get("/client/tenders/{$tender->id}/offers")->assertOk();
});

// ── #9 Must obtain tender document before submitting an offer ────────
it('blocks offer submission until the tender document is obtained', function () {
    Storage::fake('local');
    Storage::fake('public');

    $cat = Category::create(['name' => 'قطاع', 'is_active' => true]);
    $tender = b1Tender(['category_id' => $cat->id]);

    $pu = User::factory()->create(['role' => 'provider', 'status' => 'active']);
    $provider = ProviderProfile::create(['user_id' => $pu->id, 'company_name' => 'مورد', 'status' => 'approved', 'main_category_id' => $cat->id]);

    $payload = fn () => [
        'technical_file' => UploadedFile::fake()->create('t.pdf', 100, 'application/pdf'),
        'financial_file' => UploadedFile::fake()->create('f.pdf', 100, 'application/pdf'),
        'financial_value' => 1000, 'declaration_accepted' => true,
    ];

    $this->actingAs($pu)->post("/provider/tenders/{$tender->id}/offer", $payload())->assertRedirect();
    expect($tender->offers()->count())->toBe(0);

    Payment::create(['type' => 'brochure_fee', 'tender_id' => $tender->id, 'provider_id' => $provider->id, 'paid_to' => 'client', 'amount' => 0, 'status' => 'paid']);

    $this->actingAs($pu)->post("/provider/tenders/{$tender->id}/offer", $payload())->assertRedirect();
    expect($tender->offers()->count())->toBe(1);
});
