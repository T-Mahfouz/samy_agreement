<?php

use App\Models\ClientProfile;
use App\Models\Offer;
use App\Models\Payment;
use App\Models\ProviderProfile;
use App\Models\Tender;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

function b4Tender(ClientProfile $client, array $o = []): Tender
{
    return Tender::create(array_merge([
        'client_id' => $client->id, 'tender_no' => 'B4'.uniqid(), 'serial_no' => 'BS'.uniqid(),
        'name' => 'منافسة', 'type' => 'general', 'status' => 'active',
    ], $o));
}

// ── #7 Cancelling a tender hard-deletes it and its related records ───
it('hard-deletes a tender and its related records when the client cancels', function () {
    Storage::fake('local');
    Storage::fake('public');

    $cu = User::factory()->create(['role' => 'client']);
    $client = ClientProfile::create(['user_id' => $cu->id, 'company_name' => 'عميل']);
    $tender = b4Tender($client);
    $pu = User::factory()->create(['role' => 'provider']);
    $prov = ProviderProfile::create(['user_id' => $pu->id, 'company_name' => 'مورد', 'status' => 'approved']);
    $offer = Offer::create(['tender_id' => $tender->id, 'provider_id' => $prov->id, 'financial_value' => 100, 'status' => 'submitted', 'technical_check' => 'pending']);
    Payment::create(['type' => 'brochure_fee', 'tender_id' => $tender->id, 'provider_id' => $prov->id, 'paid_to' => 'client', 'amount' => 0, 'status' => 'paid']);

    $this->actingAs($cu)->put("/client/tenders/{$tender->id}/cancel")->assertRedirect();

    expect(Tender::find($tender->id))->toBeNull();
    expect(Offer::find($offer->id))->toBeNull();
    expect(Payment::where('tender_id', $tender->id)->count())->toBe(0);
    expect(User::find($pu->id))->not->toBeNull(); // provider account survives
});

it('forbids a client from cancelling another client\'s tender', function () {
    $cu = User::factory()->create(['role' => 'client']);
    $client = ClientProfile::create(['user_id' => $cu->id, 'company_name' => 'عميل']);
    $tender = b4Tender($client);
    $other = User::factory()->create(['role' => 'client']);
    ClientProfile::create(['user_id' => $other->id, 'company_name' => 'آخر']);

    $this->actingAs($other)->put("/client/tenders/{$tender->id}/cancel")->assertForbidden();
    expect(Tender::find($tender->id))->not->toBeNull();
});

// ── #16 Admin hard-deletes provider / client accounts ───────────────
it('lets an admin hard-delete a provider account and its offers', function () {
    Storage::fake('local');
    Storage::fake('public');

    $admin = User::factory()->create(['role' => 'admin']);
    $pu = User::factory()->create(['role' => 'provider']);
    $prov = ProviderProfile::create(['user_id' => $pu->id, 'company_name' => 'مورد', 'status' => 'approved']);
    $cu = User::factory()->create(['role' => 'client']);
    $client = ClientProfile::create(['user_id' => $cu->id, 'company_name' => 'عميل']);
    $tender = b4Tender($client);
    $offer = Offer::create(['tender_id' => $tender->id, 'provider_id' => $prov->id, 'financial_value' => 100, 'status' => 'submitted', 'technical_check' => 'pending']);

    $this->actingAs($admin)->delete("/admin/providers/{$prov->id}")->assertRedirect('/admin/providers');

    expect(ProviderProfile::find($prov->id))->toBeNull();
    expect(User::find($pu->id))->toBeNull();      // account gone -> cannot log in
    expect(Offer::find($offer->id))->toBeNull();
    expect(Tender::find($tender->id))->not->toBeNull(); // the client's tender survives
});

it('lets an admin hard-delete a client account and cascade its tenders', function () {
    Storage::fake('local');
    Storage::fake('public');

    $admin = User::factory()->create(['role' => 'admin']);
    $cu = User::factory()->create(['role' => 'client']);
    $client = ClientProfile::create(['user_id' => $cu->id, 'company_name' => 'عميل']);
    $tender = b4Tender($client);

    $this->actingAs($admin)->delete("/admin/clients/{$client->id}")->assertRedirect('/admin/clients');

    expect(ClientProfile::find($client->id))->toBeNull();
    expect(User::find($cu->id))->toBeNull();
    expect(Tender::find($tender->id))->toBeNull();
});

it('forbids a non-admin from deleting a provider account', function () {
    $cu = User::factory()->create(['role' => 'client']);
    $pu = User::factory()->create(['role' => 'provider']);
    $prov = ProviderProfile::create(['user_id' => $pu->id, 'company_name' => 'مورد', 'status' => 'approved']);

    $this->actingAs($cu)->delete("/admin/providers/{$prov->id}")->assertForbidden();
    expect(ProviderProfile::find($prov->id))->not->toBeNull();
});
