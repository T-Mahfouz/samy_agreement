<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Offer;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OfferController extends Controller
{
    public function index(Request $request): Response
    {
        $status = $request->query('status');

        $offers = Offer::query()
            ->with([
                'tender:id,tender_no,name',
                'provider:id,company_name',
            ])
            ->when(
                in_array($status, ['submitted', 'under_review', 'awarded', 'rejected'], true),
                fn ($q) => $q->where('status', $status)
            )
            ->latest()
            ->paginate(15, ['id', 'tender_id', 'provider_id', 'financial_value', 'technical_check', 'is_awarded', 'status', 'submitted_at', 'created_at'])->withQueryString();

        return Inertia::render('admin/offers/Index', [
            'offers' => $offers,
            'filter' => $status,
            'counts' => [
                'all' => Offer::count(),
                'submitted' => Offer::where('status', 'submitted')->count(),
                'awarded' => Offer::where('status', 'awarded')->count(),
                'rejected' => Offer::where('status', 'rejected')->count(),
            ],
        ]);
    }
}
