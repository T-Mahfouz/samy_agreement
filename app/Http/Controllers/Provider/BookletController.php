<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Tender;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class BookletController extends Controller
{
    public function index(Request $request): Response
    {
        $provider = $request->user()->providerProfile;

        $payments = Payment::query()
            ->where('provider_id', $provider?->id)
            ->where('type', 'brochure_fee')
            ->with(['tender:id,tender_no,reference_no,name,client_id,brochure_price,brochure_file', 'tender.client:id,company_name'])
            ->latest()
            ->paginate(15)
            ->withQueryString()
            ->through(fn ($p) => [
                'id' => $p->id,
                'tender_id' => $p->tender_id,
                'tender_no' => $p->tender?->tender_no,
                'reference_no' => $p->tender?->reference_no,
                'tender_name' => $p->tender?->name,
                'client' => $p->tender?->client?->company_name,
                'amount' => $p->amount,
                'receipt_file' => $p->receipt_file,
                'status' => $p->status,
                'has_file' => (bool) $p->tender?->brochure_file,
            ]);

        return Inertia::render('provider/Booklets', [
            'requests' => $payments,
        ]);
    }

    public function download(Request $request, Tender $tender)
    {
        $provider = $request->user()->providerProfile;

        $paid = Payment::where('tender_id', $tender->id)
            ->where('provider_id', $provider?->id)
            ->where('type', 'brochure_fee')
            ->where('status', 'paid')
            ->exists();

        abort_unless($paid, 403, 'يجب اعتماد سداد قيمة كراسة الشروط أولًا.');
        abort_unless($tender->brochure_file && Storage::disk('public')->exists($tender->brochure_file), 404, 'لم تُرفع كراسة الشروط لهذه المنافسة بعد.');

        $ext = pathinfo($tender->brochure_file, PATHINFO_EXTENSION);
        $filename = 'كراسة-الشروط-'.$tender->tender_no.($ext ? '.'.$ext : '');

        return Storage::disk('public')->download($tender->brochure_file, $filename);
    }
}
