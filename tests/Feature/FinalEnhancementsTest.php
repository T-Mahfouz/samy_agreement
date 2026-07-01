<?php

use App\Models\City;
use App\Models\ClientProfile;
use App\Models\Notification;
use App\Models\Offer;
use App\Models\ProviderProfile;
use App\Models\Region;
use App\Models\Tender;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function feClient(): User
{
    $u = User::factory()->create(['role' => 'client', 'status' => 'active']);
    ClientProfile::create(['user_id' => $u->id, 'company_name' => 'عميل']);

    return $u;
}

it('recomputes tender status from dates', function () {
    $client = feClient()->clientProfile;
    $examination = Tender::create(['client_id' => $client->id, 'tender_no' => 'F1', 'serial_no' => 'FS1', 'name' => 'م1', 'type' => 'general', 'status' => 'active', 'offers_deadline' => now()->subDay()->toDateString(), 'expected_award_date' => now()->addDays(3)->toDateString()]);
    $awarding = Tender::create(['client_id' => $client->id, 'tender_no' => 'F2', 'serial_no' => 'FS2', 'name' => 'م2', 'type' => 'general', 'status' => 'active', 'offers_deadline' => now()->subDays(5)->toDateString(), 'expected_award_date' => now()->subDay()->toDateString()]);
    $stillActive = Tender::create(['client_id' => $client->id, 'tender_no' => 'F3', 'serial_no' => 'FS3', 'name' => 'م3', 'type' => 'general', 'status' => 'active', 'offers_deadline' => now()->addDays(5)->toDateString()]);

    $this->artisan('tenders:recompute-status')->assertSuccessful();

    expect($examination->fresh()->status)->toBe('examination');
    expect($awarding->fresh()->status)->toBe('awarding');
    expect($stillActive->fresh()->status)->toBe('active');
});

it('creates a notification for the client when a provider submits an offer', function () {
    Storage::fake('public');
    $client = feClient();
    $tender = Tender::create(['client_id' => $client->clientProfile->id, 'tender_no' => 'N1', 'serial_no' => 'NS1', 'name' => 'منافسة', 'type' => 'general', 'status' => 'active']);
    $pu = User::factory()->create(['role' => 'provider']);
    ProviderProfile::create(['user_id' => $pu->id, 'company_name' => 'مورد', 'status' => 'approved']);

    $this->actingAs($pu)->post("/provider/tenders/{$tender->id}/offer", [
        'technical_file' => UploadedFile::fake()->create('t.pdf', 10, 'application/pdf'),
        'financial_file' => UploadedFile::fake()->create('f.pdf', 10, 'application/pdf'),
        'financial_value' => 5000,
        'declaration_accepted' => true,
    ]);

    expect(Notification::where('user_id', $client->id)->count())->toBe(1);
});

it('marks notifications as read', function () {
    $u = feClient();
    $n = Notification::create(['user_id' => $u->id, 'title' => 'تنبيه', 'is_read' => false]);

    $this->actingAs($u)->put("/notifications/{$n->id}/read")->assertRedirect();
    expect($n->fresh()->is_read)->toBeTrue();

    Notification::create(['user_id' => $u->id, 'title' => 'آخر', 'is_read' => false]);
    $this->actingAs($u)->put('/notifications/read-all')->assertRedirect();
    expect(Notification::where('user_id', $u->id)->where('is_read', false)->count())->toBe(0);
});

it('creates a tender with multiple locations', function () {
    $u = feClient();
    $r1 = Region::create(['name' => 'الرياض', 'is_active' => true]);
    $r2 = Region::create(['name' => 'مكة', 'is_active' => true]);
    $c1 = City::create(['region_id' => $r1->id, 'name' => 'الرياض', 'is_active' => true]);
    $c2 = City::create(['region_id' => $r2->id, 'name' => 'جدة', 'is_active' => true]);

    $this->actingAs($u)->post('/client/tenders', [
        'type' => 'general', 'name' => 'منافسة متعددة المواقع',
        'offers_deadline' => now()->addDays(10)->toDateString(),
        'locations' => [
            ['region_id' => $r1->id, 'city_id' => $c1->id],
            ['region_id' => $r2->id, 'city_id' => $c2->id],
        ],
    ])->assertRedirect('/client/dashboard');

    $tender = Tender::where('name', 'منافسة متعددة المواقع')->firstOrFail();
    expect($tender->locations()->count())->toBe(2);
});

it('shows terms and privacy pages', function () {
    $this->get('/terms')->assertOk();
    $this->get('/privacy')->assertOk();
});
