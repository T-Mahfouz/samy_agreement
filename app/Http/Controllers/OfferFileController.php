<?php

namespace App\Http\Controllers;

use App\Models\Offer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OfferFileController extends Controller
{
    public function download(Request $request, Offer $offer, string $type): StreamedResponse
    {
        abort_unless(in_array($type, ['technical', 'financial'], true), 404);

        $user = $request->user();
        $offer->loadMissing('tender');

        $isOwnerProvider = $user->providerProfile && $offer->provider_id === $user->providerProfile->id;
        $isTenderClient = $user->clientProfile && $offer->tender && $offer->tender->client_id === $user->clientProfile->id;
        $isAdmin = $user->role === 'admin';

        abort_unless($isOwnerProvider || $isTenderClient || $isAdmin, 403);

        $path = $type === 'technical' ? $offer->technical_file : $offer->financial_file;

        abort_if(! $path || ! Storage::disk('local')->exists($path), 404, 'الملف غير متوفر.');

        $label = $type === 'technical' ? 'العرض-الفني' : 'العرض-المالي';
        $ext = pathinfo($path, PATHINFO_EXTENSION);
        $filename = $label.'-'.($offer->tender?->tender_no ?? $offer->id).($ext ? '.'.$ext : '');

        // ?inline=1 يعرض الملف داخل المتصفح (معاينة)؛ الافتراضي تنزيل. الصلاحيات واحدة في الحالتين.
        return $request->boolean('inline')
            ? Storage::disk('local')->response($path, $filename)
            : Storage::disk('local')->download($path, $filename);
    }
}
