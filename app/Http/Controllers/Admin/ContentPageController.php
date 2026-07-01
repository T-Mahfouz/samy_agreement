<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContentPage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ContentPageController extends Controller
{
    public function index(): Response
    {
        $pages = ContentPage::query()
            ->orderBy('slug')
            ->orderBy('section_key')
            ->paginate(15, ['id', 'slug', 'section_key', 'title', 'body', 'updated_at'])->withQueryString();

        return Inertia::render('admin/content/Index', [
            'pages' => $pages,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        ContentPage::create($this->validateData($request));

        return back()->with('success', 'تم إضافة المحتوى.');
    }

    public function update(Request $request, ContentPage $content): RedirectResponse
    {
        $content->update($this->validateData($request, $content));

        return back()->with('success', 'تم تحديث المحتوى.');
    }

    public function destroy(ContentPage $content): RedirectResponse
    {
        $content->delete();

        return back()->with('success', 'تم حذف المحتوى.');
    }

    private function validateData(Request $request, ?ContentPage $content = null): array
    {
        return $request->validate([
            'slug' => ['required', 'string', 'max:100', 'regex:/^[a-z0-9-]+$/'],
            'section_key' => ['nullable', 'string', 'max:100', 'regex:/^[a-z0-9_-]+$/',
                Rule::unique('content_pages')->where(fn ($q) => $q->where('slug', $request->input('slug')))
                    ->ignore($content?->id),
            ],
            'title' => ['nullable', 'string', 'max:255'],
            'body' => ['nullable', 'string'],
        ], [
            'section_key.unique' => 'هذا القسم موجود بالفعل لنفس الصفحة.',
            'slug.regex' => 'الصفحة يجب أن تحتوي على حروف إنجليزية صغيرة وأرقام وشرطات (-) فقط.',
            'section_key.regex' => 'مفتاح القسم يجب أن يحتوي على حروف إنجليزية صغيرة وأرقام و(_ -) فقط.',
        ], [
            'slug' => 'الصفحة',
            'section_key' => 'مفتاح القسم',
            'title' => 'العنوان',
            'body' => 'المحتوى',
        ]);
    }
}
