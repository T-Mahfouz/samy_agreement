<?php

use App\Models\Category;
use App\Models\ClientProfile;
use App\Models\ContactMessage;
use App\Models\ContentPage;
use App\Models\Contract;
use App\Models\Faq;
use App\Models\Offer;
use App\Models\Payment;
use App\Models\ProviderProfile;
use App\Models\Region;
use App\Models\Tender;
use App\Models\TenderInquiry;
use App\Models\User;

function makeTender(): Tender
{
    $clientUser = User::factory()->create(['role' => 'client']);
    $client = ClientProfile::create(['user_id' => $clientUser->id, 'company_name' => 'عميل']);

    return Tender::create([
        'client_id' => $client->id,
        'tender_no' => 'T-'.uniqid(),
        'serial_no' => 'S-'.uniqid(),
        'name' => 'منافسة',
        'type' => 'general',
        'status' => 'active',
    ]);
}

function admin(): User
{
    return User::factory()->create(['role' => 'admin', 'status' => 'active']);
}

it('blocks non-admins from the admin dashboard', function () {
    $client = User::factory()->create(['role' => 'client']);

    $this->actingAs($client)->get('/admin/dashboard')->assertForbidden();
});

it('loads admin index pages for admins', function () {
    $user = admin();

    $this->actingAs($user)->get('/admin/dashboard')->assertOk();
    $this->actingAs($user)->get('/admin/categories')->assertOk();
    $this->actingAs($user)->get('/admin/locations')->assertOk();
    $this->actingAs($user)->get('/admin/faqs')->assertOk();
    $this->actingAs($user)->get('/admin/settings')->assertOk();
});

it('creates, updates and deletes a category', function () {
    $user = admin();

    $this->actingAs($user)->post('/admin/categories', [
        'name' => 'قطاع اختبار', 'parent_id' => null, 'is_active' => true, 'sort_order' => 1,
    ])->assertRedirect();
    $cat = Category::where('name', 'قطاع اختبار')->firstOrFail();

    $this->actingAs($user)->put("/admin/categories/{$cat->id}", [
        'name' => 'قطاع معدّل', 'parent_id' => null, 'is_active' => false, 'sort_order' => 2,
    ])->assertRedirect();
    expect($cat->fresh()->name)->toBe('قطاع معدّل');

    $this->actingAs($user)->delete("/admin/categories/{$cat->id}")->assertRedirect();
    expect(Category::find($cat->id))->toBeNull();
});

it('validates required fields', function () {
    $user = admin();

    $this->actingAs($user)->post('/admin/faqs', ['question' => '', 'answer' => ''])
        ->assertSessionHasErrors(['question', 'answer']);
});

it('creates a region and a city under it', function () {
    $user = admin();

    $this->actingAs($user)->post('/admin/regions', ['name' => 'منطقة اختبار', 'is_active' => true])->assertRedirect();
    $region = Region::where('name', 'منطقة اختبار')->firstOrFail();

    $this->actingAs($user)->post('/admin/cities', [
        'region_id' => $region->id, 'name' => 'مدينة اختبار', 'is_active' => true,
    ])->assertRedirect();

    expect($region->cities()->count())->toBe(1);
});

it('lists messages and changes status then deletes', function () {
    $user = admin();
    $msg = ContactMessage::create([
        'full_name' => 'زائر', 'email' => 'v@x.com', 'message' => 'مرحبا', 'status' => 'new',
    ]);

    $this->actingAs($user)->get('/admin/messages')->assertOk();

    $this->actingAs($user)->put("/admin/messages/{$msg->id}", ['status' => 'replied'])->assertRedirect();
    expect($msg->fresh()->status)->toBe('replied');

    $this->actingAs($user)->put("/admin/messages/{$msg->id}", ['status' => 'invalid'])
        ->assertSessionHasErrors('status');

    $this->actingAs($user)->delete("/admin/messages/{$msg->id}")->assertRedirect();
    expect(ContactMessage::find($msg->id))->toBeNull();
});

