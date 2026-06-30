<?php

use App\Models\ClientProfile;
use App\Models\Contract;
use App\Models\Offer;
use App\Models\Payment;
use App\Models\ProviderProfile;
use App\Models\Tender;
use App\Models\User;
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

    // المورّد يصله إشعار بالاعتماد
    expect(\App\Models\Notification::where('user_id', $pu->id)->where('is_read', false)->count())->toBeGreaterThan(0);
});

it('gates brochure download on a paid payment', function () {
    Storage::fake('public');
    ['pu' => $pu, 'tender' => $tender, 'provider' => $provider] = party();
    Storage::disk('public')->put('brochures/x.pdf', 'data');
    $tender->update(['brochure_file' => 'brochures/x.pdf']);

    // no payment yet -> forbidden
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
    ])->assertRedirect('/client/dashboard');
    expect($tender->fresh()->name)->toBe('اسم معدّل');
    expect($tender->fresh()->type)->toBe('limited');
});

it('lets client and provider update their profiles', function () {
    ['cu' => $cu, 'pu' => $pu] = party();

    $this->actingAs($cu)->get('/client/profile')->assertOk();
    $this->actingAs($cu)->put('/client/profile', ['company_name' => 'عميل جديد', 'mobile' => '0500'])->assertRedirect();
    expect($cu->clientProfile->fresh()->company_name)->toBe('عميل جديد');

    $this->actingAs($pu)->get('/provider/profile')->assertOk();
    $this->actingAs($pu)->put('/provider/profile', ['company_name' => 'مورد جديد'])->assertRedirect();
    expect($pu->providerProfile->fresh()->company_name)->toBe('مورد جديد');
});
