<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Concerns\NormalizesIban;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    use NormalizesIban;

    public function edit(Request $request): Response
    {
        $p = $request->user()->clientProfile;

        return Inertia::render('client/Profile', [
            'profile' => $p?->only(['company_name', 'mobile', 'bank_name', 'bank_beneficiary_name', 'bank_iban']),
            'email' => $request->user()->email,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $this->normalizeIban($request, 'bank_iban');

        $data = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'mobile' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\s()-]+$/'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'bank_beneficiary_name' => ['nullable', 'string', 'max:255'],
            'bank_iban' => ['nullable', 'string', 'regex:/^SA[0-9A-Z]{22}$/'],
        ], [
            'mobile.regex' => 'رقم الجوال يجب أن يحتوي على أرقام فقط.',
            'bank_iban.regex' => 'رقم الآيبان غير صحيح (يبدأ بـ SA ويتكوّن من 24 خانة).',
        ], ['company_name' => 'اسم المنشأة', 'mobile' => 'رقم الجوال', 'bank_iban' => 'رقم الآيبان']);

        $request->user()->clientProfile?->update($data);
        $request->user()->update(['name' => $data['company_name'], 'phone' => $data['mobile'] ?? null]);

        return back()->with('success', 'تم تحديث البيانات بنجاح.');
    }
}
