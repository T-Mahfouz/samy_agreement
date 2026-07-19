<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Concerns\NormalizesIban;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    use NormalizesIban;

    public function edit(Request $request): Response
    {
        $user = $request->user();
        $p = $user->clientProfile;

        return Inertia::render('client/Profile', [
            'profile' => $p?->only(['company_name', 'mobile', 'bank_name', 'bank_beneficiary_name', 'bank_iban']),
            'username' => $user->username,
            'email' => $user->email,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $this->normalizeIban($request, 'bank_iban');

        $data = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'mobile' => ['required', 'string', 'max:20', 'regex:/^[0-9+\s()-]+$/'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'bank_beneficiary_name' => ['nullable', 'string', 'max:255'],
            'bank_iban' => ['nullable', 'string', 'max:50'],
            'username' => ['nullable', 'string', 'max:255', Rule::unique('users', 'username')->ignore($user->id)],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
        ], [
            'mobile.required' => 'رقم الجوال مطلوب.',
            'mobile.regex' => 'رقم الجوال يجب أن يحتوي على أرقام فقط.',
        ], ['company_name' => 'اسم المنشأة', 'mobile' => 'رقم الجوال', 'bank_iban' => 'رقم الآيبان']);

        $user->clientProfile?->update([
            'company_name' => $data['company_name'],
            'mobile' => $data['mobile'] ?? null,
            'bank_name' => $data['bank_name'] ?? null,
            'bank_beneficiary_name' => $data['bank_beneficiary_name'] ?? null,
            'bank_iban' => $data['bank_iban'] ?? null,
        ]);

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
