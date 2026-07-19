<?php

use App\Models\ClientProfile;
use App\Models\Offer;
use App\Models\ProviderProfile;
use App\Models\Tender;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

// ── #3 Attachment preview (inline) respecting authorization ─────────
it('previews an offer file inline for an authorized user, downloads by default, and blocks others', function () {
    Storage::fake('local');

    $cu = User::factory()->create(['role' => 'client']);
    $client = ClientProfile::create(['user_id' => $cu->id, 'company_name' => 'عميل']);
    $tender = Tender::create(['client_id' => $client->id, 'tender_no' => 'P1', 'serial_no' => 'PS1', 'name' => 'م', 'type' => 'general', 'status' => 'active']);
    $pu = User::factory()->create(['role' => 'provider']);
    $prov = ProviderProfile::create(['user_id' => $pu->id, 'company_name' => 'مورد', 'status' => 'approved']);

    Storage::disk('local')->put("offers/{$tender->id}/tech.pdf", '%PDF-1.4 fake');
    $offer = Offer::create([
        'tender_id' => $tender->id, 'provider_id' => $prov->id,
        'technical_file' => "offers/{$tender->id}/tech.pdf",
        'financial_value' => 100, 'status' => 'submitted', 'technical_check' => 'pending',
    ]);

    // inline preview for the owning provider
    $preview = $this->actingAs($pu)->get("/offers/{$offer->id}/files/technical?inline=1");
    $preview->assertOk();
    expect($preview->headers->get('content-disposition'))->toContain('inline');

    // default = download (attachment)
    $download = $this->actingAs($pu)->get("/offers/{$offer->id}/files/technical");
    $download->assertOk();
    expect($download->headers->get('content-disposition'))->toContain('attachment');

    // authorization still enforced on the inline route
    $other = User::factory()->create(['role' => 'provider']);
    ProviderProfile::create(['user_id' => $other->id, 'company_name' => 'آخر', 'status' => 'approved']);
    $this->actingAs($other)->get("/offers/{$offer->id}/files/technical?inline=1")->assertForbidden();
});
