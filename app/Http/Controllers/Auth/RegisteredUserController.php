<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\ClientProfile;
use App\Models\ProviderProfile;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /** أنواع مستندات المورّد المرفوعة (input name => doc_type) */
    private const DOC_FIELDS = [
        'attach_cr' => 'commercial_register',
        'attach_zakat' => 'zakat_cert',
        'attach_tax' => 'tax_cert',
        'attach_sector_class' => 'sector_classification',
        'attach_social_insurance' => 'social_insurance',
        'attach_saudization' => 'saudization_cert',
        'attach_investment_license' => 'investment_license',
        'attach_municipal_license' => 'municipality_license',
        'attach_chamber' => 'chamber_membership',
        'attach_contractors_auth' => 'contractors_authority_cert',
        'attach_sme' => 'sme_authority_cert',
        'attach_other_licenses' => 'other_licenses',
        'attach_auth_letter' => 'authorized_signatory_letter',
        'attach_auth_id' => 'authorized_signatory_id',
        'attach_manager_id' => 'manager_id',
    ];

    public function create(Request $request): Response
    {
        $role = $request->query('role') === 'provider' ? 'provider' : 'client';

        if ($role === 'provider') {
            return Inertia::render('auth/ProviderRegister', [
                'categories' => Category::query()->where('is_active', true)->orderBy('sort_order')->get(['id', 'name', 'parent_id']),
            ]);
        }

        return Inertia::render('auth/ClientRegister');
    }

    public function store(Request $request): RedirectResponse
    {
        $role = $request->input('role') === 'provider' ? 'provider' : 'client';

        return $role === 'provider'
            ? $this->storeProvider($request)
            : $this->storeClient($request);
    }

    private function storeClient(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'facility_name' => ['required', 'string', 'max:255'],
            'mobile' => ['nullable', 'string', 'max:30'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'beneficiary_name' => ['nullable', 'string', 'max:255'],
            'iban' => ['nullable', 'string', 'max:50'],
            'username' => ['nullable', 'string', 'max:255', 'unique:users,username'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $data['facility_name'],
            'username' => $data['username'] ?? null,
            'role' => 'client',
            'phone' => $data['mobile'] ?? null,
            'status' => 'active',
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'email_verified_at' => now(),
        ]);

        ClientProfile::create([
            'user_id' => $user->id,
            'company_name' => $data['facility_name'],
            'mobile' => $data['mobile'] ?? null,
            'bank_name' => $data['bank_name'] ?? null,
            'bank_beneficiary_name' => $data['beneficiary_name'] ?? null,
            'bank_iban' => $data['iban'] ?? null,
        ]);

        event(new Registered($user));
        Auth::login($user);

        return redirect($user->dashboardPath());
    }

    private function storeProvider(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'facility_name' => ['required', 'string', 'max:255'],
            'cr_number' => ['nullable', 'string', 'max:100'],
            'cr_issue_date' => ['nullable', 'date'],
            'cr_type' => ['nullable', 'string', 'max:255'],
            'mobile' => ['nullable', 'string', 'max:30'],
            'main_sector' => ['nullable', 'exists:categories,id'],
            'sub_activity' => ['nullable', 'exists:categories,id'],
            'activity_description' => ['nullable', 'string'],
            'username' => ['nullable', 'string', 'max:255', 'unique:users,username'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'attach_cr' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:5120'],
            // باقي المستندات اختيارية لكن لازم نفس قيود النوع والحجم لو رُفعت.
            ...collect(self::DOC_FIELDS)
                ->except('attach_cr')
                ->keys()
                ->mapWithKeys(fn ($field) => [$field => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:5120']])
                ->all(),
        ]);

        $user = User::create([
            'name' => $data['facility_name'],
            'username' => $data['username'] ?? null,
            'role' => 'provider',
            'phone' => $data['mobile'] ?? null,
            'status' => 'pending', // بانتظار اعتماد الأدمن
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'email_verified_at' => now(),
        ]);

        $provider = ProviderProfile::create([
            'user_id' => $user->id,
            'company_name' => $data['facility_name'],
            'commercial_register_no' => $data['cr_number'] ?? null,
            'cr_issue_date' => $data['cr_issue_date'] ?? null,
            'cr_type' => $data['cr_type'] ?? null,
            'mobile' => $data['mobile'] ?? null,
            'main_category_id' => $data['main_sector'] ?? null,
            'sub_category_id' => $data['sub_activity'] ?? null,
            'activity_description' => $data['activity_description'] ?? null,
            'status' => 'pending',
        ]);

        foreach (self::DOC_FIELDS as $field => $docType) {
            if ($request->hasFile($field)) {
                $path = $request->file($field)->store("provider-docs/{$provider->id}", 'public');
                $provider->documents()->create([
                    'doc_type' => $docType,
                    'file_path' => $path,
                    'uploaded_at' => now(),
                ]);
            }
        }

        event(new Registered($user));
        Auth::login($user);

        return redirect($user->dashboardPath());
    }
}
