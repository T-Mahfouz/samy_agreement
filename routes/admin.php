<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CityController;
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\Admin\ContentPageController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\ContractController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\InquiryController;
use App\Http\Controllers\Admin\FaqController;
use App\Http\Controllers\Admin\OfferController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\ProviderController;
use App\Http\Controllers\Admin\RegionController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\TenderController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::redirect('/', '/admin/dashboard');
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::post('categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::put('categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

        Route::get('locations', [RegionController::class, 'index'])->name('locations.index');
        Route::post('regions', [RegionController::class, 'store'])->name('regions.store');
        Route::put('regions/{region}', [RegionController::class, 'update'])->name('regions.update');
        Route::delete('regions/{region}', [RegionController::class, 'destroy'])->name('regions.destroy');
        Route::post('cities', [CityController::class, 'store'])->name('cities.store');
        Route::put('cities/{city}', [CityController::class, 'update'])->name('cities.update');
        Route::delete('cities/{city}', [CityController::class, 'destroy'])->name('cities.destroy');

        Route::get('faqs', [FaqController::class, 'index'])->name('faqs.index');
        Route::post('faqs', [FaqController::class, 'store'])->name('faqs.store');
        Route::put('faqs/{faq}', [FaqController::class, 'update'])->name('faqs.update');
        Route::delete('faqs/{faq}', [FaqController::class, 'destroy'])->name('faqs.destroy');

        Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
        Route::put('settings', [SettingController::class, 'update'])->name('settings.update');
        Route::post('test-email', [\App\Http\Controllers\Admin\TestEmailController::class, 'send'])->name('test-email');

        Route::get('tenders', [TenderController::class, 'index'])->name('tenders.index');
        Route::get('tenders/{tender}', [TenderController::class, 'show'])->name('tenders.show');
        Route::put('tenders/{tender}', [TenderController::class, 'update'])->name('tenders.update');

        Route::get('offers', [OfferController::class, 'index'])->name('offers.index');

        Route::get('contracts', [ContractController::class, 'index'])->name('contracts.index');
        Route::get('contracts/{contract}', [ContractController::class, 'show'])->name('contracts.show');
        Route::put('contracts/{contract}', [ContractController::class, 'update'])->name('contracts.update');

        Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
        Route::put('payments/{payment}', [PaymentController::class, 'update'])->name('payments.update');

        Route::get('providers', [ProviderController::class, 'index'])->name('providers.index');
        Route::get('providers/{provider}', [ProviderController::class, 'show'])->name('providers.show');
        Route::put('providers/{provider}', [ProviderController::class, 'update'])->name('providers.update');

        Route::get('messages', [ContactMessageController::class, 'index'])->name('messages.index');
        Route::put('messages/{message}', [ContactMessageController::class, 'update'])->name('messages.update');
        Route::delete('messages/{message}', [ContactMessageController::class, 'destroy'])->name('messages.destroy');

        Route::get('content', [ContentPageController::class, 'index'])->name('content.index');
        Route::post('content', [ContentPageController::class, 'store'])->name('content.store');
        Route::put('content/{content}', [ContentPageController::class, 'update'])->name('content.update');
        Route::delete('content/{content}', [ContentPageController::class, 'destroy'])->name('content.destroy');

        Route::get('clients', [ClientController::class, 'index'])->name('clients.index');
        Route::get('clients/{client}', [ClientController::class, 'show'])->name('clients.show');
        Route::put('clients/{client}', [ClientController::class, 'update'])->name('clients.update');

        Route::get('inquiries', [InquiryController::class, 'index'])->name('inquiries.index');
        Route::put('inquiries/{inquiry}', [InquiryController::class, 'update'])->name('inquiries.update');
    });
