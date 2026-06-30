<?php

use App\Models\Category;
use App\Models\ClientProfile;
use App\Models\ProviderProfile;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('registers a client and redirects to the client dashboard', function () {
    $res = $this->post('/register', [
        'role' => 'client',
        'facility_name' => 'شركة مساس',
        'mobile' => '0500000000',
        'bank_name' => 'الراجحي',
        'beneficiary_name' => 'شركة مساس',
        'iban' => 'SA00',
        'email' => 'newclient@test.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $res->assertRedirect('/client/dashboard');
    $user = User::where('email', 'newclient@test.com')->firstOrFail();
    expect($user->role)->toBe('client');
    expect($user->status)->toBe('active');
    expect(ClientProfile::where('user_id', $user->id)->exists())->toBeTrue();
});

it('registers a provider (pending) with an uploaded document', function () {
    Storage::fake('public');
    $cat = Category::create(['name' => 'قطاع', 'is_active' => true]);

    $res = $this->post('/register', [
        'role' => 'provider',
        'facility_name' => 'شركة عندنا',
        'cr_number' => '123',
        'mobile' => '0500000001',
        'main_sector' => $cat->id,
        'email' => 'newprovider@test.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'attach_cr' => UploadedFile::fake()->create('cr.pdf', 100, 'application/pdf'),
    ]);

    $res->assertRedirect('/provider/dashboard');
    $user = User::where('email', 'newprovider@test.com')->firstOrFail();
    expect($user->role)->toBe('provider');
    expect($user->status)->toBe('pending');
    $provider = ProviderProfile::where('user_id', $user->id)->firstOrFail();
    expect($provider->documents()->where('doc_type', 'commercial_register')->count())->toBe(1);
});

it('rejects a provider registration without the required CR document', function () {
    $this->post('/register', [
        'role' => 'provider',
        'facility_name' => 'شركة بلا مستند',
        'email' => 'nodoc@test.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])->assertSessionHasErrors('attach_cr');
});

it('rejects a disallowed file type on an optional provider document', function () {
    Storage::fake('public');

    $this->post('/register', [
        'role' => 'provider',
        'facility_name' => 'شركة بمرفق خاطئ',
        'email' => 'badfile@test.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'attach_cr' => UploadedFile::fake()->create('cr.pdf', 100, 'application/pdf'),
        // .exe خارج mimes المسموحة (pdf,jpg,jpeg,png,webp) — لازم يترفض
        'attach_zakat' => UploadedFile::fake()->create('virus.exe', 100, 'application/octet-stream'),
    ])->assertSessionHasErrors('attach_zakat');
});

it('rejects an oversized optional provider document (> 5MB)', function () {
    Storage::fake('public');

    $this->post('/register', [
        'role' => 'provider',
        'facility_name' => 'شركة بملف كبير',
        'email' => 'bigfile@test.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'attach_cr' => UploadedFile::fake()->create('cr.pdf', 100, 'application/pdf'),
        // 6 ميجا > الحد الأقصى 5 ميجا
        'attach_tax' => UploadedFile::fake()->create('big.pdf', 6144, 'application/pdf'),
    ])->assertSessionHasErrors('attach_tax');
});

it('redirects each role to its own dashboard on login', function () {
    $admin = User::factory()->create(['role' => 'admin', 'password' => bcrypt('password123')]);
    $client = User::factory()->create(['role' => 'client', 'password' => bcrypt('password123')]);
    $provider = User::factory()->create(['role' => 'provider', 'password' => bcrypt('password123')]);

    $this->post('/login', ['email' => $admin->email, 'password' => 'password123'])->assertRedirect('/admin/dashboard');
    auth()->logout();
    $this->post('/login', ['email' => $client->email, 'password' => 'password123'])->assertRedirect('/client/dashboard');
    auth()->logout();
    $this->post('/login', ['email' => $provider->email, 'password' => 'password123'])->assertRedirect('/provider/dashboard');
});

it('guards portal routes by role', function () {
    $client = User::factory()->create(['role' => 'client']);
    $provider = User::factory()->create(['role' => 'provider']);

    $this->actingAs($client)->get('/provider/dashboard')->assertForbidden();
    $this->actingAs($provider)->get('/client/dashboard')->assertForbidden();
    $this->actingAs($client)->get('/client/dashboard')->assertOk();
    $this->actingAs($provider)->get('/provider/dashboard')->assertOk();
});
