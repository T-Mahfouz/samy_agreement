<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Offer;
use App\Models\Region;
use App\Models\Tender;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PublicTenderController extends Controller
{
    public function index(Request $request): Response
    {
        // المورّد يرى منافسات قطاعه فقط (إن كان له قطاع رئيسي محدّد).
        $user = $request->user();
        $lockedCategoryId = $user && $user->role === 'provider'
            ? $user->providerProfile?->main_category_id
            : null;

        $tenders = Tender::query()
            ->whereNotNull('published_at')
            ->with(['client:id,company_name', 'category:id,name', 'locations.region:id,name'])
            ->withCount('offers')
            // بحث نصي
            ->when($request->filled('q'), function ($query) use ($request) {
                $q = $request->string('q');
                $query->where(function ($sub) use ($q) {
                    $sub->where('name', 'like', "%{$q}%")
                        ->orWhere('reference_no', 'like', "%{$q}%")
                        ->orWhere('tender_no', 'like', "%{$q}%");
                });
            })
            // قطاع المورّد له الأولوية ويتجاوز أي فلتر قطاع من الطلب
            ->when($lockedCategoryId, fn ($query) => $query->where('category_id', $lockedCategoryId))
            ->when(! $lockedCategoryId && $request->filled('category_id'), fn ($query) => $query->where('category_id', $request->integer('category_id')))
            ->when($request->filled('subcategory_id'), fn ($query) => $query->where('subcategory_id', $request->integer('subcategory_id')))
            ->when($request->filled('type'), fn ($query) => $query->where('type', $request->string('type')))
            ->when(
                in_array($request->query('status'), ['active', 'examination', 'awarding', 'awarded'], true),
                fn ($query) => $query->where('status', $request->query('status'))
            )
            ->when($request->filled('region_id'), fn ($query) => $query->whereHas('locations', fn ($l) => $l->where('region_id', $request->integer('region_id'))))
            ->when($request->filled('price'), function ($query) use ($request) {
                match ($request->string('price')->toString()) {
                    'free' => $query->where('brochure_price', 0),
                    'lt_1000' => $query->whereBetween('brochure_price', [0.01, 999.99]),
                    '1000_10000' => $query->whereBetween('brochure_price', [1000, 10000]),
                    'gt_10000' => $query->where('brochure_price', '>', 10000),
                    default => null,
                };
            })
            ->when($request->query('sort') === 'deadline', fn ($query) => $query->orderBy('offers_deadline'))
            ->when($request->query('sort') === 'oldest', fn ($query) => $query->oldest('published_at'))
            ->when(! in_array($request->query('sort'), ['deadline', 'oldest'], true), fn ($query) => $query->latest('published_at'))
            ->paginate(9)
            ->withQueryString();

        return Inertia::render('public/Tenders/Index', [
            'tenders' => $tenders,
            'filters' => $request->only(['q', 'category_id', 'subcategory_id', 'type', 'status', 'region_id', 'price', 'sort']),
            'lockedCategoryId' => $lockedCategoryId,
            'categories' => Category::query()->where('is_active', true)->orderBy('sort_order')->get(['id', 'name', 'parent_id']),
            'regions' => Region::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            // الإحصائيات تطابق فلتر العرض (المنشورة فقط + قطاع المورّد إن وُجد)
            'stats' => [
                'active' => Tender::where('status', 'active')->whereNotNull('published_at')->when($lockedCategoryId, fn ($q) => $q->where('category_id', $lockedCategoryId))->count(),
                'awarded' => Tender::where('status', 'awarded')->whereNotNull('published_at')->when($lockedCategoryId, fn ($q) => $q->where('category_id', $lockedCategoryId))->count(),
                'offers' => Offer::whereHas('tender', fn ($t) => $t->whereNotNull('published_at')->when($lockedCategoryId, fn ($q) => $q->where('category_id', $lockedCategoryId)))->count(),
            ],
        ]);
    }

    public function show(Tender $tender): Response
    {
        abort_if($tender->published_at === null, 404);

        $tender->load([
            'client:id,company_name,bank_name,bank_beneficiary_name,bank_iban',
            'category:id,name',
            'subcategory:id,name',
            'locations.region:id,name',
            'locations.city:id,name',
            'offers' => fn ($q) => $q->where(fn ($w) => $w->where('technical_check', '!=', 'pending')->orWhere('is_awarded', true)),
            'offers.provider:id,company_name',
            'awardedOffer.provider:id,company_name',
        ]);

        return Inertia::render('public/Tenders/Show', [
            'tender' => $tender,
            'offersCount' => $tender->offers()->count(),
            'offersOpen' => $tender->offersOpen(),
        ]);
    }
}
