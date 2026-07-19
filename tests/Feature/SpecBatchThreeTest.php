<?php

use App\Models\ClientProfile;
use App\Models\ContactMessage;
use App\Models\Payment;
use App\Models\ProviderProfile;
use App\Models\Tender;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

// ── #11 Contact form validation (per-field errors) ──────────────────
it('returns per-field errors when the contact form is incomplete or invalid', function () {
    $this->post('/contact', ['full_name' => '', 'email' => 'not-an-email', 'message' => ''])
        ->assertSessionHasErrors(['full_name', 'message', 'email']);
});

it('accepts a valid contact message', function () {
    $this->post('/contact', ['full_name' => 'زائر', 'message' => 'مرحبا'])
        ->assertRedirect()->assertSessionHas('success');

    expect(ContactMessage::where('full_name', 'زائر')->exists())->toBeTrue();
});

// ── #2 Sub-activity removed (search still works, param ignored) ──────
it('renders the tenders search after the sub-activity filter was removed', function () {
    $this->get('/?subcategory_id=999')->assertOk();
});

// ── #17 Payments split into two type tabs with totals ───────────────
it('separates admin payments into brochure and commission tabs with per-tab totals', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $cu = User::factory()->create(['role' => 'client']);
    $client = ClientProfile::create(['user_id' => $cu->id, 'company_name' => 'عميل']);
    $pu = User::factory()->create(['role' => 'provider']);
    $prov = ProviderProfile::create(['user_id' => $pu->id, 'company_name' => 'مورد', 'status' => 'approved']);

    $t1 = Tender::create(['client_id' => $client->id, 'tender_no' => 'PT1', 'serial_no' => 'PS1', 'name' => 'م1', 'type' => 'general', 'status' => 'active']);
    $t2 = Tender::create(['client_id' => $client->id, 'tender_no' => 'PT2', 'serial_no' => 'PS2', 'name' => 'م2', 'type' => 'general', 'status' => 'active']);

    Payment::create(['type' => 'brochure_fee', 'tender_id' => $t1->id, 'provider_id' => $prov->id, 'paid_to' => 'client', 'amount' => 500, 'status' => 'paid']);
    Payment::create(['type' => 'brochure_fee', 'tender_id' => $t2->id, 'provider_id' => $prov->id, 'paid_to' => 'client', 'amount' => 300, 'status' => 'pending']);
    Payment::create(['type' => 'commission', 'tender_id' => $t1->id, 'provider_id' => $prov->id, 'paid_to' => 'platform', 'amount' => 1000, 'status' => 'paid']);

    $this->actingAs($admin)->get('/admin/payments')->assertInertia(fn (Assert $p) => $p
        ->where('filters.type', 'brochure_fee')
        ->where('totalAmount', 800)
        ->where('typeCounts.brochure_fee', 2)
        ->where('typeCounts.commission', 1)
        ->has('payments.data', 2));

    $this->actingAs($admin)->get('/admin/payments?type=commission')->assertInertia(fn (Assert $p) => $p
        ->where('filters.type', 'commission')
        ->where('totalAmount', 1000)
        ->has('payments.data', 1));
});
