<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Concerns\StoresUploads;
use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Offer;
use App\Models\Payment;
use App\Models\Tender;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OfferController extends Controller
{
    use StoresUploads;

    /** تقديم عرض على منافسة */
    public function store(Request $request, Tender $tender): RedirectResponse
    {
        $provider = $request->user()->providerProfile;
        abort_unless($provider, 403);

        if ($provider->status !== 'approved') {
            return back()->with('error', 'لا يمكنك تقديم عرض قبل اعتماد حسابك من الإدارة.');
        }
        // يمنع التقديم بعد انتهاء آخر موعد لتقديم العروض (أو إذا لم تعد المنافسة نشطة).
        if (! $tender->offersOpen()) {
            return back()->with('error', 'انتهت فترة تقديم العروض لهذه المنافسة.');
        }
        if ($tender->offers()->where('provider_id', $provider->id)->exists()) {
            return back()->with('error', 'لقد قدّمت عرضًا على هذه المنافسة بالفعل.');
        }

        // docx أصله zip → mimes:docx يفشل على كثير من السيرفرات؛ نتحقق بالامتداد + أنواع محتوى واسعة
        $offerFileRules = [
            'required', 'file', 'extensions:pdf,doc,docx,jpg,jpeg,png,webp',
            'mimetypes:application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/zip,application/x-zip-compressed,application/x-cfb,image/jpeg,image/png,image/webp',
            'max:10240',
        ];

        $data = $request->validate([
            'technical_file' => $offerFileRules,
            'financial_file' => $offerFileRules,
            'financial_value' => ['required', 'numeric', 'min:0'],
            'declaration_accepted' => ['accepted'],
        ], [
            'technical_file.extensions' => 'يجب أن يكون ملف العرض الفني من نوع PDF أو Word أو صورة.',
            'technical_file.mimetypes' => 'يجب أن يكون ملف العرض الفني من نوع PDF أو Word أو صورة.',
            'financial_file.extensions' => 'يجب أن يكون ملف العرض المالي من نوع PDF أو Word أو صورة.',
            'financial_file.mimetypes' => 'يجب أن يكون ملف العرض المالي من نوع PDF أو Word أو صورة.',
        ], [
            'technical_file' => 'ملف العرض الفني',
            'financial_file' => 'ملف العرض المالي',
            'financial_value' => 'قيمة العرض المالي',
            'declaration_accepted' => 'الإقرار والتعهد',
        ]);

        $tender->offers()->create([
            'provider_id' => $provider->id,
            // ملفات العروض سرّية → قرص خاص (تُحمَّل عبر مسار مُصرّح به فقط)
            'technical_file' => $this->storeUpload($request->file('technical_file'), "offers/{$tender->id}", 'local'),
            'financial_file' => $this->storeUpload($request->file('financial_file'), "offers/{$tender->id}", 'local'),
            'financial_value' => $data['financial_value'],
            'technical_check' => 'pending',
            'declaration_accepted' => true,
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        Notification::notify(
            $tender->client?->user_id,
            'عرض جديد على منافستك',
            "{$provider->company_name} قدّم عرضًا على «{$tender->name}»",
            "/client/tenders/{$tender->id}/offers"
        );

        return back()->with('success', 'تم إرسال عرضك بنجاح.');
    }

    /** رفع إيصال سداد قيمة كراسة الشروط */
    public function brochurePayment(Request $request, Tender $tender): RedirectResponse
    {
        $provider = $request->user()->providerProfile;
        abort_unless($provider, 403);

        // لا فائدة من طلب كراسة الشروط بعد انتهاء آخر موعد لتقديم العروض
        if (! $tender->offersOpen()) {
            return back()->with('error', 'انتهت فترة طلب كراسة الشروط لهذه المنافسة.');
        }

        $request->validate([
            'receipt_file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:5120'],
        ], [], ['receipt_file' => 'إيصال التحويل']);

        Payment::updateOrCreate(
            ['type' => 'brochure_fee', 'tender_id' => $tender->id, 'provider_id' => $provider->id],
            [
                'paid_to' => 'client',
                'amount' => $tender->brochure_price,
                'receipt_file' => $this->storeUpload($request->file('receipt_file'), "receipts/brochure/{$tender->id}"),
                'status' => 'pending',
                'reviewed_by' => null,
                'reviewed_at' => null,
            ]
        );

        Notification::notify(
            $tender->client?->user_id,
            'طلب كراسة شروط جديد',
            "{$provider->company_name} رفع إيصال سداد كراسة الشروط لمنافسة «{$tender->name}»",
            "/client/tenders/{$tender->id}/brochure-requests"
        );

        return back()->with('success', 'تم رفع إيصال قيمة كراسة الشروط، بانتظار الاعتماد.');
    }

    /** رفع إيصال سداد عمولة المنصة */
    public function commissionPayment(Request $request, Offer $offer): RedirectResponse
    {
        $provider = $request->user()->providerProfile;
        abort_unless($provider && $offer->provider_id === $provider->id, 403);
        abort_unless($offer->is_awarded, 403);

        $request->validate([
            'receipt_file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:5120'],
        ], [], ['receipt_file' => 'إيصال التحويل']);

        $amount = round((float) $offer->financial_value * (float) $offer->tender->commission_rate / 100, 2);

        Payment::updateOrCreate(
            ['type' => 'commission', 'offer_id' => $offer->id, 'provider_id' => $provider->id],
            [
                'tender_id' => $offer->tender_id,
                'paid_to' => 'platform',
                'amount' => $amount,
                'receipt_file' => $this->storeUpload($request->file('receipt_file'), "receipts/commission/{$offer->id}"),
                'status' => 'pending',
                'reviewed_by' => null,
                'reviewed_at' => null,
            ]
        );

        Notification::notifyAdmins(
            'إيصال عمولة جديد بانتظار المراجعة',
            "{$provider->company_name} رفع إيصال سداد عمولة المنصة",
            '/admin/payments'
        );

        return back()->with('success', 'تم رفع إيصال عمولة المنصة، بانتظار الاعتماد.');
    }
}
