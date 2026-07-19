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
    private function likeContains(?string $value): string
    {
        $escaped = str_replace(['!', '%', '_'], ['!!', '!%', '!_'], (string) $value);

        return '%'.$escaped.'%';
    }

    public function index(Request $request): Response
    {
        $categoryId = $request->filled('category_id')
            ? $request->integer('category_id')
            : Category::whereNull('parent_id')->where('is_active', true)->orderBy('sort_order')->value('id');

        $tenders = Tender::query()
            ->whereNotNull('published_at')
            ->with(['client:id,company_name', 'category:id,name', 'locations.region:id,name'])
            ->withCount('offers')
            ->when($request->filled('reference_no'), fn ($query) => $query->whereRaw("reference_no LIKE ? ESCAPE '!'", [$this->likeContains($request->input('reference_no'))]))
            ->when($request->filled('tender_no'), fn ($query) => $query->whereRaw("tender_no LIKE ? ESCAPE '!'", [$this->likeContains($request->input('tender_no'))]))
            ->when($request->filled('name'), fn ($query) => $query->whereRaw("name LIKE ? ESCAPE '!'", [$this->likeContains($request->input('name'))]))
            ->when($categoryId, fn ($query) => $query->where('category_id', $categoryId))
            ->when($request->filled('type'), fn ($query) => $query->where('type', $request->string('type')))
            ->when(
                in_array($request->query('status'), ['active', 'examination', 'awarding', 'awarded'], true),
                fn ($query) => $query->where('status', $request->query('status'))
            )
            ->when($request->filled('region_id'), fn ($query) => $query->whereHas('locations', fn ($l) => $l->where('region_id', $request->integer('region_id'))))
            ->when($request->filled('price'), function ($query) use ($request) {
                match ($request->string('price')->toString()) {
                    'free' => $query->where('brochure_price', 0),
                    '1_1000' => $query->where('brochure_price', '>', 0)->where('brochure_price', '<=', 1000),
                    '1001_10000' => $query->where('brochure_price', '>', 1000)->where('brochure_price', '<=', 10000),
                    '10001_20000' => $query->where('brochure_price', '>', 10000)->where('brochure_price', '<=', 20000),
                    '20001_40000' => $query->where('brochure_price', '>', 20000)->where('brochure_price', '<=', 40000),
                    '40001_50000' => $query->where('brochure_price', '>', 40000)->where('brochure_price', '<=', 50000),
                    'gt_50000' => $query->where('brochure_price', '>', 50000),
                    default => null,
                };
            })
            ->when($request->filled('published'), function ($query) use ($request) {
                $since = match ($request->string('published')->toString()) {
                    '2d' => now()->subDays(2),
                    'week' => now()->subWeek(),
                    'month' => now()->subMonth(),
                    '3months' => now()->subMonths(3),
                    default => null,
                };
                if ($since) {
                    $query->where('published_at', '>=', $since);
                }
            })
            ->when($request->filled('deadline_from'), fn ($query) => $query->whereDate('offers_deadline', '>=', $request->input('deadline_from')))
            ->when($request->filled('deadline_to'), fn ($query) => $query->whereDate('offers_deadline', '<=', $request->input('deadline_to')))
            ->when($request->query('sort') === 'created_asc', fn ($query) => $query->oldest())
            ->when($request->query('sort') === 'open_desc', fn ($query) => $query->orderByDesc('offers_open'))
            ->when($request->query('sort') === 'open_asc', fn ($query) => $query->orderBy('offers_open'))
            ->when($request->query('sort') === 'deadline_desc', fn ($query) => $query->orderByDesc('offers_deadline'))
            ->when($request->query('sort') === 'deadline_asc', fn ($query) => $query->orderBy('offers_deadline'))
            ->when(
                ! in_array($request->query('sort'), ['created_asc', 'open_desc', 'open_asc', 'deadline_desc', 'deadline_asc'], true),
                fn ($query) => $query->latest()
            )
            ->paginate(4)
            ->withQueryString();

        return Inertia::render('public/Tenders/Index', [
            'tenders' => $tenders,
            'filters' => array_merge(
                $request->only([
                    'reference_no', 'tender_no', 'name', 'type', 'status',
                    'region_id', 'price', 'published', 'deadline_from', 'deadline_to', 'sort',
                ]),
                ['category_id' => $categoryId],
            ),
            'categories' => Category::query()->where('is_active', true)->orderBy('sort_order')->get(['id', 'name', 'parent_id']),
            'regions' => Region::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'stats' => [
                'active' => Tender::where('status', 'active')->whereNotNull('published_at')->count(),
                'awarded' => Tender::where('status', 'awarded')->whereNotNull('published_at')->count(),
                'offers' => Offer::whereHas('tender', fn ($t) => $t->whereNotNull('published_at'))->count(),
            ],
        ]);
    }

    public function show(Request $request, Tender $tender): Response
    {
        abort_if($tender->published_at === null, 404);

        $provider = $request->user()?->providerProfile;
        $canApply = $tender->offersOpen()
            && $provider
            && $provider->status === 'approved'
            && (int) $tender->category_id === (int) $provider->main_category_id;

        $tender->load([
            'client:id,company_name,bank_name,bank_beneficiary_name,bank_iban',
            'category:id,name',
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
            'canApply' => $canApply,
        ]);
    }
}