it('does crud on content pages and enforces unique slug+section', function () {
    $user = admin();

    $this->actingAs($user)->get('/admin/content')->assertOk();

    $this->actingAs($user)->post('/admin/content', [
        'slug' => 'about', 'section_key' => 'intro', 'title' => 'مقدمة', 'body' => 'نص',
    ])->assertRedirect();
    $page = ContentPage::where('slug', 'about')->where('section_key', 'intro')->firstOrFail();

    // duplicate slug+section should fail
    $this->actingAs($user)->post('/admin/content', [
        'slug' => 'about', 'section_key' => 'intro', 'title' => 'x', 'body' => 'y',
    ])->assertSessionHasErrors('section_key');

    $this->actingAs($user)->put("/admin/content/{$page->id}", [
        'slug' => 'about', 'section_key' => 'intro', 'title' => 'مقدمة معدّلة', 'body' => 'نص',
    ])->assertRedirect();
    expect($page->fresh()->title)->toBe('مقدمة معدّلة');

    $this->actingAs($user)->delete("/admin/content/{$page->id}")->assertRedirect();
    expect(ContentPage::find($page->id))->toBeNull();
});

it('rejects a malformed slug on content pages', function () {
    $this->actingAs(admin())->post('/admin/content', [
        'slug' => 'About Us!', 'section_key' => 'intro', 'title' => 'x', 'body' => 'y',
    ])->assertSessionHasErrors('slug');
});

it('lists and shows providers, and approving syncs the user account', function () {
    $admin = admin();

    $providerUser = User::factory()->create(['role' => 'provider', 'status' => 'pending']);
    $provider = ProviderProfile::create([
        'user_id' => $providerUser->id,
        'company_name' => 'شركة مورد',
        'status' => 'pending',
    ]);

    $this->actingAs($admin)->get('/admin/providers')->assertOk();
    $this->actingAs($admin)->get('/admin/providers?status=pending')->assertOk();
    $this->actingAs($admin)->get("/admin/providers/{$provider->id}")->assertOk();

    // approve
    $this->actingAs($admin)->put("/admin/providers/{$provider->id}", ['status' => 'approved'])->assertRedirect();
    expect($provider->fresh()->status)->toBe('approved');
    expect($providerUser->fresh()->status)->toBe('active');

    // reject suspends the account
    $this->actingAs($admin)->put("/admin/providers/{$provider->id}", ['status' => 'rejected'])->assertRedirect();
    expect($providerUser->fresh()->status)->toBe('suspended');

    // invalid status rejected by validation
    $this->actingAs($admin)->put("/admin/providers/{$provider->id}", ['status' => 'bogus'])
        ->assertSessionHasErrors('status');
});

it('reviews a payment: approve records reviewer and timestamp', function () {
    $admin = admin();

    $clientUser = User::factory()->create(['role' => 'client']);
    $client = ClientProfile::create(['user_id' => $clientUser->id, 'company_name' => 'عميل']);
    $providerUser = User::factory()->create(['role' => 'provider']);
    $provider = ProviderProfile::create(['user_id' => $providerUser->id, 'company_name' => 'مورد', 'status' => 'approved']);

    $tender = Tender::create([
        'client_id' => $client->id,
        'tender_no' => 'T-1001',
        'serial_no' => 'S-1001',
        'name' => 'منافسة اختبار',
        'type' => 'general',
        'status' => 'active',
    ]);

    $payment = Payment::create([
        'type' => 'brochure_fee',
        'tender_id' => $tender->id,
        'provider_id' => $provider->id,
        'paid_to' => 'client',
        'amount' => 500,
        'status' => 'pending',
    ]);

    $this->actingAs($admin)->get('/admin/payments')->assertOk();
    $this->actingAs($admin)->get('/admin/payments?status=pending&type=brochure_fee')->assertOk();

    $this->actingAs($admin)->put("/admin/payments/{$payment->id}", ['status' => 'paid'])->assertRedirect();
    $fresh = $payment->fresh();
    expect($fresh->status)->toBe('paid');
    expect($fresh->reviewed_by)->toBe($admin->id);
    expect($fresh->reviewed_at)->not->toBeNull();

    // returning to pending clears the review metadata
    $this->actingAs($admin)->put("/admin/payments/{$payment->id}", ['status' => 'pending'])->assertRedirect();
    expect($payment->fresh()->reviewed_by)->toBeNull();
});

