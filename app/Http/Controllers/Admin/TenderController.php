<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tender;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TenderController extends Controller
{
    public function index(Request $request): Response
    {
        $status = $request->query('status');

        $tenders = Tender::query()
            ->with(['client:id,company_name', 'category:id,name'])
            ->withCount('offers')
            ->when(
                in_array($status, ['active', 'examination', 'awarding', 'awarded', 'cancelled'], true),
                fn ($q) => $q->where('status', $status)
            )
            ->latest()
            ->paginate(15, ['id', 'tender_no', 'reference_no', 'name', 'type', 'client_id', 'category_id', 'brochure_price', 'offers_deadline', 'status', 'created_at'])->withQueryString();

        return Inertia::render('admin/tenders/Index', [
            'tenders' => $tenders,
            'filter' => $status,
            'counts' => [
                'all' => Tender::count(),
                'active' => Tender::where('status', 'active')->count(),
                'examination' => Tender::where('status', 'examination')->count(),
                'awarding' => Tender::where('status', 'awarding')->count(),
                'awarded' => Tender::where('status', 'awarded')->count(),
                'cancelled' => Tender::where('status', 'cancelled')->count(),
            ],
        ]);
    }

    public function show(Tender $tender): Response
    {
        $tender->load([
            'client:id,company_name,mobile,bank_name,bank_iban',
            'category:id,name',
            'subcategory:id,name',
            'locations.region:id,name',
            'locations.city:id,name',
            'offers.provider:id,company_name',
            'awardedOffer:id,provider_id,financial_value',
            'contract:id,tender_id,status',
        ]);

        return Inertia::render('admin/tenders/Show', [
            'tender' => $tender,
        ]);
    }

    public function update(Request $request, Tender $tender): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:active,examination,awarding,awarded,cancelled'],
        ]);

        $tender->update($data);

        return back()->with('success', 'تم تحديث حالة المنافسة.');
    }
}
