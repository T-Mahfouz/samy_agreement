<?php

namespace App\Http\Controllers\Concerns;

use App\Models\ClientProfile;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\ProviderProfile;
use App\Models\Tender;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * حذف نهائي (hard delete) للسجلات مع تنظيف الملفات على القرص.
 * صفوف قاعدة البيانات المرتبطة تُحذف تلقائيًا عبر cascadeOnDelete المعرّف في الجداول؛
 * هنا نتكفّل بحذف الملفات (لا تُنظَّف بالـ cascade) داخل معاملة (transaction).
 */
trait ErasesRecords
{
    /** يحذف ملفات منافسة (الكراسة + ملفات العروض + إيصالات المدفوعات). */
    protected function eraseTenderFiles(Tender $tender): void
    {
        if ($tender->brochure_file) {
            Storage::disk('public')->delete($tender->brochure_file);
        }

        foreach ($tender->offers()->get(['id', 'technical_file', 'financial_file']) as $offer) {
            foreach (array_filter([$offer->technical_file, $offer->financial_file]) as $file) {
                Storage::disk('local')->delete($file);
            }
        }

        foreach ($tender->payments()->get(['id', 'receipt_file']) as $payment) {
            if ($payment->receipt_file) {
                Storage::disk('public')->delete($payment->receipt_file);
            }
        }
    }

    /** حذف منافسة نهائيًا (والعروض/المدفوعات/العقد/المواقع/الاستفسارات عبر الـ cascade). */
    protected function eraseTender(Tender $tender): void
    {
        DB::transaction(function () use ($tender) {
            $this->eraseTenderFiles($tender);
            Notification::where('link', 'like', "%/tenders/{$tender->id}/%")->delete();
            $tender->delete();
        });
    }

    /** حذف مورّد وحسابه نهائيًا (عروضه/مدفوعاته/مستنداته/عقوده عبر الـ cascade). */
    protected function eraseProvider(ProviderProfile $provider): void
    {
        DB::transaction(function () use ($provider) {
            foreach ($provider->documents()->get(['id', 'file_path']) as $doc) {
                if ($doc->file_path) {
                    Storage::disk('public')->delete($doc->file_path);
                }
            }

            $offers = $provider->offers()->get(['id', 'technical_file', 'financial_file']);
            foreach ($offers as $offer) {
                foreach (array_filter([$offer->technical_file, $offer->financial_file]) as $file) {
                    Storage::disk('local')->delete($file);
                }
            }

            foreach (Payment::where('provider_id', $provider->id)->get(['id', 'receipt_file']) as $payment) {
                if ($payment->receipt_file) {
                    Storage::disk('public')->delete($payment->receipt_file);
                }
            }

            // منافسات رُسّيت على عروض هذا المورّد ترجع لمرحلة الفحص (فقد الفائز).
            $offerIds = $offers->pluck('id');
            if ($offerIds->isNotEmpty()) {
                Tender::whereIn('awarded_offer_id', $offerIds)->update(['awarded_offer_id' => null, 'status' => 'examination']);
            }

            $userId = $provider->user_id;
            $provider->delete();
            User::whereKey($userId)->delete();
        });
    }

    /** حذف مستفيد وحسابه نهائيًا (ومنافساته وكل ما يتبعها عبر الـ cascade). */
    protected function eraseClient(ClientProfile $client): void
    {
        DB::transaction(function () use ($client) {
            foreach ($client->tenders()->get() as $tender) {
                $this->eraseTenderFiles($tender);
                Notification::where('link', 'like', "%/tenders/{$tender->id}/%")->delete();
            }

            $userId = $client->user_id;
            $client->delete();
            User::whereKey($userId)->delete();
        });
    }
}