it('lists and shows tenders, and can cancel one', function () {
    $admin = admin();
    $tender = makeTender();

    $this->actingAs($admin)->get('/admin/tenders')->assertOk();
    $this->actingAs($admin)->get('/admin/tenders?status=active')->assertOk();
    $this->actingAs($admin)->get("/admin/tenders/{$tender->id}")->assertOk();

    $this->actingAs($admin)->put("/admin/tenders/{$tender->id}", ['status' => 'cancelled'])->assertRedirect();
    expect($tender->fresh()->status)->toBe('cancelled');

    $this->actingAs($admin)->put("/admin/tenders/{$tender->id}", ['status' => 'nope'])->assertSessionHasErrors('status');
});

it('lists offers', function () {
    $admin = admin();
    $tender = makeTender();
    $providerUser = User::factory()->create(['role' => 'provider']);
    $provider = ProviderProfile::create(['user_id' => $providerUser->id, 'company_name' => 'مورد', 'status' => 'approved']);

    Offer::create([
        'tender_id' => $tender->id,
        'provider_id' => $provider->id,
        'financial_value' => 5000,
        'technical_check' => 'matching',
        'status' => 'submitted',
    ]);

    $this->actingAs($admin)->get('/admin/offers')->assertOk();
    $this->actingAs($admin)->get('/admin/offers?status=submitted')->assertOk();
});

it('lists, shows and updates a contract', function () {
    $admin = admin();
    $tender = makeTender();
    $providerUser = User::factory()->create(['role' => 'provider']);
    $provider = ProviderProfile::create(['user_id' => $providerUser->id, 'company_name' => 'مورد', 'status' => 'approved']);
    $offer = Offer::create(['tender_id' => $tender->id, 'provider_id' => $provider->id, 'financial_value' => 5000, 'status' => 'awarded']);

    $contract = Contract::create([
        'tender_id' => $tender->id,
        'offer_id' => $offer->id,
        'client_id' => $tender->client_id,
        'provider_id' => $provider->id,
        'contract_value' => 5000,
        'status' => 'active',
    ]);

    $this->actingAs($admin)->get('/admin/contracts')->assertOk();
    $this->actingAs($admin)->get("/admin/contracts/{$contract->id}")->assertOk();

    $this->actingAs($admin)->put("/admin/contracts/{$contract->id}", ['status' => 'completed'])->assertRedirect();
    expect($contract->fresh()->status)->toBe('completed');
});

it('lists, shows and suspends a client', function () {
    $admin = admin();
    $tender = makeTender();
    $client = ClientProfile::find($tender->client_id);

    $this->actingAs($admin)->get('/admin/clients')->assertOk();
    $this->actingAs($admin)->get("/admin/clients/{$client->id}")->assertOk();

    $this->actingAs($admin)->put("/admin/clients/{$client->id}", ['status' => 'suspended'])->assertRedirect();
    expect($client->user->fresh()->status)->toBe('suspended');
});

it('lists inquiries and saves a reply', function () {
    $admin = admin();
    $tender = makeTender();
    $providerUser = User::factory()->create(['role' => 'provider']);
    $provider = ProviderProfile::create(['user_id' => $providerUser->id, 'company_name' => 'مورد', 'status' => 'approved']);

    $inquiry = TenderInquiry::create([
        'tender_id' => $tender->id,
        'provider_id' => $provider->id,
        'question' => 'ما هي مدة العقد؟',
    ]);

    $this->actingAs($admin)->get('/admin/inquiries')->assertOk();
    $this->actingAs($admin)->get('/admin/inquiries?filter=unanswered')->assertOk();

    $this->actingAs($admin)->put("/admin/inquiries/{$inquiry->id}", ['answer' => ''])->assertSessionHasErrors('answer');

    $this->actingAs($admin)->put("/admin/inquiries/{$inquiry->id}", ['answer' => 'ثلاث سنوات'])->assertRedirect();
    $fresh = $inquiry->fresh();
    expect($fresh->answer)->toBe('ثلاث سنوات');
    expect($fresh->answered_at)->not->toBeNull();
});
