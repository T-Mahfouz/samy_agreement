<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function edit(Request $request): Response
    {
        $p = $request->user()->providerProfile;

        return Inertia::render('provider/Profile', [
            'profile' => $p?->only(['company_name', 'commercial_register_no', 'cr_type', 'mobile', 'main_category_id', 'sub_category_id', 'activity_description']),
            'categories' => Category::where('is_active', true)->orderBy('sort_order')->get(['id', 'name', 'parent_id']),
            'email' => $request->user()->email,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'commercial_register_no' => ['nullable', 'string', 'max:20', 'regex:/^[0-9]+$/'],
            'cr_type' => ['nullable', 'string', 'max:255'],
            'mobile' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\s()-]+$/'],
            'main_category_id' => ['nullable', 'exists:categories,id'],
            'sub_category_id' => ['nullable', 'exists:categories,id'],
            'activity_description' => ['nullable', 'string'],
        ], [
            'mobile.regex' => 'رقم الجوال يجب أن يحتوي على أرقام فقط.',
            'commercial_register_no.regex' => 'رقم السجل التجاري يجب أن يحتوي على أرقام فقط.',
        ], ['company_name' => 'اسم المنشأة', 'mobile' => 'رقم الجوال', 'commercial_register_no' => 'رقم السجل التجاري']);

        $request->user()->providerProfile?->update($data);
        $request->user()->update(['name' => $data['company_name'], 'phone' => $data['mobile'] ?? null]);

        return back()->with('success', 'تم تحديث البيانات بنجاح.');
    }
}
