<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\City;
use App\Models\ClientProfile;
use App\Models\Offer;
use App\Models\ProviderProfile;
use App\Models\Region;
use App\Models\Tender;
use App\Models\TenderLocation;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class DemoTenderSeeder extends Seeder
{
    public function run(): void
    {
        $clientUser = User::firstOrCreate(
            ['email' => 'client@agreement.com'],
            ['name' => 'شركة مساس المحدودة', 'username' => 'masas', 'role' => 'client', 'status' => 'active', 'password' => Hash::make('password'), 'email_verified_at' => now()]
        );
        $client = ClientProfile::firstOrCreate(
            ['user_id' => $clientUser->id],
            ['company_name' => 'شركة مساس المحدودة', 'mobile' => '0512345678', 'bank_name' => 'بنك الراجحي', 'bank_beneficiary_name' => 'شركة مساس المحدودة', 'bank_iban' => 'SA4420000001234567891234']
        );

        $providerUser = User::firstOrCreate(
            ['email' => 'provider@agreement.com'],
            ['name' => 'شركة عندنا للمشاريع', 'username' => 'andana', 'role' => 'provider', 'status' => 'active', 'password' => Hash::make('password'), 'email_verified_at' => now()]
        );
        $provider = ProviderProfile::firstOrCreate(
            ['user_id' => $providerUser->id],
            ['company_name' => 'شركة عندنا للمشاريع', 'status' => 'approved']
        );

        $cats = Category::whereNull('parent_id')->with('children')->get();
        $regions = Region::with('cities')->get();

        if (! $provider->main_category_id && $cats->isNotEmpty()) {
            $provider->update([
                'main_category_id' => $cats->first()->id,
            ]);
        }

        $samples = [
            ['name' => 'توريد قطع غيار للصمامات والمحركات الكهربائية لمنظومة إنتاج المياه', 'type' => 'general', 'status' => 'active', 'price' => 500, 'days' => 12],
            ['name' => 'أعمال صيانة وتشغيل المباني الإدارية لمدة ثلاث سنوات', 'type' => 'general', 'status' => 'active', 'price' => 1200, 'days' => 20],
            ['name' => 'تقديم خدمات الحراسات الأمنية للمنشآت الحكومية', 'type' => 'limited', 'status' => 'examination', 'price' => 0, 'days' => -3],
            ['name' => 'مشروع تطوير البنية التحتية لشبكات تقنية المعلومات', 'type' => 'general', 'status' => 'awarding', 'price' => 3000, 'days' => -8],
            ['name' => 'توريد مواد استهلاكية ومستلزمات مكتبية', 'type' => 'direct_purchase', 'status' => 'awarded', 'price' => 250, 'days' => -20],
            ['name' => 'أعمال النظافة العامة للمرافق والساحات', 'type' => 'general', 'status' => 'active', 'price' => 800, 'days' => 7],
        ];

        foreach ($samples as $i => $s) {
            $cat = $cats[$i % $cats->count()];
            $sub = $cat->children->first();
            $deadline = now()->addDays($s['days']);

            $tender = Tender::firstOrCreate(
                ['tender_no' => '6001'.str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT)],
                [
                    'client_id' => $client->id,
                    'reference_no' => '26043900'.str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT),
                    'serial_no' => '2609390'.str_pad((string) ($i + 1), 6, '0', STR_PAD_LEFT),
                    'name' => $s['name'],
                    'type' => $s['type'],
                    'category_id' => $cat->id,
                    'purpose' => 'يهدف هذا المشروع إلى '.$s['name'].' وفق المواصفات الفنية المعتمدة وضمان الجودة طوال فترة العقد.',
                    'activity_description' => 'المنافسة تخص نشاط '.($sub?->name ?? $cat->name).'.',
                    'submission_method' => 'ملف للعرض الفني وملف للعرض المالي',
                    'includes_supply_items' => $i % 2 === 0,
                    'brochure_price' => $s['price'],
                    'contract_duration_months' => 36,
                    'insurance_required' => $i % 2 === 0,
                    'initial_guarantee_required' => true,
                    'initial_guarantee_value' => 200,
                    'final_guarantee_required' => true,
                    'final_guarantee_value' => 500,
                    'commission_rate' => 1.00,
                    'questions_deadline' => $deadline->copy()->subDays(3)->toDateString(),
                    'offers_deadline' => $deadline->toDateString(),
                    'offers_deadline_hijri' => '1447-11-'.str_pad((string) (($i % 28) + 1), 2, '0', STR_PAD_LEFT),
                    'offers_deadline_time' => '21:59',
                    'offers_open' => $deadline->copy()->addDay()->toDateString(),
                    'offers_open_time' => '10:00',
                    'expected_award_date' => $deadline->copy()->addDays(10)->toDateString(),
                    'works_start' => $deadline->copy()->addDays(20)->toDateString(),
                    'status' => $s['status'],
                    'published_at' => now()->subDays($i + 1),
                ]
            );

            if (! $tender->brochure_file) {
                $bookletPath = "brochures/booklet-{$tender->id}.pdf";
                Storage::disk('public')->put($bookletPath, $this->samplePdf('كراسة الشروط'));
                $tender->update(['brochure_file' => $bookletPath]);
            }

            if ($tender->locations()->count() === 0) {
                foreach ($regions->take(2) as $region) {
                    foreach ($region->cities->take(2) as $city) {
                        TenderLocation::create(['tender_id' => $tender->id, 'region_id' => $region->id, 'city_id' => $city->id]);
                    }
                }
            }

            if ($tender->offers()->count() === 0 && in_array($s['status'], ['examination', 'awarding', 'awarded'], true)) {
                $techPath = "offers/{$tender->id}/technical-sample.pdf";
                $finPath = "offers/{$tender->id}/financial-sample.pdf";
                Storage::disk('local')->put($techPath, $this->samplePdf('نموذج العرض الفني'));
                Storage::disk('local')->put($finPath, $this->samplePdf('نموذج العرض المالي'));

                Offer::create([
                    'tender_id' => $tender->id,
                    'provider_id' => $provider->id,
                    'technical_file' => $techPath,
                    'financial_file' => $finPath,
                    'financial_value' => 5750 + $i * 1000,
                    'technical_check' => 'matching',
                    'is_awarded' => $s['status'] === 'awarded',
                    'status' => $s['status'] === 'awarded' ? 'awarded' : 'submitted',
                    'submitted_at' => now()->subDays($i),
                ]);
            }
        }
    }

    private function samplePdf(string $title): string
    {
        $text = "BT /F1 18 Tf 60 120 Td ($title) Tj ET";
        $len = strlen($text);

        return "%PDF-1.4\n"
            ."1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj\n"
            ."2 0 obj<</Type/Pages/Kids[3 0 R]/Count 1>>endobj\n"
            ."3 0 obj<</Type/Page/Parent 2 0 R/MediaBox[0 0 300 200]/Resources<</Font<</F1 4 0 R>>>>/Contents 5 0 R>>endobj\n"
            ."4 0 obj<</Type/Font/Subtype/Type1/BaseFont/Helvetica>>endobj\n"
            ."5 0 obj<</Length {$len}>>stream\n{$text}\nendstream endobj\n"
            ."trailer<</Root 1 0 R>>\n%%EOF";
    }
}
