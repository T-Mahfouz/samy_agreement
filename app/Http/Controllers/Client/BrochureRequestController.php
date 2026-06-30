<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\Tender;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BrochureRequestController extends Controller
{
    public function index(Request $request, Tender $tender): Response
    {
        $this->authorizeTender($request, $tender);

        $requests = Payment::query()
            ->where('tender_id', $tender->id)
            ->where('type', 'brochure_fee')
            ->with('provider:id,company_name')
            ->latest()
            ->paginate(15)
            ->withQueryString()
            ->through(fn ($p) => [
                'id' => $p->id,
                'provider' => $p->provider?->company_name,
                'amount' => $p->amount,
                'receipt_file' => $p->receipt_file,
                'status' => $p->status,
            ]);

        return Inertia::render('client/BrochureRequests', [
            'tender' => $tender->only(['id', 'tender_no', 'name']),
            'requests' => $requests,
        ]);
    }

    public function update(Request $request, Tender $tender): RedirectResponse
    {
        $this->authorizeTender($request, $tender);

        $data = $request->validate([
            'decisions' => ['array'],
            'decisions.*' => ['in:paid,rejected,pending'],
        ]);

        foreach ($data['decisions'] ?? [] as $paymentId => $status) {
            $payment = Payment::where('id', $paymentId)
                ->where('tender_id', $tender->id)
                ->where('type', 'brochure_fee')
                ->with('provider:id,user_id')
                ->first();

            if (! $payment || $payment->status === $status) {
                continue; // لا تغيير → لا إشعار مكرّر
            }

            $payment->update([
                'status' => $status,
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
            ]);

            // إشعار المورّد بقرار العميل
            if (in_array($status, ['paid', 'rejected'], true) && $payment->provider?->user_id) {
                $approved = $status === 'paid';
                Notification::notify(
                    $payment->provider->user_id,
                    $approved ? 'تم اعتماد سداد كراسة الشروط' : 'تم رفض طلب كراسة الشروط',
                    $approved
                        ? "تم اعتماد سدادك لكراسة الشروط لمنافسة «{$tender->name}»، ويمكنك تحميلها الآن من صفحة كراسات الشروط."
                        : "تم رفض طلب كراسة الشروط لمنافسة «{$tender->name}». يرجى التواصل مع الجهة المستفيدة.",
                    '/provider/booklets'
                );
            }
        }

        return back()->with('success', 'تم حفظ قرارات طلبات كراسة الشروط.');
    }

    private function authorizeTender(Request $request, Tender $tender): void
    {
        abort_unless($tender->client_id === $request->user()->clientProfile?->id, 403);
    }
}
