<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Region;
use App\Models\SystemSetting;
use App\Models\Tender;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TenderController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('client/CreateTender', $this->formData());
    }

    public function store(Request $request): RedirectResponse
    {
        $client = $request->user()->clientProfile;
        abort_unless($client, 403);

        $data = $this->validateData($request);
        $booklet = $request->hasFile('booklet_file')
            ? $request->file('booklet_file')->store('brochures', 'public')
            : null;

        $tender = Tender::create(array_merge($this->mapFields($data), [
            'client_id' => $client->id,
            'tender_no' => $this->uniqueNumber('tender_no', '60'),
            'serial_no' => $this->uniqueNumber('serial_no', '2609'),
            'reference_no' => $this->uniqueNumber('reference_no', '26043'),
            'brochure_file' => $booklet,
            'submission_method' => 'ملف للعرض الفني و ملف للعرض المالي',
            'commission_rate' => (float) SystemSetting::get('default_commission_rate', 1),
            'status' => 'active',
            'published_at' => now(),
        ]));

        $this->syncLocation($tender, $data);

        return redirect('/client/dashboard')->with('success', 'تم نشر المنافسة بنجاح.');
    }

    public function edit(Request $request, Tender $tender): Response
    {
        $this->authorizeOwner($request, $tender);
        $tender->load('locations');

        return Inertia::render('client/CreateTender', array_merge($this->formData(), [
            'tender' => array_merge($tender->toArray(), [
                'locations' => $tender->locations->map(fn ($l) => [
                    'region_id' => $l->region_id,
                    'city_id' => $l->city_id,
                ])->values()->all(),
            ]),
        ]));
    }

    public function update(Request $request, Tender $tender): RedirectResponse
    {
        $this->authorizeOwner($request, $tender);

        $data = $this->validateData($request);
        $fields = $this->mapFields($data);

        if ($request->hasFile('booklet_file')) {
            $fields['brochure_file'] = $request->file('booklet_file')->store('brochures', 'public');
        }

        $tender->update($fields);
        $tender->locations()->delete();
        $this->syncLocation($tender, $data);

        return redirect('/client/dashboard')->with('success', 'تم تحديث المنافسة بنجاح.');
    }

    private function formData(): array
    {
        return [
            'categories' => Category::where('is_active', true)->orderBy('sort_order')->get(['id', 'name', 'parent_id']),
            'regions' => Region::where('is_active', true)->with('cities:id,region_id,name')->orderBy('name')->get(['id', 'name']),
        ];
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'type' => ['required', 'in:general,direct_purchase,limited'],
            'name' => ['required', 'string', 'max:255'],
            'booklet_file' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
            'brochure_price' => ['nullable', 'numeric', 'min:0'],
            'contract_duration_months' => ['nullable', 'integer', 'min:0'],
            'insurance_required' => ['boolean'],
            'initial_guarantee_required' => ['boolean'],
            'initial_guarantee_value' => ['nullable', 'numeric', 'min:0'],
            'initial_guarantee_address' => ['nullable', 'string', 'max:255'],
            'final_guarantee_required' => ['boolean'],
            'final_guarantee_value' => ['nullable', 'numeric', 'min:0'],
            'final_guarantee_address' => ['nullable', 'string', 'max:255'],
            'purpose' => ['nullable', 'string'],
            'questions_start' => ['nullable', 'date'],
            'questions_start_hijri' => ['nullable', 'string', 'max:20'],
            'questions_deadline' => ['nullable', 'date'],
            'questions_deadline_hijri' => ['nullable', 'string', 'max:20'],
            'offers_deadline' => ['nullable', 'date'],
            'offers_deadline_hijri' => ['nullable', 'string', 'max:20'],
            'offers_deadline_time' => ['nullable'],
            'offers_open' => ['nullable', 'date'],
            'offers_open_hijri' => ['nullable', 'string', 'max:20'],
            'offers_open_time' => ['nullable'],
            'expected_award_date' => ['nullable', 'date'],
            'expected_award_date_hijri' => ['nullable', 'string', 'max:20'],
            'standstill_period_days' => ['nullable', 'integer', 'min:0'],
            'max_answer_duration_days' => ['nullable', 'integer', 'min:0'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'subcategory_id' => ['nullable', 'exists:categories,id'],
            'locations' => ['array'],
            'locations.*.region_id' => ['required_with:locations', 'exists:regions,id'],
            'locations.*.city_id' => ['required_with:locations', 'exists:cities,id'],
            'includes_supply_items' => ['boolean'],
            'activity_description' => ['nullable', 'string'],
        ], [], ['type' => 'نوع المنافسة', 'name' => 'اسم المنافسة']);
    }

    private function mapFields(array $data): array
    {
        return [
            'name' => $data['name'],
            'type' => $data['type'],
            'category_id' => $data['category_id'] ?? null,
            'subcategory_id' => $data['subcategory_id'] ?? null,
            'purpose' => $data['purpose'] ?? null,
            'activity_description' => $data['activity_description'] ?? null,
            'includes_supply_items' => $data['includes_supply_items'] ?? false,
            'brochure_price' => $data['brochure_price'] ?? 0,
            'contract_duration_months' => $data['contract_duration_months'] ?? null,
            'insurance_required' => $data['insurance_required'] ?? false,
            'initial_guarantee_required' => $data['initial_guarantee_required'] ?? false,
            'initial_guarantee_value' => $data['initial_guarantee_value'] ?? null,
            'initial_guarantee_address' => $data['initial_guarantee_address'] ?? null,
            'final_guarantee_required' => $data['final_guarantee_required'] ?? false,
            'final_guarantee_value' => $data['final_guarantee_value'] ?? null,
            'final_guarantee_address' => $data['final_guarantee_address'] ?? null,
            'standstill_period_days' => $data['standstill_period_days'] ?? null,
            'max_answer_duration_days' => $data['max_answer_duration_days'] ?? null,
            'questions_start' => $data['questions_start'] ?? null,
            'questions_start_hijri' => $data['questions_start_hijri'] ?? null,
            'questions_deadline' => $data['questions_deadline'] ?? null,
            'questions_deadline_hijri' => $data['questions_deadline_hijri'] ?? null,
            'offers_deadline' => $data['offers_deadline'] ?? null,
            'offers_deadline_hijri' => $data['offers_deadline_hijri'] ?? null,
            'offers_deadline_time' => $data['offers_deadline_time'] ?? null,
            'offers_open' => $data['offers_open'] ?? null,
            'offers_open_hijri' => $data['offers_open_hijri'] ?? null,
            'offers_open_time' => $data['offers_open_time'] ?? null,
            'expected_award_date' => $data['expected_award_date'] ?? null,
            'expected_award_date_hijri' => $data['expected_award_date_hijri'] ?? null,
        ];
    }

    private function syncLocation(Tender $tender, array $data): void
    {
        foreach ($data['locations'] ?? [] as $loc) {
            if (! empty($loc['region_id']) && ! empty($loc['city_id'])) {
                $tender->locations()->create([
                    'region_id' => $loc['region_id'],
                    'city_id' => $loc['city_id'],
                ]);
            }
        }
    }

    private function authorizeOwner(Request $request, Tender $tender): void
    {
        abort_unless($tender->client_id === $request->user()->clientProfile?->id, 403);
    }

    private function uniqueNumber(string $column, string $prefix): string
    {
        do {
            $value = $prefix.str_pad((string) random_int(1, 9999999), 7, '0', STR_PAD_LEFT);
        } while (Tender::where($column, $value)->exists());

        return $value;
    }
}
