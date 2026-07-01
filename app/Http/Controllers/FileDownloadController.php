<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\ProviderDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * يقدّم ملفات القرص العام (إيصالات السداد، مستندات المورّد) عبر مسارات مؤمّنة
 * بدل الاعتماد على رابط storage الرمزي — يعمل على أي استضافة وأكثر أمانًا.
 */
class FileDownloadController extends Controller
{
    /** إيصال سداد (كراسة الشروط أو العمولة): المورّد صاحبه، أو عميل المنافسة، أو الأدمن. */
    public function paymentReceipt(Request $request, Payment $payment)
    {
        $user = $request->user();
        $payment->loadMissing('provider', 'tender');

        $isOwnerProvider = $user->providerProfile && $payment->provider_id === $user->providerProfile->id;
        $isTenderClient = $user->clientProfile && $payment->tender && $payment->tender->client_id === $user->clientProfile->id;
        $isAdmin = $user->role === 'admin';

        abort_unless($isOwnerProvider || $isTenderClient || $isAdmin, 403);
        abort_if(! $payment->receipt_file || ! Storage::disk('public')->exists($payment->receipt_file), 404, 'الملف غير متوفر.');

        return Storage::disk('public')->response($payment->receipt_file);
    }

    /** مستند مورّد: المورّد صاحبه، أو الأدمن. */
    public function providerDocument(Request $request, ProviderDocument $document)
    {
        $user = $request->user();

        $isOwner = $user->providerProfile && $document->provider_id === $user->providerProfile->id;
        $isAdmin = $user->role === 'admin';

        abort_unless($isOwner || $isAdmin, 403);
        abort_if(! $document->file_path || ! Storage::disk('public')->exists($document->file_path), 404, 'الملف غير متوفر.');

        return Storage::disk('public')->response($document->file_path);
    }
}
