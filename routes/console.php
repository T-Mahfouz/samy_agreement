<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// إعادة حساب حالة المنافسات كل ساعة
Schedule::command('tenders:recompute-status')->hourly();

// تفريغ طابور البريد كل دقيقة (يعتمد على كرون schedule:run الموجود على الاستضافة)
Schedule::command('queue:work --stop-when-empty --max-time=50')
    ->everyMinute()
    ->withoutOverlapping();
