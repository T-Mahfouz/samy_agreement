<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FaqController extends Controller
{
    public function index(): Response
    {
        $faqs = Faq::query()
            ->orderBy('sort_order')
            ->paginate(15, ['id', 'question', 'answer', 'sort_order', 'is_active'])->withQueryString();

        return Inertia::render('admin/faqs/Index', [
            'faqs' => $faqs,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Faq::create($this->validateData($request));

        return back()->with('success', 'تم إضافة السؤال.');
    }

    public function update(Request $request, Faq $faq): RedirectResponse
    {
        $faq->update($this->validateData($request));

        return back()->with('success', 'تم تحديث السؤال.');
    }

    public function destroy(Faq $faq): RedirectResponse
    {
        $faq->delete();

        return back()->with('success', 'تم حذف السؤال.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'question' => ['required', 'string', 'max:500'],
            'answer' => ['required', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ], [], [
            'question' => 'السؤال',
            'answer' => 'الإجابة',
        ]);
    }
}
