<?php

namespace Database\Seeders;

use App\Models\ContentPage;
use App\Models\Faq;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CoreDataSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@agreement.com'],
            [
                'name' => 'مدير المنصة',
                'username' => 'admin',
                'role' => 'admin',
                'status' => 'active',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $settings = [
            'platform_bank_name' => 'بنك الراجحي',
            'platform_bank_beneficiary' => 'مؤسسة اتفاق',
            'platform_bank_iban' => 'SA0380000000608010167519',
            'default_commission_rate' => '1',
            'contact_phone' => '0112005500',
            'contact_whatsapp' => '0552005500',
            'contact_email' => 'info@agreement.com',
            'contact_support_email' => 'support@agreement.com',
        ];
        foreach ($settings as $key => $value) {
            SystemSetting::set($key, $value);
        }

        $faqs = [
            ['ما هي منصة اتفاق؟', 'منصة إلكترونية تربط بين الجهات الطارحة للمنافسات والموردين لتقديم العروض.'],
            ['من يمكنه التسجيل في المنصة؟', 'يمكن للجهات الطارحة (العملاء) والموردين (مقدمي الخدمة) التسجيل.'],
            ['كيف أحصل على كراسة الشروط؟', 'بعد دفع قيمة الكراسة ورفع إيصال التحويل واعتماده، يمكنك تحميل الكراسة.'],
            ['ما هي عمولة المنصة؟', 'نسبة من قيمة العقد تُسدد بعد الترسية ويُرفع إيصال تحويلها.'],
        ];
        foreach ($faqs as $i => [$q, $a]) {
            Faq::updateOrCreate(
                ['question' => $q],
                ['answer' => $a, 'sort_order' => $i, 'is_active' => true]
            );
        }

        $about = [
            'who_we_are' => ['من نحن', 'منصة اتفاق هي المصدر الرائد في المملكة العربية السعودية للوصول إلى المنافسات والمشاريع والفرص التجارية.'],
            'vision' => ['رؤيتنا', 'تمكين الشركات من جميع الأحجام لتكون في طليعة المنافسة عبر وصول سريع وفعّال للمعلومات.'],
            'mission' => ['مهمتنا', 'توفير منصة موثوقة وسهلة الاستخدام تجمع المنافسات والمشاريع من مختلف الجهات.'],
        ];
        foreach ($about as $key => [$title, $body]) {
            ContentPage::updateOrCreate(
                ['slug' => 'about', 'section_key' => $key],
                ['title' => $title, 'body' => $body]
            );
        }

        ContentPage::updateOrCreate(
            ['slug' => 'terms', 'section_key' => null],
            ['title' => 'شروط الاستخدام وأداء المسؤولية', 'body' => "باستخدامك منصة اتفاق فإنك توافق على الالتزام بالشروط والأحكام التالية:\n- تقديم بيانات صحيحة ودقيقة عند التسجيل.\n- الالتزام بسداد العمولات والمستحقات في مواعيدها.\n- المنصة وسيط إلكتروني ولا تتحمل مسؤولية أي اتفاقات تتم خارجها.\n- يحق للإدارة تعليق أو إيقاف أي حساب يخالف الشروط."]
        );
        ContentPage::updateOrCreate(
            ['slug' => 'privacy', 'section_key' => null],
            ['title' => 'سياسة الخصوصية', 'body' => "نحرص في منصة اتفاق على حماية خصوصية بياناتك:\n- نجمع البيانات اللازمة فقط لتقديم الخدمة.\n- لا نشارك بياناتك مع أطراف ثالثة دون موافقتك.\n- تُحفظ المستندات والإيصالات بشكل آمن.\n- يمكنك طلب تحديث أو حذف بياناتك بالتواصل مع الإدارة."]
        );
    }
}
