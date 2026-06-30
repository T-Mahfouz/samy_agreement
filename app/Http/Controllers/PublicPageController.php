<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use App\Models\ContentPage;
use App\Models\Faq;
use App\Models\Notification;
use App\Models\SystemSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PublicPageController extends Controller
{
    public function about(): Response
    {
        $sections = ContentPage::where('slug', 'about')->get()->keyBy('section_key');

        return Inertia::render('public/About', [
            'sections' => [
                'who_we_are' => $sections->get('who_we_are')?->only(['title', 'body']),
                'vision' => $sections->get('vision')?->only(['title', 'body']),
                'mission' => $sections->get('mission')?->only(['title', 'body']),
            ],
        ]);
    }

    public function terms(): Response
    {
        return $this->staticPage('terms', 'شروط الاستخدام وأداء المسؤولية');
    }

    public function privacy(): Response
    {
        return $this->staticPage('privacy', 'سياسة الخصوصية');
    }

    private function staticPage(string $slug, string $fallbackTitle): Response
    {
        $page = ContentPage::where('slug', $slug)->first();

        return Inertia::render('public/StaticPage', [
            'title' => $page?->title ?? $fallbackTitle,
            'body' => $page?->body ?? 'سيتم إضافة المحتوى قريبًا.',
        ]);
    }

    public function faqs(): Response
    {
        return Inertia::render('public/Faqs', [
            'faqs' => Faq::where('is_active', true)->orderBy('sort_order')->get(['id', 'question', 'answer']),
        ]);
    }

    public function contact(): Response
    {
        return Inertia::render('public/Contact', [
            'info' => [
                'phone' => SystemSetting::get('contact_phone', ''),
                'whatsapp' => SystemSetting::get('contact_whatsapp', ''),
                'email' => SystemSetting::get('contact_email', ''),
                'support_email' => SystemSetting::get('contact_support_email', ''),
            ],
        ]);
    }

    public function contactStore(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'mobile' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'message' => ['required', 'string'],
        ], [], [
            'full_name' => 'الاسم بالكامل',
            'message' => 'تفاصيل الرسالة',
        ]);

        ContactMessage::create($data + ['status' => 'new']);

        Notification::notifyAdmins('رسالة تواصل جديدة', "رسالة من {$data['full_name']}", '/admin/messages');

        return back()->with('success', 'تم إرسال رسالتك بنجاح، سنتواصل معك قريبًا.');
    }
}
