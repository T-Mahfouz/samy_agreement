<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\Notification;
use App\Models\Tender;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OfferReviewController extends Controller
{
    public function index(Request $request, Tender $tender): Response
    {
        $this->authorizeTender($request, $tender);
        abort_if(! $tender->offersOpened(), 403, 'لا يمكن فحص العروض قبل موعد ووقت فتح العروض.');

        $tender->load(['offers.provider:id,company_name']);

        return Inertia::render('client/Offers', [
            'tender' => $tender->only(['id', 'tender_no', 'name', 'status']),
            'offers' => $tender->offers->map(fn ($o) => [
                'id' => $o->id,
                'provider' => $o->provider?->company_name,
                'technical_file' => $o->technical_file,
                'financial_file' => $o->financial_file,
                'financial_value' => $o->financial_value,
                'technical_check' => $o->technical_check,
                'is_awarded' => $o->is_awarded,
            ])->values(),
        ]);
    }

    public function update(Request $request, Tender $tender): RedirectResponse
    {
        $this->authorizeTender($request, $tender);
        abort_if(! $tender->offersOpened(), 403, 'لا يمكن فحص العروض قبل موعد ووقت فتح العروض.');

        $data = $request->validate([
            'checks' => ['array'],
            'checks.*' => ['in:matching,not_matching,pending'],
            'award_offer_id' => ['nullable', 'integer'],
        ]);

        $offers = $tender->offers()->get();
        $checks = $data['checks'] ?? [];

        foreach ($offers as $offer) {
            $offer->technical_check = $checks[$offer->id] ?? $offer->technical_check;
            $offer->save();
        }

        $awardId = $data['award_offer_id'] ?? null;

        if ($awardId) {
            $winner = $offers->firstWhere('id', $awardId);
            abort_unless($winner, 422);
            abort_if($winner->technical_check === 'not_matching', 422, 'لا يمكن الترسية على عرض غير مطابق.');

            foreach ($offers as $offer) {
                $isWinner = $offer->id === $winner->id;
                $offer->is_awarded = $isWinner;
                $offer->status = $isWinner ? 'awarded' : 'rejected';
                $offer->save();
            }

            $tender->update(['status' => 'awarded', 'awarded_offer_id' => $winner->id]);

            Contract::firstOrCreate(
                ['tender_id' => $tender->id],
                [
                    'offer_id' => $winner->id,
                    'client_id' => $tender->client_id,
                    'provider_id' => $winner->provider_id,
                    'contract_value' => $winner->financial_value,
                    'contract_duration_months' => $tender->contract_duration_months,
                    'status' => 'awaiting_signature',
                ]
            );

            $winner->loadMissing('provider');
            $contract = Contract::where('tender_id', $tender->id)->first();
            Notification::notify(
                $winner->provider?->user_id,
                'تمت ترسية منافسة عليك',
                "تمت ترسية منافسة «{$tender->name}» على مؤسستك، يرجى توقيع العقد.",
                $contract ? "/contract/{$contract->id}" : '/provider/dashboard'
            );

            return redirect('/client/dashboard')->with('success', 'تمت الترسية وإرسال العقد بنجاح.');
        }

        if ($tender->status === 'active') {
            $tender->update(['status' => 'examination']);
        }

        return back()->with('success', 'تم حفظ نتائج فحص العروض.');
    }

    private function authorizeTender(Request $request, Tender $tender): void
    {
        abort_unless($tender->client_id === $request->user()->clientProfile?->id, 403);
    }
}
