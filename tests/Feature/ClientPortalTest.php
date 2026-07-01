<?php

use App\Models\City;
use App\Models\ClientProfile;
use App\Models\Region;
use App\Models\Tender;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function clientUser(): User
{
    $user = User::factory()->create(['role' => 'client', 'status' => 'active']);
    ClientProfile::create(['user_id' => $user->id, 'company_name' => 'شركة العميل']);

    return $user;
}

it('shows the client dashboard', function () {
    $this->actingAs(clientUser())->get('/client/dashboard')->assertOk();
});

it('blocks non-clients from the client portal', function () {
    $provider = User::factory()->create(['role' => 'provider']);
    $this->actingAs($provider)->get('/client/dashboard')->assertForbidden();
    $this->actingAs($provider)->get('/client/tenders/create')->assertForbidden();
});

it('lets a client create (publish) a tender with a location', function () {
    $user = clientUser();
    $region = Region::create(['name' => 'الرياض', 'is_active' => true]);
    $city = City::create(['region_id' => $region->id, 'name' => 'الرياض', 'is_active' => true]);

    $res = $this->actingAs($user)->post('/client/tenders', [
        'type' => 'general',
        'name' => 'منافسة جديدة للاختبار',
        'brochure_price' => 500,
        'contract_duration_months' => 36,
        'insurance_required' => false,
        'initial_guarantee_required' => true,
        'final_guarantee_required' => true,
        'includes_supply_items' => false,
        'locations' => [['region_id' => $region->id, 'city_id' => $city->id]],
        'offers_deadline' => now()->addDays(10)->toDateString(),
    ]);

    $res->assertRedirect('/client/dashboard');
    $tender = Tender::where('name', 'منافسة جديدة للاختبار')->firstOrFail();
    expect($tender->client_id)->toBe($user->clientProfile->id);
    expect($tender->status)->toBe('active');
    expect($tender->published_at)->not->toBeNull();
    expect($tender->tender_no)->not->toBeNull();
    expect($tender->locations()->count())->toBe(1);
});

it('validates required tender fields', function () {
    $this->actingAs(clientUser())->post('/client/tenders', ['type' => '', 'name' => ''])
        ->assertSessionHasErrors(['type', 'name']);
});

it('requires an offers deadline', function () {
    $this->actingAs(clientUser())->post('/client/tenders', ['type' => 'general', 'name' => 'بدون موعد'])
        ->assertSessionHasErrors('offers_deadline');
});

it('rejects an offers deadline in the past', function () {
    $this->actingAs(clientUser())->post('/client/tenders', [
        'type' => 'general', 'name' => 'تاريخ منتهي',
        'offers_deadline' => now()->subDay()->toDateString(),
    ])->assertSessionHasErrors('offers_deadline');
});

it('rejects a non-numeric guarantee value', function () {
    $this->actingAs(clientUser())->post('/client/tenders', [
        'type' => 'general', 'name' => 'قيمة نصية',
        'offers_deadline' => now()->addDays(10)->toDateString(),
        'initial_guarantee_value' => 'نص وليس رقم',
    ])->assertSessionHasErrors('initial_guarantee_value');
});

it('accepts a real docx booklet even when it is detected as a zip file', function () {
    Storage::fake('public');
    $path = tempnam(sys_get_temp_dir(), 'bk').'.docx';
    $zip = new ZipArchive();
    $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);
    $zip->addFromString('word/document.xml', '<w:document/>');
    $zip->close();
    $file = new UploadedFile($path, 'booklet.docx', null, null, true);

    $this->actingAs(clientUser())->post('/client/tenders', [
        'type' => 'general', 'name' => 'منافسة بملف docx حقيقي',
        'offers_deadline' => now()->addDays(10)->toDateString(),
        'booklet_file' => $file,
    ])->assertSessionHasNoErrors()->assertRedirect('/client/dashboard');

    // الملف المخزَّن يحتفظ بامتداده الأصلي حتى يُحمَّل بشكل صحيح
    $tender = Tender::where('name', 'منافسة بملف docx حقيقي')->firstOrFail();
    expect($tender->brochure_file)->toEndWith('.docx');

    @unlink($path);
});

it('rejects a text file renamed to .docx as a booklet', function () {
    Storage::fake('public');
    $path = tempnam(sys_get_temp_dir(), 'bk').'.docx';
    file_put_contents($path, 'this is plain text, not a real document');
    $file = new UploadedFile($path, 'fake.docx', null, null, true);

    $this->actingAs(clientUser())->post('/client/tenders', [
        'type' => 'general', 'name' => 'منافسة بملف مزيف',
        'offers_deadline' => now()->addDays(10)->toDateString(),
        'booklet_file' => $file,
    ])->assertSessionHasErrors('booklet_file');

    @unlink($path);
});

it('rejects offers_open before the offers deadline', function () {
    $this->actingAs(clientUser())->post('/client/tenders', [
        'type' => 'general', 'name' => 'ترتيب مواعيد خاطئ',
        'offers_deadline' => now()->addDays(10)->toDateString(),
        'offers_open' => now()->addDays(5)->toDateString(),
    ])->assertSessionHasErrors('offers_open');
});

it('lets a client cancel their own tender but not others', function () {
    $user = clientUser();
    $tender = Tender::create([
        'client_id' => $user->clientProfile->id,
        'tender_no' => 'C-1', 'serial_no' => 'CS-1', 'name' => 'منافسة', 'type' => 'general', 'status' => 'active',
    ]);
    $other = Tender::create([
        'client_id' => clientUser()->clientProfile->id,
        'tender_no' => 'C-2', 'serial_no' => 'CS-2', 'name' => 'أخرى', 'type' => 'general', 'status' => 'active',
    ]);

    $this->actingAs($user)->put("/client/tenders/{$tender->id}/cancel")->assertRedirect();
    expect($tender->fresh()->status)->toBe('cancelled');

    $this->actingAs($user)->put("/client/tenders/{$other->id}/cancel")->assertForbidden();
});
