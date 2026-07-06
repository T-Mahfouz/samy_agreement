<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Concerns\HandlesProviderDocuments;
use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    use HandlesProviderDocuments;

    public function edit(Request $request): Response
    {
        $user = $request->user();
        $p = $user->providerProfile;

        return Inertia::render('provider/Profile', [
            'profile' => $p ? [
                'company_name' => $p->company_name,
                'commercial_register_no' => $p->commercial_register_no,
                'cr_issue_date' => optional($p->cr_issue_date)->format('Y-m-d'),
                'cr_type' => $p->cr_type,
                'mobile' => $p->mobile,
                'main_category_id' => $p->main_category_id,
                'sub_category_id' => $p->sub_category_id,
                'activity_description' => $p->activity_description,
            ] : null,
            'documents' => $p ? $this->providerDocumentsByField($p) : [],
            'categories' => Category::where('is_active', true)->orderBy('sort_order')->get(['id', 'name', 'parent_id']),
            'username' => $user->username,
            'email' => $user->email,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'commercial_register_no' => ['nullable', 'string', 'max:20', 'regex:/^[0-9]+$/'],
            'cr_issue_date' => ['nullable', 'date', 'before_or_equal:today'],
            'cr_type' => ['nullable', 'string', 'max:255'],
            'mobile' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\s()-]+$/'],
            'main_category_id' => ['nullable', 'exists:categories,id'],
            'sub_category_id' => ['nullable', 'exists:categories,id'],
            'activity_description' => ['nullable', 'string'],
            'username' => ['nullable', 'string', 'max:255', Rule::unique('users', 'username')->ignore($user->id)],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],

            ...$this->providerDocumentRules(),
        ], [
            'mobile.regex' => 'رقم الجوال يجب أن يحتوي على أرقام فقط.',
            'commercial_register_no.regex' => 'رقم السجل التجاري يجب أن يحتوي على أرقام فقط.',
            'cr_issue_date.before_or_equal' => 'لا يمكن أن يكون تاريخ إصدار السجل التجاري في المستقبل.',
        ], [
            'company_name' => 'اسم المنشأة',
            'commercial_register_no' => 'رقم السجل التجاري',
            'cr_issue_date' => 'تاريخ إصدار السجل التجاري',
            'mobile' => 'رقم الجوال',
        ]);

        $provider = $user->providerProfile;
        $provider?->update([
            'company_name' => $data['company_name'],
            'commercial_register_no' => $data['commercial_register_no'] ?? null,
            'cr_issue_date' => $data['cr_issue_date'] ?? null,
            'cr_type' => $data['cr_type'] ?? null,
            'mobile' => $data['mobile'] ?? null,
            'main_category_id' => $data['main_category_id'] ?? null,
            'sub_category_id' => $data['sub_category_id'] ?? null,
            'activity_description' => $data['activity_description'] ?? null,
        ]);

        if ($provider) {
            $this->syncProviderDocuments($request, $provider);
        }

        $user->fill([
            'name' => $data['company_name'],
            'username' => $data['username'] ?? null,
            'phone' => $data['mobile'] ?? null,
            'email' => $data['email'],
        ]);
        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        $user->save();

        return back()->with('success', 'تم تحديث البيانات بنجاح.');
    }
}
