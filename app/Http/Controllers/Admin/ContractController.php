<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ContractController extends Controller
{
    public function index(Request $request): Response
    {
        $status = $request->query('status');

        $contracts = Contract::query()
            ->with([
                'tender:id,tender_no,name',
                'client:id,company_name',
                'provider:id,company_name',
            ])
            ->when(
                in_array($status, ['awaiting_signature', 'active', 'completed', 'cancelled'], true),
                fn ($q) => $q->where('status', $status)
            )
            ->latest()
            ->paginate(15, ['id', 'tender_id', 'client_id', 'provider_id', 'contract_value', 'documentation_date', 'client_signed_at', 'provider_signed_at', 'status', 'created_at'])->withQueryString();

        return Inertia::render('admin/contracts/Index', [
            'contracts' => $contracts,
            'filter' => $status,
            'counts' => [
                'all' => Contract::count(),
                'awaiting_signature' => Contract::where('status', 'awaiting_signature')->count(),
                'active' => Contract::where('status', 'active')->count(),
                'completed' => Contract::where('status', 'completed')->count(),
                'cancelled' => Contract::where('status', 'cancelled')->count(),
            ],
        ]);
    }

    public function show(Contract $contract): Response
    {
        $contract->load([
            'tender:id,tender_no,name,contract_duration_months',
            'client:id,company_name',
            'provider:id,company_name',
            'offer:id,financial_value',
        ]);

        return Inertia::render('admin/contracts/Show', [
            'contract' => $contract,
        ]);
    }

    public function update(Request $request, Contract $contract): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:awaiting_signature,active,completed,cancelled'],
        ]);

        $contract->update($data);

        return back()->with('success', 'تم تحديث حالة العقد.');
    }
}
