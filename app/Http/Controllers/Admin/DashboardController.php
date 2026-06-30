<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\Contract;
use App\Models\Offer;
use App\Models\Payment;
use App\Models\ClientProfile;
use App\Models\ProviderProfile;
use App\Models\Tender;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $stats = [
            'tenders' => [
                'total' => Tender::count(),
                'active' => Tender::where('status', 'active')->count(),
                'examination' => Tender::where('status', 'examination')->count(),
                'awarding' => Tender::where('status', 'awarding')->count(),
                'awarded' => Tender::where('status', 'awarded')->count(),
                'cancelled' => Tender::where('status', 'cancelled')->count(),
            ],
            'offers' => Offer::count(),
            'clients' => ClientProfile::count(),
            'providers' => [
                'total' => ProviderProfile::count(),
                'pending' => ProviderProfile::where('status', 'pending')->count(),
            ],
            'contracts' => [
                'total' => Contract::count(),
                'awaiting_signature' => Contract::where('status', 'awaiting_signature')->count(),
                'active' => Contract::where('status', 'active')->count(),
            ],
            'payments' => [
                'pending' => Payment::where('status', 'pending')->count(),
                'brochure_pending' => Payment::where('status', 'pending')->where('type', 'brochure_fee')->count(),
                'commission_pending' => Payment::where('status', 'pending')->where('type', 'commission')->count(),
            ],
            'messages_new' => ContactMessage::where('status', 'new')->count(),
        ];

        $recentTenders = Tender::query()
            ->with('client:id,company_name')
            ->latest()
            ->take(5)
            ->get(['id', 'tender_no', 'name', 'status', 'client_id', 'created_at']);

        $pendingProviders = ProviderProfile::query()
            ->where('status', 'pending')
            ->latest()
            ->take(5)
            ->get(['id', 'company_name', 'commercial_register_no', 'created_at']);

        $pendingPayments = Payment::query()
            ->with(['provider:id,company_name', 'tender:id,tender_no,name'])
            ->where('status', 'pending')
            ->latest()
            ->take(5)
            ->get(['id', 'type', 'amount', 'provider_id', 'tender_id', 'created_at']);

        return Inertia::render('admin/Dashboard', [
            'stats' => $stats,
            'recentTenders' => $recentTenders,
            'pendingProviders' => $pendingProviders,
            'pendingPayments' => $pendingPayments,
        ]);
    }
}
