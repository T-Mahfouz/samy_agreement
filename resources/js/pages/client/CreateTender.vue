<script setup lang="ts">
import FileTypeHint from '@/components/FileTypeHint.vue';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

interface Category { id: number; name: string; parent_id: number | null }
interface Region { id: number; name: string; cities: { id: number; region_id: number; name: string }[] }
const props = defineProps<{ categories: Category[]; regions: Region[]; tender?: Record<string, any> | null }>();

const img = (n: string) => `/slice/assets/images/${n}`;
const isEditing = !!props.tender;
const t = props.tender ?? {};
const v = (key: string, def: any = '') => (t[key] ?? def);

// تاريخ اليوم (YYYY-MM-DD) لمنع اختيار تواريخ ماضية في حقول التاريخ
const today = (() => {
    const d = new Date();
    const m = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return `${d.getFullYear()}-${m}-${day}`;
})();

const form = useForm<Record<string, any>>({
    type: v('type', 'general'),
    name: v('name'),
    booklet_file: null,
    brochure_price: v('brochure_price'),
    contract_duration_months: v('contract_duration_months', 36),
    insurance_required: !!v('insurance_required', false),
    initial_guarantee_required: !!v('initial_guarantee_required', false),
    initial_guarantee_value: v('initial_guarantee_value'),
    initial_guarantee_address: v('initial_guarantee_address'),
    final_guarantee_required: !!v('final_guarantee_required', false),
    final_guarantee_value: v('final_guarantee_value'),
    final_guarantee_address: v('final_guarantee_address'),
    purpose: v('purpose'),
    questions_deadline: v('questions_deadline'), questions_deadline_hijri: v('questions_deadline_hijri'),
    offers_deadline: v('offers_deadline'), offers_deadline_hijri: v('offers_deadline_hijri'), offers_deadline_time: v('offers_deadline_time'),
    offers_open: v('offers_open'), offers_open_hijri: v('offers_open_hijri'), offers_open_time: v('offers_open_time'),
    expected_award_date: v('expected_award_date'), expected_award_date_hijri: v('expected_award_date_hijri'),
    questions_start: v('questions_start'), questions_start_hijri: v('questions_start_hijri'),
    standstill_period_days: v('standstill_period_days', 4),
    max_answer_duration_days: v('max_answer_duration_days', 5),
    category_id: v('category_id'), subcategory_id: v('subcategory_id'),
    locations: (t.locations && t.locations.length ? t.locations.map((l: any) => ({ region_id: l.region_id, city_id: l.city_id })) : [{ region_id: '', city_id: '' }]),
    includes_supply_items: !!v('includes_supply_items', false),
    activity_description: v('activity_description'),
});

const rootCategories = computed(() => props.categories.filter((c) => c.parent_id === null));
const subCategories = computed(() => props.categories.filter((c) => String(c.parent_id) === String(form.category_id)));
const citiesFor = (regionId: any) => props.regions.find((r) => String(r.id) === String(regionId))?.cities ?? [];
const addLocation = () => form.locations.push({ region_id: '', city_id: '' });
const removeLocation = (i: number) => { if (form.locations.length > 1) form.locations.splice(i, 1); };

const onBooklet = (e: Event) => { form.booklet_file = (e.target as HTMLInputElement).files?.[0] ?? null; };
const submit = () => {
    if (isEditing) {
        form.transform((d) => ({ ...d, _method: 'put' })).post(`/client/tenders/${props.tender!.id}`, { forceFormData: true });
    } else {
        form.post('/client/tenders', { forceFormData: true });
    }
};
</script>

