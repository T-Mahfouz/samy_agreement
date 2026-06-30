<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TenderInquiry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InquiryController extends Controller
{
    public function index(Request $request): Response
    {
        $filter = $request->query('filter'); // answered | unanswered

        $inquiries = TenderInquiry::query()
            ->with(['tender:id,tender_no,name', 'provider:id,company_name'])
            ->when($filter === 'answered', fn ($q) => $q->whereNotNull('answered_at'))
            ->when($filter === 'unanswered', fn ($q) => $q->whereNull('answered_at'))
            ->latest()
            ->paginate(15, ['id', 'tender_id', 'provider_id', 'question', 'answer', 'answered_at', 'created_at'])->withQueryString();

        return Inertia::render('admin/inquiries/Index', [
            'inquiries' => $inquiries,
            'filter' => $filter,
            'counts' => [
                'all' => TenderInquiry::count(),
                'unanswered' => TenderInquiry::whereNull('answered_at')->count(),
                'answered' => TenderInquiry::whereNotNull('answered_at')->count(),
            ],
        ]);
    }

    public function update(Request $request, TenderInquiry $inquiry): RedirectResponse
    {
        $data = $request->validate([
            'answer' => ['required', 'string'],
        ], [], ['answer' => 'الرد']);

        $inquiry->update([
            'answer' => $data['answer'],
            'answered_at' => now(),
        ]);

        return back()->with('success', 'تم حفظ الرد على الاستفسار.');
    }
}
