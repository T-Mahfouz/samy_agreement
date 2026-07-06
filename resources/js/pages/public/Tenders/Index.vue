<script setup lang="ts">
import Countdown from '@/components/Countdown.vue';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { onClickOutside } from '@vueuse/core';
import { computed, reactive, ref } from 'vue';

interface Tender {
    id: number;
    tender_no: string;
    reference_no: string | null;
    serial_no: string;
    name: string;
    type: string;
    brochure_price: string;
    questions_deadline: string | null;
    offers_deadline: string | null;
    offers_deadline_hijri: string | null;
    offers_open: string | null;
    offers_open_time: string | null;
    status: string;
    offers_count: number;
    published_at: string | null;
    client?: { id: number; company_name: string } | null;
    locations?: { id: number; region?: { id: number; name: string } | null }[];
}
interface Category { id: number; name: string; parent_id: number | null }

const props = defineProps<{
    tenders: { data: Tender[]; links: { url: string | null; label: string; active: boolean }[]; total: number };
    filters: Record<string, string>;
    categories: Category[];
    regions: { id: number; name: string }[];
    stats: { active: number; awarded: number; offers: number };
}>();

const img = (n: string) => `/slice/assets/images/${n}`;
const typeLabels: Record<string, string> = { general: 'منافسة عامة', direct_purchase: 'شراء مباشر', limited: 'محدودة' };

const hijri = (date: string | null) => {
    if (!date) return '';
    try {
        return new Intl.DateTimeFormat('ar-SA-u-ca-islamic-nu-latn', { day: 'numeric', month: 'long', year: 'numeric' }).format(new Date(date));
    } catch {
        return '';
    }
};
const publishTitle = (date: string | null) => {
    if (!date) return '';
    const h = hijri(date);
    return h ? `${date} — ${h} هـ` : date;
};

const form = reactive({
    category_id: props.filters.category_id ?? '',
    subcategory_id: props.filters.subcategory_id ?? '',
    reference_no: props.filters.reference_no ?? '',
    tender_no: props.filters.tender_no ?? '',
    name: props.filters.name ?? '',
    type: props.filters.type ?? '',
    status: props.filters.status ?? '',
    region_id: props.filters.region_id ?? '',
    price: props.filters.price ?? '',
    published: props.filters.published ?? '',
    deadline_from: props.filters.deadline_from ?? '',
    deadline_to: props.filters.deadline_to ?? '',
    sort: props.filters.sort ?? '',
});

const calendarType = ref<'gregorian' | 'hijri'>('gregorian');
const hijriCaption = (d: string) => (d ? `${hijri(d)} هـ` : '');

const rootCategories = computed(() => props.categories.filter((c) => c.parent_id === null));
const subCategories = computed(() => props.categories.filter((c) => String(c.parent_id) === String(form.category_id)));

const apply = () => {
    const params = Object.fromEntries(Object.entries(form).filter(([, v]) => v !== ''));
    router.get('/', params, { preserveScroll: true, preserveState: true, replace: true });
    filterOpen.value = false;
};
const setSort = (val: string) => { form.sort = val; sortOpen.value = false; apply(); };

const resetFilters = () => {
    const firstCat = rootCategories.value[0]?.id;
    Object.assign(form, {
        category_id: firstCat ? String(firstCat) : '',
        subcategory_id: '', reference_no: '', tender_no: '', name: '',
        type: '', status: '', region_id: '', price: '', published: '',
        deadline_from: '', deadline_to: '', sort: '',
    });
    calendarType.value = 'gregorian';
    apply();
};

const isActiveCat = (id: number) => String(form.category_id) === String(id);
const selectCategory = (id: number) => {
    form.category_id = String(id);
    form.subcategory_id = '';
    apply();
};

const filterOpen = ref(false);
const sortOpen = ref(false);
const sortRef = ref<HTMLElement | null>(null);
onClickOutside(sortRef, () => (sortOpen.value = false));

