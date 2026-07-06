<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ContractController extends Controller
{
    public function show(Request $request, Contract $contract): Response
    {
        $side = $this->side($request, $contract);
        abort_if($side === null && ! $request->user()->isAdmin(), 403);

        $contract->load([
            'tender:id,tender_no,reference_no,name,contract_duration_months',
            'client:id,company_name',
            'provider:id,company_name',
        ]);

        return Inertia::render('contract/Show', [
            'contract' => [
                'id' => $contract->id,
                'tender_no' => $contract->tender?->tender_no,
                'reference_no' => $contract->tender?->reference_no,
                'tender_name' => $contract->tender?->name,
                'client_name' => $contract->client?->company_name,
                'provider_name' => $contract->provider?->company_name,
                'contract_value' => $contract->contract_value,
                'contract_duration_months' => $contract->contract_duration_months,
                'documentation_date' => $contract->documentation_date,
                'status' => $contract->status,
                'client_signed_at' => $contract->client_signed_at,
                'client_signed_ip' => $contract->client_signed_ip,
                'provider_signed_at' => $contract->provider_signed_at,
                'provider_signed_ip' => $contract->provider_signed_ip,
            ],
            'side' => $side,
            'canSign' => $side !== null
                && $contract->status === 'awaiting_signature'
                && ($side === 'client' ? $contract->client_signed_at === null : $contract->provider_signed_at === null),
        ]);
    }

    public function sign(Request $request, Contract $contract): RedirectResponse
    {
        $side = $this->side($request, $contract);
        abort_if($side === null, 403);
        abort_if($contract->status !== 'awaiting_signature', 422);

        if ($side === 'client') {
            abort_unless($contract->client_signed_at === null, 422);
            $contract->client_signed_at = now();
            $contract->client_signed_ip = $request->ip();
        } else {
            abort_unless($contract->provider_signed_at === null, 422);
            $contract->provider_signed_at = now();
            $contract->provider_signed_ip = $request->ip();
        }

        if ($contract->client_signed_at && $contract->provider_signed_at) {
            $contract->status = 'active';
            if (! $contract->documentation_date) {
                $contract->documentation_date = now()->toDateString();
            }
        }

        $contract->save();

        $otherUserId = $side === 'client' ? $contract->provider?->user_id : $contract->client?->user_id;
        if ($contract->status === 'active') {
            Notification::notify($contract->client?->user_id, 'تم تفعيل العقد', 'تم توقيع العقد من الطرفين وأصبح ساريًا.', "/contract/{$contract->id}");
            Notification::notify($contract->provider?->user_id, 'تم تفعيل العقد', 'تم توقيع العقد من الطرفين وأصبح ساريًا.', "/contract/{$contract->id}");
        } else {
            Notification::notify($otherUserId, 'بانتظار توقيعك على العقد', 'وقّع الطرف الآخر على العقد، بانتظار توقيعك.', "/contract/{$contract->id}");
        }

        return back()->with('success', 'تم توقيع العقد إلكترونيًا.');
    }

    private function side(Request $request, Contract $contract): ?string
    {
        $user = $request->user();
        if ($contract->client?->user_id === $user->id) {
            return 'client';
        }
        if ($contract->provider?->user_id === $user->id) {
            return 'provider';
        }

        return null;
    }
}
