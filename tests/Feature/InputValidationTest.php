<?php

use App\Models\ClientProfile;
use App\Models\ProviderProfile;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('rejects a non-numeric mobile on client registration', function () {
    $this->post('/register', [
        'role' => 'client', 'facility_name' => 'منشأة',
        'mobile' => 'ليس رقمًا',
        'email' => 'mob1@test.com', 'password' => 'password123', 'password_confirmation' => 'password123',
    ])->assertSessionHasErrors('mobile');
});

it('rejects a malformed IBAN on client registration', function () {
    $this->post('/register', [
        'role' => 'client', 'facility_name' => 'منشأة',
        'iban' => 'BADIBAN123',
        'email' => 'iban1@test.com', 'password' => 'password123', 'password_confirmation' => 'password123',
    ])->assertSessionHasErrors('iban');
});

it('rejects a future commercial-register issue date on provider registration', function () {
    Storage::fake('public');
    $this->post('/register', [
        'role' => 'provider', 'facility_name' => 'مورد',
        'cr_issue_date' => now()->addYear()->toDateString(),
        'email' => 'crdate@test.com', 'password' => 'password123', 'password_confirmation' => 'password123',
        'attach_cr' => UploadedFile::fake()->create('cr.pdf', 100, 'application/pdf'),
    ])->assertSessionHasErrors('cr_issue_date');
});

it('rejects a non-numeric commercial-register number on provider registration', function () {
    Storage::fake('public');
    $this->post('/register', [
        'role' => 'provider', 'facility_name' => 'مورد',
        'cr_number' => 'AB123',
        'email' => 'crno@test.com', 'password' => 'password123', 'password_confirmation' => 'password123',
        'attach_cr' => UploadedFile::fake()->create('cr.pdf', 100, 'application/pdf'),
    ])->assertSessionHasErrors('cr_number');
});

it('rejects a non-numeric mobile on the client profile', function () {
    $user = User::factory()->create(['role' => 'client']);
    ClientProfile::create(['user_id' => $user->id, 'company_name' => 'عميل']);

    $this->actingAs($user)->put('/client/profile', [
        'company_name' => 'عميل', 'mobile' => 'حروف',
    ])->assertSessionHasErrors('mobile');
});

it('rejects a non-numeric commercial register on the provider profile', function () {
    $user = User::factory()->create(['role' => 'provider', 'status' => 'active']);
    ProviderProfile::create(['user_id' => $user->id, 'company_name' => 'مورد', 'status' => 'approved']);

    $this->actingAs($user)->put('/provider/profile', [
        'company_name' => 'مورد', 'commercial_register_no' => 'abc123',
    ])->assertSessionHasErrors('commercial_register_no');
});

it('rejects a malformed IBAN in admin settings', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)->put('/admin/settings', [
        'platform_bank_iban' => 'NOTANIBAN',
        'default_commission_rate' => 1,
    ])->assertSessionHasErrors('platform_bank_iban');
});

it('accepts a valid Saudi IBAN and numeric mobile on client registration', function () {
    $this->post('/register', [
        'role' => 'client', 'facility_name' => 'منشأة سليمة',
        'mobile' => '0512345678',
        'iban' => 'SA4420000001234567891234',
        'email' => 'valid@test.com', 'password' => 'password123', 'password_confirmation' => 'password123',
    ])->assertRedirect('/client/dashboard');
});
