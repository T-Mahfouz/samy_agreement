<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
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
        $data = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'mobile' => ['nullable', 'string', 'max:30'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'bank_beneficiary_name' => ['nullable', 'string', 'max:255'],
            'bank_iban' => ['nullable', 'string', 'max:50'],
        ], [], ['company_name' => 'اسم المنشأة']);

        $request->user()->clientProfile?->update($data);
        $request->user()->update(['name' => $data['company_name'], 'phone' => $data['mobile'] ?? null]);

        return back()->with('success', 'تم تحديث البيانات بنجاح.');
    }
}
