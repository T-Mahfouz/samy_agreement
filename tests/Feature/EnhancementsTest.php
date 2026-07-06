<?php

use App\Models\ClientProfile;
use App\Models\Contract;
use App\Models\Offer;
use App\Models\Payment;
use App\Models\ProviderDocument;
use App\Models\ProviderProfile;
use App\Models\Tender;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

function party(): array
{
    $cu = User::factory()->create(['role' => 'client']);
    $client = ClientProfile::create(['user_id' => $cu->id, 'company_name' => 'عميل']);
    $pu = User::factory()->create(['role' => 'provider']);
    $provider = ProviderProfile::create(['user_id' => $pu->id, 'company_name' => 'مورد', 'status' => 'approved']);
    $tender = Tender::create(['client_id' => $client->id, 'tender_no' => 'E'.uniqid(), 'serial_no' => 'ES'.uniqid(), 'name' => 'منافسة', 'type' => 'general', 'status' => 'awarded', 'brochure_price' => 500, 'commission_rate' => 1, 'contract_duration_months' => 36]);
    $offer = Offer::create(['tender_id' => $tender->id, 'provider_id' => $provider->id, 'financial_value' => 9000, 'is_awarded' => true, 'status' => 'awarded']);
    $contract = Contract::create(['tender_id' => $tender->id, 'offer_id' => $offer->id, 'client_id' => $client->id, 'provider_id' => $provider->id, 'contract_value' => 9000, 'contract_duration_months' => 36, 'status' => 'awaiting_signature']);

    return compact('cu', 'client', 'pu', 'provider', 'tender', 'offer', 'contract');
}

it('both parties sign a contract and it becomes active', function () {
    ['cu' => $cu, 'pu' => $pu, 'contract' => $contract] = party();

    $this->actingAs($cu)->get("/contract/{$contract->id}")->assertOk();
    $this->actingAs($cu)->post("/contract/{$contract->id}/sign")->assertRedirect();
    expect($contract->fresh()->client_signed_at)->not->toBeNull();
    expect($contract->fresh()->status)->toBe('awaiting_signature');

    $this->actingAs($pu)->post("/contract/{$contract->id}/sign")->assertRedirect();
    $contract->refresh();
    expect($contract->provider_signed_at)->not->toBeNull();
    expect($contract->status)->toBe('active');
});

it('blocks a stranger from viewing a contract', function () {
    ['contract' => $contract] = party();
    $stranger = User::factory()->create(['role' => 'client']);
    $this->actingAs($stranger)->get("/contract/{$contract->id}")->assertForbidden();
});

it('client approves a brochure-fee request and notifies the provider', function () {
    ['cu' => $cu, 'pu' => $pu, 'tender' => $tender, 'provider' => $provider] = party();
    $pay = Payment::create(['type' => 'brochure_fee', 'tender_id' => $tender->id, 'provider_id' => $provider->id, 'paid_to' => 'client', 'amount' => 500, 'status' => 'pending']);

    $this->actingAs($cu)->get("/client/tenders/{$tender->id}/brochure-requests")->assertOk();
    $this->actingAs($cu)->put("/client/tenders/{$tender->id}/brochure-requests", ['decisions' => [$pay->id => 'paid']])->assertRedirect();
    expect($pay->fresh()->status)->toBe('paid');

    expect(\App\Models\Notification::where('user_id', $pu->id)->where('is_read', false)->count())->toBeGreaterThan(0);
});

it('gates brochure download on a paid payment', function () {
    Storage::fake('public');
    ['pu' => $pu, 'tender' => $tender, 'provider' => $provider] = party();
    Storage::disk('public')->put('brochures/x.pdf', 'data');
    $tender->update(['brochure_file' => 'brochures/x.pdf']);

    $this->actingAs($pu)->get("/provider/tenders/{$tender->id}/brochure/download")->assertForbidden();

    Payment::create(['type' => 'brochure_fee', 'tender_id' => $tender->id, 'provider_id' => $provider->id, 'paid_to' => 'client', 'amount' => 500, 'status' => 'paid']);
    $this->actingAs($pu)->get("/provider/tenders/{$tender->id}/brochure/download")->assertOk();
    $this->actingAs($pu)->get('/provider/booklets')->assertOk();
});

