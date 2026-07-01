<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\NormalizesIban;
use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SettingController extends Controller
{
    use NormalizesIban;

    /** مفاتيح الإعدادات المعروفة */
    private const KEYS = [
        'platform_bank_name',
        'platform_bank_beneficiary',
        'platform_bank_iban',
        'default_commission_rate',
        'contact_phone',
        'contact_whatsapp',
        'contact_email',
        'contact_support_email',
    ];

    public function index(): Response
    {
        $settings = SystemSetting::query()
            ->whereIn('key', self::KEYS)
            ->pluck('value', 'key');

        // ضمان وجود كل المفاتيح (حتى الفارغة) للنموذج
        $values = [];
        foreach (self::KEYS as $key) {
            $values[$key] = $settings[$key] ?? '';
        }

        return Inertia::render('admin/settings/Index', [
            'settings' => $values,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $this->normalizeIban($request, 'platform_bank_iban');

        $data = $request->validate([
            'platform_bank_name' => ['nullable', 'string', 'max:255'],
            'platform_bank_beneficiary' => ['nullable', 'string', 'max:255'],
            'platform_bank_iban' => ['nullable', 'string', 'regex:/^SA[0-9A-Z]{22}$/'],
            'default_commission_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'contact_phone' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\s()-]+$/'],
            'contact_whatsapp' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\s()-]+$/'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_support_email' => ['nullable', 'email', 'max:255'],
        ], [
            'platform_bank_iban.regex' => 'رقم الآيبان غير صحيح (يبدأ بـ SA ويتكوّن من 24 خانة).',
            'contact_phone.regex' => 'رقم الجوال يجب أن يحتوي على أرقام فقط.',
            'contact_whatsapp.regex' => 'رقم الواتساب يجب أن يحتوي على أرقام فقط.',
        ]);

        foreach ($data as $key => $value) {
            SystemSetting::set($key, (string) $value);
        }

        return back()->with('success', 'تم حفظ الإعدادات.');
    }
}
