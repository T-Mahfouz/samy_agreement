<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Concerns\ErasesRecords;
use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\Offer;
use App\Models\Tender;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    use ErasesRecords;

    public function index(Request $request): Response
    {
        $client = $request->user()->clientProfile;

        $columns = [
            'id', 'client_id', 'tender_no', 'reference_no', 'serial_no', 'name', 'type',
            'brochure_price', 'contract_duration_months', 'published_at',
            'offers_deadline', 'offers_deadline_hijri', 'offers_deadline_time',
            'offers_open', 'offers_open_hijri', 'offers_open_time',
            'status', 'awarded_offer_id',
        ];

        $base = fn () => Tender::query()
            ->where('client_id', $client?->id)
            ->with(['locations.region:id,name', 'locations.city:id,name'])
            ->withCount('offers')
            ->latest();

        $active = (clone $base())->where('status', '!=', 'awarded')
            ->paginate(10, $columns, 'active_page')->withQueryString();

        $awarded = (clone $base())
            ->where('status', 'awarded')
            ->with(['awardedOffer.provider:id,company_name', 'contract:id,tender_id,status'])
            ->paginate(10, $columns, 'awarded_page')->withQueryString();

        $tenderIds = Tender::where('client_id', $client?->id)->pluck('id');

        return Inertia::render('client/Dashboard', [
            'clientName' => $client?->company_name ?? $request->user()->name,
            'activeTenders' => $active,
            'awardedTenders' => $awarded,
            'stats' => [
                'active' => (clone $base())->where('status', '!=', 'awarded')->count(),
                'awarded' => (clone $base())->where('status', 'awarded')->count(),
                'offers' => Offer::whereIn('tender_id', $tenderIds)->count(),
                'contracts' => Contract::whereIn('tender_id', $tenderIds)->count(),
            ],
        ]);
    }

    public function cancelTender(Request $request, Tender $tender)
    {
        abort_unless($tender->client_id === $request->user()->clientProfile?->id, 403);

        $this->eraseTender($tender);

        return back()->with('success', 'تم إلغاء المنافسة وحذفها نهائيًا.');
    }
}
