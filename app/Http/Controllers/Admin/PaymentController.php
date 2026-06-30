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
        $status = $request->query('status');
        $type = $request->query('type');

        $payments = Payment::query()
            ->with([
                'provider:id,company_name',
                'tender:id,tender_no,name',
                'reviewer:id,name',
            ])
            ->when(in_array($status, ['pending', 'paid', 'rejected'], true), fn ($q) => $q->where('status', $status))
            ->when(in_array($type, ['brochure_fee', 'commission'], true), fn ($q) => $q->where('type', $type))
            ->latest()
            ->paginate(15, ['id', 'type', 'tender_id', 'provider_id', 'paid_to', 'amount', 'receipt_file', 'status', 'reviewed_by', 'reviewed_at', 'created_at'])->withQueryString();

        return Inertia::render('admin/payments/Index', [
            'payments' => $payments,
            'filters' => ['status' => $status, 'type' => $type],
            'counts' => [
                'all' => Payment::count(),
                'pending' => Payment::where('status', 'pending')->count(),
                'paid' => Payment::where('status', 'paid')->count(),
                'rejected' => Payment::where('status', 'rejected')->count(),
            ],
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
