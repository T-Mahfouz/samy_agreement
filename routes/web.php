<?php

use App\Http\Controllers\PublicTenderController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// الموقع العام
Route::get('/', [PublicTenderController::class, 'index'])->name('home');
Route::get('/tenders/{tender}', [PublicTenderController::class, 'show'])->name('tenders.show');
Route::get('/about', [\App\Http\Controllers\PublicPageController::class, 'about'])->name('about');
Route::get('/faqs', [\App\Http\Controllers\PublicPageController::class, 'faqs'])->name('faqs');
Route::get('/contact', [\App\Http\Controllers\PublicPageController::class, 'contact'])->name('contact');
Route::post('/contact', [\App\Http\Controllers\PublicPageController::class, 'contactStore'])->name('contact.store');
Route::get('/terms', [\App\Http\Controllers\PublicPageController::class, 'terms'])->name('terms');
Route::get('/privacy', [\App\Http\Controllers\PublicPageController::class, 'privacy'])->name('privacy');

// إعادة توجيه حسب الدور
Route::get('dashboard', function () {
    return redirect(auth()->user()->dashboardPath());
})->middleware(['auth'])->name('dashboard');

// العقد الإلكتروني (الطرفان + الأدمن)
Route::middleware('auth')->group(function () {
    Route::get('contract/{contract}', [\App\Http\Controllers\ContractController::class, 'show'])->name('contract.show');
    Route::post('contract/{contract}/sign', [\App\Http\Controllers\ContractController::class, 'sign'])->name('contract.sign');

    // الإشعارات
    Route::put('notifications/{notification}/read', [\App\Http\Controllers\NotificationController::class, 'read'])->name('notifications.read');
    Route::put('notifications/read-all', [\App\Http\Controllers\NotificationController::class, 'readAll'])->name('notifications.readAll');

    // تحميل ملفات العروض (المورّد صاحب العرض / عميل المنافسة / الأدمن)
    Route::get('offers/{offer}/files/{type}', [\App\Http\Controllers\OfferFileController::class, 'download'])
        ->whereIn('type', ['technical', 'financial'])
        ->name('offers.file.download');

    // إيصالات السداد ومستندات المورّد (بدل رابط storage الرمزي)
    Route::get('payments/{payment}/receipt', [\App\Http\Controllers\FileDownloadController::class, 'paymentReceipt'])->name('payments.receipt');
    Route::get('provider-documents/{document}/download', [\App\Http\Controllers\FileDownloadController::class, 'providerDocument'])->name('provider.documents.download');
});

// بوابة العميل
Route::middleware(['auth', 'role:client'])->prefix('client')->name('client.')->group(function () {
    Route::get('dashboard', [\App\Http\Controllers\Client\DashboardController::class, 'index'])->name('dashboard');
    Route::get('tenders/create', [\App\Http\Controllers\Client\TenderController::class, 'create'])->name('tenders.create');
    Route::post('tenders', [\App\Http\Controllers\Client\TenderController::class, 'store'])->name('tenders.store');
    Route::get('tenders/{tender}/edit', [\App\Http\Controllers\Client\TenderController::class, 'edit'])->name('tenders.edit');
    Route::put('tenders/{tender}', [\App\Http\Controllers\Client\TenderController::class, 'update'])->name('tenders.update');
    Route::put('tenders/{tender}/cancel', [\App\Http\Controllers\Client\DashboardController::class, 'cancelTender'])->name('tenders.cancel');
    Route::get('tenders/{tender}/offers', [\App\Http\Controllers\Client\OfferReviewController::class, 'index'])->name('tenders.offers');
    Route::put('tenders/{tender}/offers', [\App\Http\Controllers\Client\OfferReviewController::class, 'update'])->name('tenders.offers.update');
    Route::get('tenders/{tender}/brochure-requests', [\App\Http\Controllers\Client\BrochureRequestController::class, 'index'])->name('brochure.requests');
    Route::put('tenders/{tender}/brochure-requests', [\App\Http\Controllers\Client\BrochureRequestController::class, 'update'])->name('brochure.requests.update');
    Route::get('profile', [\App\Http\Controllers\Client\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [\App\Http\Controllers\Client\ProfileController::class, 'update'])->name('profile.update');
});

// بوابة المورّد
Route::middleware(['auth', 'role:provider'])->prefix('provider')->name('provider.')->group(function () {
    Route::get('dashboard', [\App\Http\Controllers\Provider\DashboardController::class, 'index'])->name('dashboard');
    Route::post('tenders/{tender}/offer', [\App\Http\Controllers\Provider\OfferController::class, 'store'])->name('offers.store');
    Route::post('tenders/{tender}/brochure-payment', [\App\Http\Controllers\Provider\OfferController::class, 'brochurePayment'])->name('brochure.pay');
    Route::post('offers/{offer}/commission-payment', [\App\Http\Controllers\Provider\OfferController::class, 'commissionPayment'])->name('commission.pay');
    Route::get('booklets', [\App\Http\Controllers\Provider\BookletController::class, 'index'])->name('booklets');
    Route::get('tenders/{tender}/brochure/download', [\App\Http\Controllers\Provider\BookletController::class, 'download'])->name('brochure.download');
    Route::get('profile', [\App\Http\Controllers\Provider\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [\App\Http\Controllers\Provider\ProfileController::class, 'update'])->name('profile.update');
});

require __DIR__.'/settings.php';
require __DIR__.'/admin.php';
require __DIR__.'/auth.php';
