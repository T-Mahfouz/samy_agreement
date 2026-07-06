<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Offer;
use App\Models\Payment;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $provider = $request->user()->providerProfile;
        $providerId = $provider?->id;

        $commissionPayments = Payment::query()
            ->where('provider_id', $providerId)
            ->where('type', 'commission')
            ->get()
            ->keyBy('offer_id');

        $map = function (Offer $o) use ($commissionPayments) {
            $t = $o->tender;
            $commissionAmount = $o->financial_value !== null && $t
                ? round((float) $o->financial_value * (float) $t->commission_rate / 100, 2)
                : null;
            $pay = $commissionPayments->get($o->id);

            return [
                'id' => $o->id,
                'tender' => $t,
                'technical_file' => $o->technical_file,
                'financial_file' => $o->financial_file,
                'financial_value' => $o->financial_value,
                'status' => $o->status,
                'is_awarded' => $o->is_awarded,
                'commission_amount' => $commissionAmount,
                'commission_status' => $pay?->status,
                'contract' => $t?->contract,
            ];
        };

        $with = [
            'tender:id,tender_no,reference_no,name,client_id,brochure_price,contract_duration_months,commission_rate,published_at,offers_deadline,offers_deadline_hijri,offers_deadline_time,offers_open,offers_open_hijri,offers_open_time,status',
            'tender.client:id,company_name',
            'tender.contract:id,tender_id,status',
        ];

        $active = Offer::where('provider_id', $providerId)->where('status', '!=', 'awarded')
            ->with($with)->latest()->paginate(10, ['*'], 'active_page')->withQueryString()->through($map);

        $awarded = Offer::where('provider_id', $providerId)->where('status', 'awarded')
            ->with($with)->latest()->paginate(10, ['*'], 'awarded_page')->withQueryString()->through($map);

        return Inertia::render('provider/Dashboard', [
            'providerName' => $provider?->company_name ?? $request->user()->name,
            'providerStatus' => $provider?->status,
            'activeOffers' => $active,
            'awardedOffers' => $awarded,
            'platformBank' => [
                'beneficiary' => SystemSetting::get('platform_bank_beneficiary', 'مؤسسة اتفاق'),
                'bank' => SystemSetting::get('platform_bank_name', ''),
                'iban' => SystemSetting::get('platform_bank_iban', ''),
            ],
            'stats' => [
                'applied' => Offer::where('provider_id', $providerId)->count(),
                'awarded' => Offer::where('provider_id', $providerId)->where('status', 'awarded')->count(),
                'contracts' => Offer::where('provider_id', $providerId)->whereHas('tender.contract')->count(),
            ],
        ]);
    }
}