const sortOptions = [
    { val: '', label: 'تاريخ الإنشاء تنازليا' },
    { val: 'created_asc', label: 'تاريخ الإنشاء تصاعديا' },
    { val: 'open_desc', label: 'تاريخ فتح العروض تنازليا' },
    { val: 'open_asc', label: 'تاريخ فتح العروض تصاعديا' },
    { val: 'deadline_desc', label: 'تاريخ تقديم العروض تنازليا' },
    { val: 'deadline_asc', label: 'تاريخ تقديم العروض تصاعديا' },
];

const remaining = (date: string | null) => {
    if (!date) return '';
    const days = Math.ceil((new Date(date).getTime() - Date.now()) / 86400000);
    if (days < 0) return 'انتهى التقديم';
    if (days === 0) return 'ينتهي اليوم';
    return `${days} يوم متبقي`;
};
const progress = (t: Tender) => {
    if (!t.offers_deadline || !t.published_at) return 30;
    const total = new Date(t.offers_deadline).getTime() - new Date(t.published_at).getTime();
    const passed = Date.now() - new Date(t.published_at).getTime();
    if (total <= 0) return 100;
    return Math.min(100, Math.max(0, Math.round((passed / total) * 100)));
};
</script>

<template>
    <Head title="المنافسات" />

    <PublicLayout>
        <section>
            <div class="container">
                <div class="row">
                    <div class="col-12 d-flex align-items-center justify-content-between flex-wrap">
                        <h3 class="fs-32 main-color fw-bold d-inline-flex align-items-center gap-4">
                            <div class="img_box main_bc d-flex align-items-center justify-content-center">
                                <img :src="img('menu-icon-1.png')" alt="" style="filter: brightness(3);">
                            </div>
                            المنافسات
                        </h3>
                        <ul class="d-flex align-items-center gap-2 flex-wrap statistics">
                            <li class="d-flex align-items-center justify-content-between gap-6"><span class="fs-24 fw-bold main-color">{{ stats.active.toLocaleString('en') }}</span>منافسات نشطة</li>
                            <li class="d-flex align-items-center justify-content-between gap-6"><span class="fs-24 fw-bold second-color me_24">{{ stats.awarded.toLocaleString('en') }}</span>منافسات تم ترسيتها</li>
                            <li class="d-flex align-items-center justify-content-between gap-6"><span class="fs-24 fw-bold third-color">{{ stats.offers.toLocaleString('en') }}</span>عرض مقدم</li>
                        </ul>
                    </div>

                    <div class="col-12 mt_32 d-flex align-items-center justify-content-between gap-8 items_search position-relative">
                        <ul class="nav nav-tabs partc_tabs" role="tablist">
                            <li class="nav-item" role="presentation" v-for="cat in rootCategories" :key="cat.id">
                                <a class="nav-link" :class="{ active: isActiveCat(cat.id) }" href="#" role="tab"
                                    @click.prevent="selectCategory(cat.id)">
                                    منافسات {{ cat.name }}
                                </a>
                            </li>
                        </ul>
                        <div class="actions d-flex align-items-center gap-2">
                            <a href="#" role="button" aria-label="فلترة"
                                class="main_btn shadow d-flex align-items-center justify-content-center gap-2 dropdown-toggle"
                                @click.prevent="filterOpen = true">
                                <img :src="img('search.png')" alt="" class="m-0">
                                <span class="sort_dropdown__label">بحث</span>
                            </a>
                            <div class="dropdown sort_dropdown" :class="{ show: sortOpen }" ref="sortRef">
                                <a href="#" role="button"
                                    class="main_btn w-64 second p-0 shadow d-flex align-items-center justify-content-center gap-2"
                                    @click.prevent="sortOpen = !sortOpen">
                                    <img :src="img('filter.png')" alt="" class="m-0">
                                </a>
                                <div class="dropdown-menu sort_dropdown__menu" :class="{ show: sortOpen }">
                                    <div class="sort_dropdown__title fs-14 second-color p_16 gray_bc">ترتيب العناصر حسب</div>
                                    <button v-for="o in sortOptions" :key="o.label" class="dropdown-item sort_dropdown__item" type="button" @click="setSort(o.val)">
                                        {{ o.label }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="row">
                            <div v-for="t in tenders.data" :key="t.id" class="col-lg-6">
                                <Link :href="`/tenders/${t.id}`" class="partc_item white_bc mt_32">
                                    <div class="head p_24">
                                        <div class="head_top_info d-flex align-items-center justify-content-between mb_24">
                                            <div class="type fs-14 text-center">{{ typeLabels[t.type] ?? t.type }}</div>
                                            <div class="price d-flex align-items-center gap-2">
                                                قيمة كراسة الشروط
                                                <span v-if="Number(t.brochure_price) > 0" class="d-flex align-items-center gap-6 fw-bold fs-24 ms_16">
                                                    {{ Number(t.brochure_price).toLocaleString('en') }}
                                                    <img :src="img('sar.png')" alt="" class="ms_16">
                                                </span>
                                                <span v-else class="fw-bold fs-24 ms_16 main-color">مجانية</span>
                                            </div>
                                        </div>
                                        <div class="title d-flex align-items-center gap-4">
                                            <div class="img_box publish-date-tooltip d-flex align-items-center justify-content-center"
                                                :title="publishTitle(t.published_at)" tabindex="0">
                                                <img :src="img('calendar-black.png')" alt="تاريخ نشر المنافسة">
                                            </div>
                                            <div class="d-flex flex-column gap-2">
                                                <h3 class="fs-16 fw-bold main-color m-0">{{ t.name }}</h3>
                                                <p class="fs-16 dark-color m-0">{{ t.client?.company_name }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="time_content p_24">
                                        <ul class="d-flex align-items-start justify-content-between gap-4">
                                            <li class="p_16">
                                                <div class="title d-flex align-items-center gap-2 mb_24 fs-14">
                                                    <div class="img_box d-flex align-items-center justify-content-center">
                                                        <img :src="img('calendar-blue.png')" alt="">
                                                    </div>
                                                    آخر موعد لإستلام الإستفسارات
                                                </div>
                                                <p class="d-flex align-items-center justify-content-between gap-4 mb-0">
                                                    <span class="fs-14 fw-bold dark-color">{{ t.questions_deadline ?? '—' }}</span>
                                                </p>
                                            </li>
                                            <li class="p_16">
                                                <div class="title d-flex align-items-center gap-2 mb_24 fs-14">
                                                    <div class="img_box d-flex align-items-center justify-content-center">
                                                        <img :src="img('calendar-orange.png')" alt="">
                                                    </div>
                                                    آخر موعد لتقديم العروض
                                                </div>
                                                <p class="d-flex align-items-center justify-content-between gap-4 mb-0">
                                                    <span class="fs-14 fw-bold dark-color">{{ t.offers_deadline ?? '—' }}</span>
                                                </p>
                                            </li>
                                            <li class="p_16">
                                                <div class="title d-flex align-items-center gap-2 mb_24 fs-14">
                                                    <div class="img_box d-flex align-items-center justify-content-center">
                                                        <img :src="img('clock.png')" alt="">
                                                    </div>
                                                    تاريخ ووقت فتح العروض
                                                </div>
                                                <p class="d-flex align-items-center justify-content-between gap-4 mb-0">
                                                    <span class="fs-14 fw-bold dark-color">{{ t.offers_open ?? '—' }}</span>
                                                    <span v-if="t.offers_open_time" class="fs-14 red-color">{{ t.offers_open_time?.slice(0,5) }}</span>
                                                </p>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="info d-flex align-items-center justify-content-between p_24 gap-4">
                                        <div class="time w-100">
                                            <div class="cont fs-14 dark-color d-flex align-items-center gap-4 mb_12">
                                                <img :src="img('clock-black.png')" alt="" class="m-0">
                                                <Countdown :deadline="t.offers_deadline" :time="t.offers_deadline_time" />
                                            </div>
                                            <div class="progress" role="progressbar" :aria-valuenow="progress(t)" aria-valuemin="0" aria-valuemax="100">
                                                <div class="progress-bar" :style="{ width: progress(t) + '%' }"></div>
                                            </div>
                                        </div>
                                        <div class="numbers d-flex align-items-center gap-4">
                                            <img :src="img('file.png')" alt="">
                                            {{ t.serial_no }}
                                        </div>
                                    </div>
                                </Link>
                            </div>

                            <div v-if="tenders.data.length === 0" class="col-12 text-center dark-color p_24 mt_32">
                                لا توجد منافسات مطابقة لبحثك
                            </div>

                            <div v-if="tenders.links.length > 3" class="col-12 d-flex justify-content-end mt_48">
                                <nav class="ag-pagination d-flex align-items-center" aria-label="Pagination">
                                    <template v-for="(link, i) in tenders.links" :key="i">
                                        <Link v-if="link.url && !link.label.includes('Previous') && !link.label.includes('Next') && !link.label.includes('&laquo;') && !link.label.includes('&raquo;')"
                                            :href="link.url" preserve-scroll
                                            class="ag-pagination__page" :class="{ 'is-active': link.active }"
                                            :aria-current="link.active ? 'page' : undefined" v-html="link.label" />
                                    </template>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <teleport to="body">
            <div v-if="filterOpen" class="modal-backdrop fade show"
                style="position:fixed; inset:0; background-color:rgba(0,0,0,.5); z-index:99999;" @click="filterOpen = false"></div>
            <div class="modal fade" :class="{ show: filterOpen }"
                :style="{ display: filterOpen ? 'block' : 'none', position: 'fixed', inset: '0', zIndex: 9999999 }" tabindex="-1" role="dialog"
                @click.self="filterOpen = false">
                <div class="modal-dialog modal-dialog-centered filter_modal__dialog" role="document">
                    <div class="modal-content filter_modal">
                        <div class="d-flex align-items-center justify-content-end">
                            <button type="button" class="close filter_modal__close m-0" aria-label="إغلاق" @click="filterOpen = false">
                                <img :src="img('close.png')" alt="">
                            </button>
                        </div>
                        <div class="modal-body" style="max-height:78vh; overflow-y:auto;">
                            <div class="row">
                                <div class="col-12 col-lg-3 col-md-6 col-sm-6">
                                    <div class="form-group">
                                        <label>القطاع الرئيسي</label>
                                        <select class="form-control" v-model="form.category_id" @change="form.subcategory_id = ''">
                                            <option value="">اختر القطاع الرئيسي</option>
                                            <option v-for="c in rootCategories" :key="c.id" :value="c.id">{{ c.name }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-12 col-lg-3 col-md-6 col-sm-6">
                                    <div class="form-group">
                                        <label>النشاط الفرعي</label>
                                        <select class="form-control" v-model="form.subcategory_id">
                                            <option value="">اختر النشاط الفرعي</option>
                                            <option v-for="c in subCategories" :key="c.id" :value="c.id">{{ c.name }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-12 col-lg-3 col-md-6 col-sm-6">
                                    <div class="form-group">
                                        <label>الرقم المرجعي للمنافسة</label>
                                        <input class="form-control" type="text" v-model="form.reference_no" placeholder="" @keyup.enter="apply">
                                    </div>
                                </div>
                                <div class="col-12 col-lg-3 col-md-6 col-sm-6">
                                    <div class="form-group">
                                        <label>رقم المنافسة</label>
                                        <input class="form-control" type="text" v-model="form.tender_no" placeholder="" @keyup.enter="apply">
                                    </div>
                                </div>
                                <div class="col-12 col-lg-3 col-md-6 col-sm-6">
                                    <div class="form-group">
                                        <label>اسم المنافسة</label>
                                        <input class="form-control" type="text" v-model="form.name" placeholder="" @keyup.enter="apply">
                                    </div>
                                </div>
                                <div class="col-12 col-lg-3 col-md-6 col-sm-6">
                                    <div class="form-group">
                                        <label>نوع المنافسة</label>
                                        <select class="form-control" v-model="form.type">
                                            <option value="">اختر النوع</option>
                                            <option value="general">منافسة عامة</option>
                                            <option value="limited">محدودة</option>
                                            <option value="direct_purchase">شراء مباشر</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-12 col-lg-3 col-md-6 col-sm-6">
                                    <div class="form-group">
                                        <label>حالة المنافسة</label>
                                        <select class="form-control" v-model="form.status">
                                            <option value="">اختر الحالة</option>
                                            <option value="active">نشطة ( تقديم العروض )</option>
                                            <option value="examination">فحص العروض</option>
                                            <option value="awarding">مرحلة الترسية</option>
                                            <option value="awarded">تم ترسيتها</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-12 col-lg-3 col-md-6 col-sm-6">
                                    <div class="form-group">
                                        <label>المنطقة</label>
                                        <select class="form-control" v-model="form.region_id">
                                            <option value="">اختر المنطقة</option>
                                            <option v-for="r in regions" :key="r.id" :value="r.id">{{ r.name }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-12 col-lg-3 col-md-6 col-sm-6">
                                    <div class="form-group">
                                        <label>قيمة كراسة الشروط</label>
                                        <select class="form-control" v-model="form.price">
                                            <option value="">اختر القيمة</option>
                                            <option value="free">مجانية</option>
                                            <option value="1_1000">1 - 1000</option>
                                            <option value="1001_10000">1001 - 10000</option>
                                            <option value="10001_20000">10001 - 20000</option>
                                            <option value="20001_40000">20001 - 40000</option>
                                            <option value="40001_50000">40001 - 50000</option>
                                            <option value="gt_50000">أكثر من 50000</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-12 col-lg-3 col-md-6 col-sm-6">
                                    <div class="form-group">
                                        <label>تاريخ نشر المنافسة</label>
                                        <select class="form-control" v-model="form.published">
                                            <option value="">اختر المدة</option>
                                            <option value="2d">منذ يومين</option>
                                            <option value="week">منذ أسبوع</option>
                                            <option value="month">منذ شهر</option>
                                            <option value="3months">منذ ٣ شهور</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-12 col-lg-6">
                                    <div class="form-group">
                                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb_12">
                                            <label class="m-0">آخر موعد لتقديم العروض</label>
                                            <div class="date_type d-flex align-items-center gap-2" role="radiogroup" aria-label="نوع التقويم">
                                                <input type="radio" id="calendarTypeGregorian" value="gregorian" v-model="calendarType">
                                                <label class="m-0" for="calendarTypeGregorian">ميلادي</label>
                                                <input type="radio" id="calendarTypeHijri" value="hijri" v-model="calendarType">
                                                <label class="m-0" for="calendarTypeHijri">هجري</label>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center gap-2 date_group">
                                            <label class="m-0 flex-shrink-0">من</label>
                                            <input type="date" class="form-control" aria-label="من" v-model="form.deadline_from" style="flex:1; min-width:0;">
                                            <label class="m-0 flex-shrink-0">إلى</label>
                                            <input type="date" class="form-control" aria-label="إلى" v-model="form.deadline_to" style="flex:1; min-width:0;">
                                        </div>
                                        <div v-if="calendarType === 'hijri' && (form.deadline_from || form.deadline_to)" class="fs-14 dark-color mt_8">
                                            <span v-if="form.deadline_from">من: {{ hijriCaption(form.deadline_from) }}</span>
                                            <span v-if="form.deadline_to" class="ms_16">إلى: {{ hijriCaption(form.deadline_to) }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 d-flex align-items-center justify-content-end gap-2 flex-wrap">
                                    <button type="button" class="main_btn dark_light d-flex align-items-center justify-content-center gap-2" @click="resetFilters">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true" class="m-0">
                                            <path d="M3 12a9 9 0 1 0 3-6.7L3 8m0-5v5h5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        مسح الكل
                                    </button>
                                    <button type="button" class="main_btn d-flex align-items-center justify-content-center gap-2" @click="apply">
                                        <img :src="img('search.png')" alt="" class="m-0">
                                        بحث
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </teleport>
    </PublicLayout>
</template>