it('lets a client edit their tender', function () {
    ['cu' => $cu, 'tender' => $tender] = party();

    $this->actingAs($cu)->get("/client/tenders/{$tender->id}/edit")->assertOk();
    $this->actingAs($cu)->put("/client/tenders/{$tender->id}", [
        'type' => 'limited', 'name' => 'اسم معدّل',
        'offers_deadline' => now()->addDays(10)->toDateString(),
    ])->assertRedirect('/client/dashboard');
    expect($tender->fresh()->name)->toBe('اسم معدّل');
    expect($tender->fresh()->type)->toBe('limited');
});

it('serves a payment receipt to the owning provider but blocks another provider', function () {
    Storage::fake('public');
    ['pu' => $pu, 'tender' => $tender, 'provider' => $provider] = party();
    Storage::disk('public')->put('receipts/brochure/r.png', 'img');
    $pay = Payment::create([
        'type' => 'brochure_fee', 'tender_id' => $tender->id, 'provider_id' => $provider->id,
        'paid_to' => 'client', 'amount' => 500, 'status' => 'pending', 'receipt_file' => 'receipts/brochure/r.png',
    ]);

    $this->actingAs($pu)->get("/payments/{$pay->id}/receipt")->assertOk();

    $other = User::factory()->create(['role' => 'provider']);
    ProviderProfile::create(['user_id' => $other->id, 'company_name' => 'مورد آخر', 'status' => 'approved']);
    $this->actingAs($other)->get("/payments/{$pay->id}/receipt")->assertForbidden();
});

it('serves a provider document to admin but blocks a stranger', function () {
    Storage::fake('public');
    ['provider' => $provider] = party();
    Storage::disk('public')->put('provider-docs/cr.pdf', 'pdf');
    $doc = ProviderDocument::create([
        'provider_id' => $provider->id, 'doc_type' => 'commercial_register',
        'file_path' => 'provider-docs/cr.pdf', 'uploaded_at' => now(),
    ]);

    $admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($admin)->get("/provider-documents/{$doc->id}/download")->assertOk();

    $stranger = User::factory()->create(['role' => 'client']);
    ClientProfile::create(['user_id' => $stranger->id, 'company_name' => 'غريب']);
    $this->actingAs($stranger)->get("/provider-documents/{$doc->id}/download")->assertForbidden();
});

it('lets a client edit every field like registration (incl. username/email/password)', function () {
    ['cu' => $cu] = party();

    $this->actingAs($cu)->get('/client/profile')->assertOk();
    $this->actingAs($cu)->put('/client/profile', [
        'company_name' => 'عميل جديد',
        'mobile' => '0500000000',
        'bank_name' => 'الأهلي',
        'bank_beneficiary_name' => 'عميل جديد',
        'bank_iban' => 'SA44 2000 0001 2345 6789 1234',
        'username' => 'client_new',
        'email' => 'client_new@test.com',
        'password' => 'newpass123',
        'password_confirmation' => 'newpass123',
    ])->assertRedirect();

    $cu->refresh();
    expect($cu->clientProfile->fresh()->company_name)->toBe('عميل جديد');
    expect($cu->clientProfile->fresh()->bank_iban)->toBe('SA4420000001234567891234');
    expect($cu->name)->toBe('عميل جديد');
    expect($cu->username)->toBe('client_new');
    expect($cu->email)->toBe('client_new@test.com');
    expect(Hash::check('newpass123', $cu->password))->toBeTrue();
});

it('lets a provider edit every field like registration and replace a document', function () {
    Storage::fake('public');
    ['pu' => $pu, 'provider' => $provider] = party();

    $this->actingAs($pu)->get('/provider/profile')->assertOk();
    $this->actingAs($pu)->put('/provider/profile', [
        'company_name' => 'مورد جديد',
        'commercial_register_no' => '1234567890',
        'cr_type' => 'مؤسسة',
        'mobile' => '0511111111',
        'email' => $pu->email,
        'username' => 'provider_new',
        'attach_cr' => UploadedFile::fake()->create('cr.pdf', 100, 'application/pdf'),
    ])->assertRedirect();

    $pu->refresh();
    expect($provider->fresh()->company_name)->toBe('مورد جديد');
    expect($provider->fresh()->commercial_register_no)->toBe('1234567890');
    expect($pu->username)->toBe('provider_new');
    expect($provider->documents()->where('doc_type', 'commercial_register')->count())->toBe(1);
});

it('rejects a profile email that belongs to another user', function () {
    ['cu' => $cu] = party();
    $other = User::factory()->create(['role' => 'client', 'email' => 'taken@test.com']);

    $this->actingAs($cu)->put('/client/profile', [
        'company_name' => 'عميل',
        'email' => 'taken@test.com',
    ])->assertSessionHasErrors('email');
});
