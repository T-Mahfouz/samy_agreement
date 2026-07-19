<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PaymentController extends Controller
{
    public function index(Request $request): Response
    {
        // نوع الدفعة تبويب رئيسي دائم: كراسة الشروط أو عمولة المنصة (لا خلط بين النوعين).
        $type = in_array($request->query('type'), ['brochure_fee', 'commission'], true)
            ? $request->query('type')
            : 'brochure_fee';
        $status = $request->query('status');
        $search = trim((string) $request->query('q'));

        $base = Payment::query()
            ->where('type', $type)
            ->when(in_array($status, ['pending', 'paid', 'rejected'], true), fn ($q) => $q->where('status', $status))
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($w) use ($search) {
                    $w->whereHas('provider', fn ($p) => $p->where('company_name', 'like', "%{$search}%"))
                        ->orWhereHas('tender', fn ($t) => $t->where('tender_no', 'like', "%{$search}%")->orWhere('name', 'like', "%{$search}%"));
                });
            });

        $payments = (clone $base)
            ->with(['provider:id,company_name', 'tender:id,tender_no,name', 'reviewer:id,name'])
            ->latest()
            ->paginate(15, ['id', 'type', 'tender_id', 'provider_id', 'paid_to', 'amount', 'receipt_file', 'status', 'reviewed_by', 'reviewed_at', 'created_at'])
            ->withQueryString();

        $typeScope = Payment::where('type', $type);

        return Inertia::render('admin/payments/Index', [
            'payments' => $payments,
            'filters' => ['status' => $status, 'type' => $type, 'q' => $search],
            'counts' => [
                'all' => (clone $typeScope)->count(),
                'pending' => (clone $typeScope)->where('status', 'pending')->count(),
                'paid' => (clone $typeScope)->where('status', 'paid')->count(),
                'rejected' => (clone $typeScope)->where('status', 'rejected')->count(),
            ],
            'typeCounts' => [
                'brochure_fee' => Payment::where('type', 'brochure_fee')->count(),
                'commission' => Payment::where('type', 'commission')->count(),
            ],
            'totalAmount' => (float) (clone $base)->sum('amount'),
        ]);
    }

    public function update(Request $request, Payment $payment): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:pending,paid,rejected'],
        ]);

        $reviewed = $data['status'] !== 'pending';

        $payment->update([
            'status' => $data['status'],
            'reviewed_by' => $reviewed ? $request->user()->id : null,
            'reviewed_at' => $reviewed ? now() : null,
        ]);

        $label = match ($data['status']) {
            'paid' => 'تم اعتماد الدفعة (مدفوعة).',
            'rejected' => 'تم رفض الدفعة.',
            default => 'تم إرجاع الدفعة إلى قيد المراجعة.',
        };

        return back()->with('success', $label);
    }
}
