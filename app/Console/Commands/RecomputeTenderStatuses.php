<?php

namespace App\Console\Commands;

use App\Models\Tender;
use Illuminate\Console\Command;

class RecomputeTenderStatuses extends Command
{
    protected $signature = 'tenders:recompute-status';

    protected $description = 'إعادة حساب حالة المنافسات تلقائيًا حسب التواريخ (active → examination → awarding)';

    public function handle(): int
    {
        $now = now();
        $changed = 0;

        Tender::query()
            ->whereIn('status', ['active', 'examination', 'awarding'])
            ->whereNull('awarded_offer_id')
            ->chunkById(200, function ($tenders) use ($now, &$changed) {
                foreach ($tenders as $tender) {
                    $new = $this->computeStatus($tender, $now);
                    if ($new !== $tender->status) {
                        $tender->update(['status' => $new]);
                        $changed++;
                    }
                }
            });

        $this->info("تم تحديث حالة {$changed} منافسة.");

        return self::SUCCESS;
    }

    private function computeStatus(Tender $tender, $now): string
    {
        if ($tender->offers_deadline && $now->lte($tender->offers_deadline->endOfDay())) {
            return 'active';
        }
        if ($tender->expected_award_date && $now->lte($tender->expected_award_date->endOfDay())) {
            return 'examination';
        }
        if ($tender->offers_deadline) {
            return 'awarding';
        }

        return $tender->status;
    }
}