<template>
    <Head title="إنشاء منافسة جديدة" />

    <PublicLayout>
        <section class="tender_details">
            <div class="container">
                <div class="row">
                    <div class="col-12 d-flex align-items-center justify-content-between flex-wrap mb_32 position-relative">
                        <h3 class="dark-color d-flex gap-2 mb_16 fs-24 fw-bold">
                            <div class="img_box main_bc d-flex align-items-center justify-content-center"><img :src="img('add-cricle.png')" alt="" class="m-0"></div>
                            {{ isEditing ? 'تعديل المنافسة' : 'إنشاء منافسة جديدة' }}
                        </h3>
                        <Link href="/client/dashboard" class="main_btn dark_light d-inline-flex align-items-center gap-2">
                            <span class="img_box white_bc main-color d-inline-flex align-items-center justify-content-center fw-bold fs-24"><img :src="img('details-arrow.png')" alt=""></span>
                            رجوع الى منافساتي
                        </Link>
                    </div>

                    <div class="col-12">
                        <form @submit.prevent="submit" novalidate>
                            <!-- البيانات الأساسية -->
                            <div class="border_box p_24 white_bc mb_32">
                                <h3 class="fs-16 dark-color d-flex align-items-center gap-4 mb_24">
                                    <div class="img_box dark_gray_bc d-flex align-items-center justify-content-center"><img :src="img('details-file.png')" alt="" class="m-0"></div>
                                    البيانات الأساسية للمنافسة
                                </h3>
                                <div class="row align-items-start">
                                    <div class="col-12 col-lg-9">
                                        <div class="row">
                                            <div class="col-12 col-sm-6 col-md-6 col-lg-4">
                                                <div class="form-group">
                                                    <label>نوع المنافسة</label>
                                                    <select class="form-control" v-model="form.type">
                                                        <option value="general">منافسة عامة</option>
                                                        <option value="direct_purchase">شراء مباشر</option>
                                                        <option value="limited">محدودة</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-12 col-sm-6 col-md-6 col-lg-8">
                                                <div class="form-group">
                                                    <label>اسم المنافسة</label>
                                                    <input type="text" class="form-control" v-model="form.name" placeholder="اسم المنافسة">
                                                    <small v-if="form.errors.name" class="red-color">{{ form.errors.name }}</small>
                                                </div>
                                            </div>
                                            <div class="col-12 col-sm-6 col-md-6 col-lg-4">
                                                <div class="form-group">
                                                    <label class="d-block mb_12">ملف كراسة الشروط</label>
                                                    <label class="tender_book_modal__upload mb-0">
                                                        <input type="file" class="sr-only" accept=".pdf,.doc,.docx" @change="onBooklet">
                                                        <span class="tender_book_modal__upload-inner d-flex align-items-center gap-4">
                                                            <span class="dark-color fs-14">{{ form.booklet_file?.name || 'ارفاق الملف' }}</span>
                                                            <svg class="tender_book_modal__clip flex-shrink-0" width="22" height="22" viewBox="0 0 24 24" aria-hidden="true"><path fill="#000000" d="M16.5 6v11.5a4.5 4.5 0 1 1-9 0V5a2.5 2.5 0 0 1 5 0v10.5a1 1 0 1 1-2 0V6h-2v9.5a3 3 0 0 0 6 0V5a4.5 4.5 0 0 0-9 0v12.5a6 6 0 1 0 12 0V6h-2z" /></svg>
                                                        </span>
                                                    </label>
                                                    <FileTypeHint exts="PDF أو Word (DOC/DOCX)" :max-mb="10" />
                                                    <small v-if="form.errors.booklet_file" class="red-color d-block">{{ form.errors.booklet_file }}</small>
                                                </div>
                                            </div>
                                            <div class="col-12 col-sm-6 col-md-6 col-lg-4">
                                                <div class="form-group">
                                                    <label>قيمة كراسة الشروط</label>
                                                    <input type="number" min="0" step="0.01" class="form-control" v-model="form.brochure_price" placeholder="القيمة" inputmode="decimal">
                                                    <small v-if="form.errors.brochure_price" class="red-color d-block">{{ form.errors.brochure_price }}</small>
                                                </div>
                                            </div>
                                            <div class="col-12 col-sm-6 col-md-6 col-lg-2">
                                                <div class="form-group">
                                                    <label>مدة العقد</label>
                                                    <select class="form-control" v-model="form.contract_duration_months">
                                                        <option :value="3">3 شهور</option>
                                                        <option :value="6">6 شهور</option>
                                                        <option :value="12">1 سنة</option>
                                                        <option :value="24">2 سنة</option>
                                                        <option :value="36">3 سنة</option>
                                                        <option :value="48">4 سنة</option>
                                                        <option :value="60">5 سنة</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-12 col-sm-6 col-md-6 col-lg-2">
                                                <div class="form-group">
                                                    <label>التأمين</label>
                                                    <select class="form-control" v-model="form.insurance_required">
                                                        <option :value="true">مطلوب</option>
                                                        <option :value="false">غير مطلوب</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-12 col-sm-6 col-md-6 col-lg-4">
                                                <div class="form-group">
                                                    <label>الضمان الإبتدائي</label>
                                                    <select class="form-control" v-model="form.initial_guarantee_required">
                                                        <option :value="true">مطلوب</option>
                                                        <option :value="false">غير مطلوب</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-12 col-sm-6 col-md-6 col-lg-4">
                                                <div class="form-group">
                                                    <label>قيمة الضمان الإبتدائي</label>
                                                    <input type="number" min="0" step="0.01" class="form-control" v-model="form.initial_guarantee_value" placeholder="القيمة" inputmode="decimal">
                                                    <small v-if="form.errors.initial_guarantee_value" class="red-color d-block">{{ form.errors.initial_guarantee_value }}</small>
                                                </div>
                                            </div>
                                            <div class="col-12 col-sm-6 col-md-6 col-lg-4">
                                                <div class="form-group">
                                                    <label>عنوان الضمان الإبتدائي</label>
                                                    <input type="text" class="form-control" v-model="form.initial_guarantee_address" placeholder="العنوان">
                                                </div>
                                            </div>
                                            <div class="col-12 col-sm-6 col-md-6 col-lg-4">
                                                <div class="form-group">
                                                    <label>الضمان النهائي</label>
                                                    <select class="form-control" v-model="form.final_guarantee_required">
                                                        <option :value="true">مطلوب</option>
                                                        <option :value="false">غير مطلوب</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-12 col-sm-6 col-md-6 col-lg-4">
                                                <div class="form-group">
                                                    <label>قيمة الضمان النهائي</label>
                                                    <input type="number" min="0" step="0.01" class="form-control" v-model="form.final_guarantee_value" placeholder="القيمة" inputmode="decimal">
                                                    <small v-if="form.errors.final_guarantee_value" class="red-color d-block">{{ form.errors.final_guarantee_value }}</small>
                                                </div>
                                            </div>
                                            <div class="col-12 col-sm-6 col-md-6 col-lg-4">
                                                <div class="form-group">
                                                    <label>عنوان الضمان النهائي</label>
                                                    <input type="text" class="form-control" v-model="form.final_guarantee_address" placeholder="العنوان">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-lg-3 mt_24 mt-lg-0">
                                        <div class="form-group mb-0">
                                            <label>الغرض منها</label>
                                            <textarea class="form-control" v-model="form.purpose" rows="12" placeholder="وصف الغرض من المنافسة"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- العناوين والمواعيد -->
                            <div class="border_box p_24 white_bc mb_32">
                                <h3 class="fs-16 dark-color d-flex align-items-center gap-4 mb_24">
                                    <div class="img_box dark_gray_bc d-flex align-items-center justify-content-center"><img :src="img('details-clock.png')" alt="" class="m-0"></div>
                                    العناوين والمواعيد للمنافسة
                                </h3>
                                <div class="row">
                                    <div class="col-12 col-lg-3 col-md-6 col-sm-6"><div class="form-group"><label class="d-flex align-items-center gap-2 mb_12">آخر موعد لإستلام الإستفسارات ( ميلادي )</label><input type="date" class="form-control" :min="today" :max="form.offers_deadline || undefined" v-model="form.questions_deadline"><small v-if="form.errors.questions_deadline" class="red-color d-block">{{ form.errors.questions_deadline }}</small></div></div>
                                    <div class="col-12 col-lg-3 col-md-6 col-sm-6"><div class="form-group"><label class="d-flex align-items-center gap-2 mb_12">آخر موعد لإستلام الإستفسارات ( هجري )</label><input type="text" class="form-control" v-model="form.questions_deadline_hijri" placeholder="هجري"></div></div>
                                    <div class="col-12 col-lg-3 col-md-6 col-sm-6"><div class="form-group"><label class="d-flex align-items-center gap-2 mb_12">آخر موعد لتقديم العروض ( ميلادي ) <span class="red-color">*</span></label><input type="date" class="form-control" :min="today" v-model="form.offers_deadline"><small v-if="form.errors.offers_deadline" class="red-color d-block">{{ form.errors.offers_deadline }}</small></div></div>
                                    <div class="col-12 col-lg-3 col-md-6 col-sm-6"><div class="form-group"><label class="d-flex align-items-center gap-2 mb_12">آخر موعد لتقديم العروض ( هجري )</label><input type="text" class="form-control" v-model="form.offers_deadline_hijri" placeholder="هجري"></div></div>
                                    <div class="col-12 col-lg-3 col-md-6 col-sm-6"><div class="form-group"><label class="d-flex align-items-center gap-2 mb_12">وقت آخر موعد لتقديم العروض</label><input type="time" class="form-control" v-model="form.offers_deadline_time"></div></div>
                                    <div class="col-12 col-lg-3 col-md-6 col-sm-6"><div class="form-group"><label class="d-flex align-items-center gap-2 mb_12">تاريخ فتح العروض ( ميلادي )</label><input type="date" class="form-control" :min="form.offers_deadline || today" v-model="form.offers_open"><small v-if="form.errors.offers_open" class="red-color d-block">{{ form.errors.offers_open }}</small></div></div>
                                    <div class="col-12 col-lg-3 col-md-6 col-sm-6"><div class="form-group"><label class="d-flex align-items-center gap-2 mb_12">تاريخ فتح العروض ( هجري )</label><input type="text" class="form-control" v-model="form.offers_open_hijri" placeholder="هجري"></div></div>
                                    <div class="col-12 col-lg-3 col-md-6 col-sm-6"><div class="form-group"><label class="d-flex align-items-center gap-2 mb_12">وقت فتح العروض</label><input type="time" class="form-control" v-model="form.offers_open_time"></div></div>
                                    <div class="col-12 col-lg-3 col-md-6 col-sm-6"><div class="form-group"><label class="d-flex align-items-center gap-2 mb_12">التاريخ المتوقع للترسية ( ميلادي )</label><input type="date" class="form-control" :min="form.offers_deadline || today" v-model="form.expected_award_date"><small v-if="form.errors.expected_award_date" class="red-color d-block">{{ form.errors.expected_award_date }}</small></div></div>
                                    <div class="col-12 col-lg-3 col-md-6 col-sm-6"><div class="form-group"><label class="d-flex align-items-center gap-2 mb_12">التاريخ المتوقع للترسية ( هجري )</label><input type="text" class="form-control" v-model="form.expected_award_date_hijri" placeholder="هجري"></div></div>
                                    <div class="col-12 col-lg-3 col-md-6 col-sm-6"><div class="form-group"><label class="d-flex align-items-center gap-2 mb_12">بداية إرسال الاسئلة والإستفسارات ( ميلادي )</label><input type="date" class="form-control" :min="today" :max="form.offers_deadline || undefined" v-model="form.questions_start"><small v-if="form.errors.questions_start" class="red-color d-block">{{ form.errors.questions_start }}</small></div></div>
                                    <div class="col-12 col-lg-3 col-md-6 col-sm-6"><div class="form-group"><label class="d-flex align-items-center gap-2 mb_12">بداية إرسال الاسئلة والإستفسارات ( هجري )</label><input type="text" class="form-control" v-model="form.questions_start_hijri" placeholder="هجري"></div></div>
                                    <div class="col-12 col-lg-3 col-md-6 col-sm-6"><div class="form-group"><label>فترة التوقف</label><input type="number" class="form-control" v-model="form.standstill_period_days" min="0"></div></div>
                                    <div class="col-12 col-lg-3 col-md-6 col-sm-6"><div class="form-group"><label>اقصى مدة للاجابة على الاستفسارات</label><input type="number" class="form-control" v-model="form.max_answer_duration_days" min="0"></div></div>
                                </div>
                            </div>

                            <!-- مجال التصنيف وموقع التنفيذ -->
                            <div class="border_box p_24 white_bc mb_32">
                                <h3 class="fs-16 dark-color d-flex align-items-center gap-4 mb_24">
                                    <div class="img_box dark_gray_bc d-flex align-items-center justify-content-center"><img :src="img('details-map.png')" alt="" class="m-0"></div>
                                    مجال التصنيف وموقع التنفيذ والتقديم
                                </h3>
                                <div class="row">
                                    <div class="col-12 col-lg-3 col-md-6 col-sm-6">
                                        <div class="form-group">
                                            <label>القطاع الرئيسي</label>
                                            <select class="form-control" v-model="form.category_id" @change="form.subcategory_id = ''">
                                                <option value="" disabled>اختر القطاع الرئيسي</option>
                                                <option v-for="c in rootCategories" :key="c.id" :value="c.id">{{ c.name }}</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-12 col-lg-3 col-md-6 col-sm-6">
                                        <div class="form-group">
                                            <label>النشاط الفرعي</label>
                                            <select class="form-control" v-model="form.subcategory_id">
                                                <option value="" disabled>اختر النشاط الفرعي</option>
                                                <option v-for="c in subCategories" :key="c.id" :value="c.id">{{ c.name }}</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <label class="d-flex align-items-center justify-content-between mb_12">
                                            <span>مكان التنفيذ والمدن</span>
                                            <a href="#" class="main_btn light fs-14 pe_16 pst_16 pt_4 pb_4" @click.prevent="addLocation">+ إضافة مكان</a>
                                        </label>
                                        <div v-for="(loc, i) in form.locations" :key="i" class="d-flex gap-2 align-items-center inputs_group mb_12">
                                            <select class="form-control" v-model="loc.region_id" @change="loc.city_id = ''">
                                                <option value="" disabled>اختر المنطقة</option>
                                                <option v-for="r in regions" :key="r.id" :value="r.id">{{ r.name }}</option>
                                            </select>
                                            <select class="form-control" v-model="loc.city_id">
                                                <option value="" disabled>اختر المدينة</option>
                                                <option v-for="c in citiesFor(loc.region_id)" :key="c.id" :value="c.id">{{ c.name }}</option>
                                            </select>
                                            <button type="button" class="main_btn red_light flex-shrink-0 pe_16 pst_16" @click="removeLocation(i)" :disabled="form.locations.length === 1">×</button>
                                        </div>
                                    </div>
                                    <div class="col-12 col-lg-3 col-md-4 col-sm-6">
                                        <div class="form-group">
                                            <label>تشمل المنافسة بنود توريد</label>
                                            <select class="form-control" v-model="form.includes_supply_items">
                                                <option :value="true">يوجد</option>
                                                <option :value="false">لايوجد</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-12 col-lg-9 col-md-8 col-sm-6">
                                        <div class="form-group mb-0">
                                            <label>نشاط المنافسة</label>
                                            <textarea class="form-control" v-model="form.activity_description" rows="5" placeholder="وصف نشاط المنافسة"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-center mt_24">
                                <button type="submit" :disabled="form.processing" class="main_btn m-0 shadow d-inline-flex align-items-center justify-content-center pe_32 ps_32">{{ isEditing ? 'حفظ التعديلات' : 'نشر المنافسة' }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>
