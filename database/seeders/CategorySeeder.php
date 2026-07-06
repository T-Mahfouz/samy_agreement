<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $tree = [
            'الإنشاءات' => ['مقاولات عامة', 'أعمال خرسانية'],
            'تقنية المعلومات' => ['شبكات', 'تطوير برمجيات'],
            'الصيانة والتشغيل' => ['أعمال كهربائية', 'سباكة', 'نظافة', 'صيانة كهربائية'],
            'الحراسات الأمنية' => ['حراس أمن', 'أنظمة مراقبة'],
            'التوريدات' => ['قطع غيار', 'مواد استهلاكية'],
        ];

        $order = 0;
        foreach ($tree as $sector => $subs) {
            $parent = Category::create([
                'name' => $sector,
                'parent_id' => null,
                'is_active' => true,
                'sort_order' => $order++,
            ]);

            $subOrder = 0;
            foreach ($subs as $sub) {
                Category::create([
                    'name' => $sub,
                    'parent_id' => $parent->id,
                    'is_active' => true,
                    'sort_order' => $subOrder++,
                ]);
            }
        }
    }
}
