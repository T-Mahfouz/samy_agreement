<script setup lang="ts">
import FileTypeHint from '@/components/FileTypeHint.vue';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { type SharedData } from '@/types';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { onClickOutside } from '@vueuse/core';
import { computed, ref } from 'vue';

interface Provider { id: number; company_name: string }
interface Offer { id: number; financial_value: string | null; technical_check: string; is_awarded: boolean; provider?: Provider | null }
interface Loc { id: number; region?: { name: string } | null; city?: { name: string } | null }
interface Tender {
    id: number; tender_no: string; reference_no: string | null; serial_no: string; name: string; type: string; status: string;
    purpose: string | null; activity_description: string | null; submission_method: string | null; includes_supply_items: boolean;
    brochure_price: string; contract_duration_months: number | null; insurance_required: boolean;
    initial_guarantee_required: boolean; initial_guarantee_value: string | null; initial_guarantee_address: string | null;
    final_guarantee_required: boolean; final_guarantee_value: string | null; final_guarantee_address: string | null;
    standstill_period_days: number | null; max_answer_duration_days: number | null; commission_rate: string;
    questions_start: string | null; questions_start_hijri: string | null;
    questions_deadline: string | null; questions_deadline_hijri: string | null;
    offers_deadline: string | null; offers_deadline_hijri: string | null; offers_deadline_time: string | null;
    offers_open: string | null; offers_open_hijri: string | null; offers_open_time: string | null;
    expected_award_date: string | null; expected_award_date_hijri: string | null;
    works_start: string | null; works_start_hijri: string | null; works_start_time: string | null;
    client?: { id: number; company_name: string; bank_name: string | null; bank_beneficiary_name: string | null; bank_iban: string | null } | null;
    category?: { name: string } | null; subcategory?: { name: string } | null;
    locations: Loc[]; offers: Offer[]; awarded_offer?: Offer | null;
}

const props = defineProps<{ tender: Tender; offersCount: number; offersOpen: boolean }>();
const page = usePage<SharedData>();
const isProvider = computed(() => page.props.auth?.user?.role === 'provider');

const img = (n: string) => `/slice/assets/images/${n}`;
const typeLabels: Record<string, string> = { general: 'منافسة عامة', direct_purchase: 'شراء مباشر', limited: 'محدودة' };
const statusLabels: Record<string, string> = { active: 'نشطة', examination: 'فحص العروض', awarding: 'مرحلة الترسية', awarded: 'تم ترسيتها', cancelled: 'ملغاة' };
const techLabels: Record<string, string> = { matching: 'مطابق', not_matching: 'غير مطابق', pending: 'قيد الفحص' };
const t = props.tender;
void t;

const num = (v: string | null) => (v ? Number(v).toLocaleString('en') : '—');

// dropdowns / modals
const awardOpen = ref(false);
const awardRef = ref<HTMLElement | null>(null);
onClickOutside(awardRef, () => (awardOpen.value = false));
const descExpanded = ref(false);
const bookOpen = ref(false);
const proposalOpen = ref(false);

// تقديم عرض (للمورّد)
const proposalForm = useForm<{ technical_file: File | null; financial_file: File | null; financial_value: string; declaration_accepted: boolean }>({
    technical_file: null, financial_file: null, financial_value: '', declaration_accepted: false,
});
const onProposalFile = (key: 'technical_file' | 'financial_file', e: Event) => { proposalForm[key] = (e.target as HTMLInputElement).files?.[0] ?? null; };
const submitProposal = () => proposalForm.post(`/provider/tenders/${props.tender.id}/offer`, {
    forceFormData: true, preserveScroll: true, onSuccess: () => { proposalOpen.value = false; proposalForm.reset(); },
});

// سداد قيمة كراسة الشروط (للمورّد)
const bookForm = useForm<{ receipt_file: File | null }>({ receipt_file: null });
const onBookFile = (e: Event) => { bookForm.receipt_file = (e.target as HTMLInputElement).files?.[0] ?? null; };
const submitBook = () => bookForm.post(`/provider/tenders/${props.tender.id}/brochure-payment`, {
    forceFormData: true, preserveScroll: true, onSuccess: () => { bookOpen.value = false; bookForm.reset(); },
});

// group locations by region
const groupedLocations = computed(() => {
    const map: Record<string, string[]> = {};
    for (const l of t.locations) {
        const r = l.region?.name ?? '—';
        (map[r] ??= []).push(l.city?.name ?? '');
    }
    return Object.entries(map).map(([region, cities]) => ({ region, cities: cities.filter(Boolean).join(' - ') }));
});
</script>

<template>
    <Head :title="tender.name" />

    <PublicLayout>
        <section class="tender_details">
            <div class="container">
                <div class="row">
                    <!-- Title + award results -->
                    <div class="col-12 d-flex align-items-center justify-content-between flex-wrap mb_48 pb_24 border_bottom position-relative">
                        <h3 class="fs-32 main-color fw-bold d-inline-flex align-items-center gap-4">
                            <div class="img_box main_bc d-flex align-items-center justify-content-center">
                                <img :src="img('comption_details.png')" alt="">
                            </div>
                            تفاصيل المنافسة
                        </h3>
                        <div v-if="tender.offers.length" class="dropdown award_mega_dropdown" :class="{ show: awardOpen }" ref="awardRef">
                            <a href="#" role="button"
                                class="main_btn gray d-flex align-items-center gap-4 pe_32 ps_32 pt_8 pb_8 dropdown-toggle"
                                @click.prevent="awardOpen = !awardOpen">
                                <img :src="img('details-users.png')" alt="">
                                إعلان نتائج الترسية
                            </a>
                            <div class="dropdown-menu dropdown-menu-right award_mega_dropdown__menu shadow border_box" :class="{ show: awardOpen }">
                                <div class="award_mega_dropdown__panel border_box white_bc">
                                    <div class="row ms_0">
                                        <div class="col-lg-8 col-md-8">
                                            <div class="p_16 pe_0">
                                                <h3 class="fs-16 main-color d-flex align-items-center gap-4 mb_24">
                                                    <img :src="img('details-arrow.png')" alt="" class="m-0">
                                                    قائمة الموردين المتقدمين
                                                </h3>
                                                <div class="award_mega_dropdown__table_wrap">
                                                    <table class="custom_table w-100">
                                                        <thead>
                                                            <tr>
                                                                <th class="main-color">اسم المورد</th>
                                                                <th class="main-color">قيمة العرض المالي</th>
                                                                <th class="main-color">نتائج فحص العروض الفنية</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr v-for="o in tender.offers" :key="o.id" class="award_mega_dropdown__row" :class="{ 'award_mega_dropdown__row--winner': o.is_awarded }">
                                                                <td>{{ o.provider?.company_name }}</td>
                                                                <td>{{ num(o.financial_value) }}</td>
                                                                <td>{{ techLabels[o.technical_check] }}</td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-md-4">
                                            <div class="winner p_24">
                                                <div class="award_mega_dropdown__winner gray-bc h-100">
                                                    <div class="award_mega_dropdown__winner_head white-color text-center fs-14 p_12 mb_16">
                                                        المورد الفائز بالمنافسة (ترسية كاملة)
                                                    </div>
                                                    <div class="p_16">
                                                        <h4 class="fs-14 dark-color d-flex align-items-center justify-content-start gap-4 mb_12">
                                                            <img :src="img('details-arrow.png')" alt="" class="m-0">
                                                            قيمة العرض المالي والترسية
                                                        </h4>
                                                        <div class="d-flex justify-content-center mt_16 mb_16">
                                                            <div class="award_mega_dropdown__value_circle main_bc d-flex align-items-center justify-content-center">
                                                                <span class="white-color fw-bold">{{ num(tender.awarded_offer?.financial_value ?? null) }}</span>
                                                            </div>
                                                        </div>
                                                        <div class="award_mega_dropdown__supplier_label fs-14 dark-color d-flex align-items-center justify-content-start gap-4 mb_8">
                                                            <img :src="img('details-arrow.png')" alt="" class="m-0">
                                                            اسم المورد :
                                                        </div>
                                                        <p class="main-color mb-0 fs-16">{{ tender.awarded_offer?.provider?.company_name ?? '—' }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Basic data -->
                    <div class="col-lg-6">
                        <h3 class="fs-16 main-color d-inline-flex align-items-center gap-4 mb_24">
                            <div class="img_box main_bc d-flex align-items-center justify-content-center"><img :src="img('details-file.png')" alt=""></div>
                            البيانات الإساسية للمنافسة
                        </h3>
                        <div class="border_box p_16 white_bc mb_32">
                            <ul class="d-flex flex-column gap-4">
                                <li class="d-flex align-items-center w-100">
                                    <div class="me_16 img_box gray-bc d-inline-flex align-items-center justify-content-center"><img :src="img('details-arrow.png')" alt=""></div>
                                    <div class="d-flex align-items-center justify-content-between group_h3 w-100">
                                        <h3 class="fs-16 m-0 main-color d-inline-flex align-items-center gap-4"><span class="dark-color">نوع المنافسة : </span>{{ typeLabels[tender.type] }}</h3>
                                        <h3 class="fs-16 m-0 main-color d-inline-flex align-items-center gap-4"><span class="dark-color">حالة المنافسة : </span>{{ statusLabels[tender.status] }}</h3>
                                    </div>
                                </li>
                                <li class="d-flex align-items-center w-100">
                                    <div class="me_16 img_box gray-bc d-inline-flex align-items-center justify-content-center"><img :src="img('details-arrow.png')" alt=""></div>
                                    <div class="d-flex align-items-center justify-content-between group_h3 w-100">
                                        <h3 class="fs-16 m-0 main-color d-inline-flex align-items-center gap-4"><span class="dark-color">رقم المنافسة : </span>{{ tender.tender_no }}</h3>
                                        <h3 class="fs-16 m-0 main-color d-inline-flex align-items-center gap-4"><span class="dark-color"> الرقم المرجعى : </span>{{ tender.reference_no ?? '—' }}</h3>
                                    </div>
                                </li>
                                <li class="d-flex align-items-center w-100">
                                    <div class="me_16 img_box gray-bc d-inline-flex align-items-center justify-content-center"><img :src="img('details-arrow.png')" alt=""></div>
                                    <h3 class="fs-16 m-0 main-color d-inline-flex align-items-center gap-4"><span class="dark-color"> اسم المنافسة : </span>{{ tender.name }}</h3>
                                </li>
                                <li v-if="tender.purpose" class="d-flex align-items-start w-100">
                                    <div class="me_16 img_box gray-bc d-inline-flex align-items-center justify-content-center"><img :src="img('details-arrow.png')" alt=""></div>
                                    <div class="description_expand w-100">
                                        <h3 class="fs-16 m-0 main-color d-flex align-items-start gap-4 justify-content-between pe_16">
                                            <div class="d-flex align-items-start gap-4">
                                                <span class="dark-color flex-shrink-0"> الغرض منها : </span>
                                                <span class="description_expand__content">{{ descExpanded ? tender.purpose : tender.purpose.slice(0, 90) + (tender.purpose.length > 90 ? ' ...' : '') }}</span>
                                            </div>
                                            <button type="button" class="description_expand__toggle flex-shrink-0" @click="descExpanded = !descExpanded" aria-label="عرض المزيد">
                                                <img :src="img('add-cricle.png')" alt="" style="filter: brightness(0.5);">
                                            </button>
                                        </h3>
                                    </div>
                                </li>
                                <li class="d-flex align-items-center w-100">
                                    <div class="me_16 img_box gray-bc d-inline-flex align-items-center justify-content-center"><img :src="img('details-arrow.png')" alt=""></div>
                                    <h3 class="fs-16 m-0 main-color d-inline-flex align-items-center gap-4"><span class="dark-color"> طالب الخدمة : </span>{{ tender.client?.company_name }}</h3>
                                </li>
                                <li class="d-flex align-items-center w-100">
                                    <div class="me_16 img_box gray-bc d-inline-flex align-items-center justify-content-center"><img :src="img('details-arrow.png')" alt=""></div>
                                    <div class="d-flex align-items-center justify-content-between group_h3 w-100">
                                        <h3 class="fs-16 m-0 main-color d-inline-flex align-items-center gap-4"><span class="dark-color">مدة العقد : </span>{{ tender.contract_duration_months ? tender.contract_duration_months + ' شهر' : '—' }}</h3>
                                        <h3 class="fs-16 m-0 main-color d-inline-flex align-items-center gap-4"><span class="dark-color"> التأمين : </span>{{ tender.insurance_required ? 'مطلوب' : 'غير مطلوب' }}</h3>
                                    </div>
                                </li>
                                <li class="d-flex align-items-center w-100">
                                    <div class="me_16 img_box gray-bc d-inline-flex align-items-center justify-content-center"><img :src="img('details-arrow.png')" alt=""></div>
                                    <div class="d-flex align-items-center justify-content-between group_h3 w-100">
                                        <h3 class="fs-16 m-0 main-color d-inline-flex align-items-center gap-4"><span class="dark-color">الضمان الإبتدائي : </span>{{ tender.initial_guarantee_required ? 'مطلوب' : 'غير مطلوب' }}</h3>
                                        <h3 class="fs-16 m-0 main-color d-inline-flex align-items-center gap-4"><span class="dark-color">قيمة الضمان الإبتدائي : </span>{{ num(tender.initial_guarantee_value) }}</h3>
                                    </div>
                                </li>
                                <li class="d-flex align-items-center w-100">
                                    <div class="me_16 img_box gray-bc d-inline-flex align-items-center justify-content-center"><img :src="img('details-arrow.png')" alt=""></div>
                                    <div class="d-flex align-items-center justify-content-between group_h3 w-100">
                                        <h3 class="fs-16 m-0 main-color d-inline-flex align-items-center gap-4"><span class="dark-color">الضمان النهائى : </span>{{ tender.final_guarantee_required ? 'مطلوب' : 'غير مطلوب' }}</h3>
                                        <h3 class="fs-16 m-0 main-color d-inline-flex align-items-center gap-4"><span class="dark-color">قيمة الضمان النهائى : </span>{{ num(tender.final_guarantee_value) }}</h3>
                                    </div>
                                </li>
                                <li class="d-flex align-items-center w-100">
                                    <div class="me_16 img_box main_bc d-inline-flex align-items-center justify-content-center"><img :src="img('details-file.png')" alt=""></div>
                                    <h3 class="fs-16 m-0 main-color d-inline-flex align-items-center gap-4"><span class="dark-color"> طريقة تقديم العروض : </span>{{ tender.submission_method ?? 'ملف للعرض الفني و ملف للعرض المالي' }}</h3>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Dates -->
                    <div class="col-lg-6">
                        <h3 class="fs-16 second-color d-inline-flex align-items-center gap-4 mb_24">
                            <div class="img_box second_bc d-flex align-items-center justify-content-center"><img :src="img('details-clock.png')" alt=""></div>
                            العناوين والمواعيد للمنافسة
                        </h3>
                        <div class="border_box border-top-0 mb_32 white_bc">
                            <div class="time_content pt_32 pb_32 pe_16 pst_16">
                                <ul class="d-flex align-items-start justify-content-between gap-4">
                                    <li class="p_12">
                                        <div class="title d-flex align-items-center gap-2 mb_24 fs-14"><div class="img_box d-flex align-items-center justify-content-center border-0"><img :src="img('calendar-blue.png')" alt=""></div>آخر موعد لإستلام الإستفسارات</div>
                                        <p class="d-flex align-items-end justify-content-between gap-4 mb-0"><span class="fs-14 fw-bold dark-color">{{ tender.questions_deadline ?? '—' }}<br>{{ tender.questions_deadline_hijri }}</span></p>
                                    </li>
                                    <li class="p_12">
                                        <div class="title d-flex align-items-center gap-2 mb_24 fs-14"><div class="img_box d-flex align-items-center justify-content-center border-0"><img :src="img('calendar-orange.png')" alt=""></div>آخر موعد لتقديم العروض</div>
                                        <p class="d-flex align-items-end justify-content-between gap-4 mb-0"><span class="fs-14 fw-bold dark-color">{{ tender.offers_deadline ?? '—' }} <br> {{ tender.offers_deadline_hijri }}</span></p>
                                    </li>
                                    <li class="p_12">
                                        <div class="title d-flex align-items-center gap-2 mb_24 fs-14"><div class="img_box d-flex align-items-center justify-content-center border-0"><img :src="img('clock.png')" alt=""></div>تاريخ ووقت فتح العروض</div>
                                        <p class="d-flex align-items-end justify-content-between gap-4 mb-0"><span class="fs-14 fw-bold dark-color">{{ tender.offers_open ?? '—' }} <br> {{ tender.offers_open_hijri }}</span><span class="fs-14 red-color">{{ tender.offers_open_time?.slice(0,5) }}</span></p>
                                    </li>
                                </ul>
                                <li class="d-flex align-items-center w-100 mt_24">
                                    <div class="me_16 img_box gray-bc d-inline-flex align-items-center justify-content-center"><img :src="img('details-arrow.png')" alt=""></div>
                                    <h3 class="fs-16 m-0 main-color d-inline-flex align-items-center gap-4"><span class="dark-color"> فترة التوقف : </span>{{ tender.standstill_period_days ?? '—' }}</h3>
                                </li>
                            </div>
                            <div class="time_content pt_32 pb_32 pe_16 pst_16 border-top-0">
                                <ul class="d-flex align-items-start justify-content-between gap-4">
                                    <li class="p_12">
                                        <div class="title d-flex align-items-center gap-2 mb_24 fs-14"><div class="img_box d-flex align-items-center justify-content-center border-0"><img :src="img('calendar-blue.png')" alt=""></div>التاريخ المتوقع للترسية</div>
                                        <p class="d-flex align-items-end justify-content-between gap-4 mb-0"><span class="fs-14 fw-bold dark-color">{{ tender.expected_award_date ?? '—' }} <br> {{ tender.expected_award_date_hijri }}</span></p>
                                    </li>
                                    <li class="p_12">
                                        <div class="title d-flex align-items-center gap-2 mb_24 fs-14"><div class="img_box d-flex align-items-center justify-content-center border-0"><img :src="img('calendar-orange.png')" alt=""></div>بداية إرسال الاسئلة والإستفسارات</div>
                                        <p class="d-flex align-items-end justify-content-between gap-4 mb-0"><span class="fs-14 fw-bold dark-color">{{ tender.questions_start ?? '—' }} <br> {{ tender.questions_start_hijri }}</span></p>
                                    </li>
                                    <li class="p_12">
                                        <div class="title d-flex align-items-center gap-2 mb_24 fs-14"><div class="img_box d-flex align-items-center justify-content-center border-0"><img :src="img('clock.png')" alt=""></div>تاريخ بدء الإعمال والخدمات</div>
                                        <p class="d-flex align-items-end justify-content-between gap-4 mb-0"><span class="fs-14 fw-bold dark-color">{{ tender.works_start ?? '—' }} <br> {{ tender.works_start_hijri }}</span><span class="fs-14 red-color">{{ tender.works_start_time?.slice(0,5) }}</span></p>
                                    </li>
                                </ul>
                                <li class="d-flex align-items-center w-100 mt_24">
                                    <div class="me_16 img_box gray-bc d-inline-flex align-items-center justify-content-center"><img :src="img('details-arrow.png')" alt=""></div>
                                    <h3 class="fs-16 m-0 main-color d-inline-flex align-items-center gap-4"><span class="dark-color"> اقصى مدة للاجابة على الاستفسارات : </span>{{ tender.max_answer_duration_days ?? '—' }}</h3>
                                </li>
                            </div>
                            <div class="info d-flex align-items-center justify-content-between p_24 gap-4">
                                <div class="time w-100">
                                    <div class="numbers d-flex align-items-center gap-4">
                                        <img :src="img('file.png')" alt="">
                                        {{ tender.serial_no }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Classification + location + actions -->
                    <div class="col-12">
                        <div class="d-flex align-items-center justify-content-between mb_24 title_with_action">
                            <h3 class="fs-16 dark-color d-inline-flex align-items-center gap-4 m-0">
                                <div class="img_box third_bc d-flex align-items-center justify-content-center"><img :src="img('details-map.png')" alt=""></div>
                                مجال التصنيف وموقع التنفيذ والتقديم
                            </h3>
                            <div class="d-flex align-items-center gap-4">
                                <a href="#" class="main_btn d-flex align-items-center gap-2" role="button" @click.prevent="bookOpen = true">
                                    <img :src="img('details-pdf.png')" alt=""> كراسة الشروط
                                </a>
                                <a v-if="offersOpen" href="#" class="main_btn second d-flex align-items-center gap-2" role="button" @click.prevent="proposalOpen = true">
                                    <img :src="img('details-edit.png')" alt=""> تقديم عروض
                                </a>
                                <span v-else class="main_btn second d-flex align-items-center gap-2" style="opacity:.55; cursor:not-allowed;" title="انتهى آخر موعد لتقديم العروض">
                                    <img :src="img('details-edit.png')" alt=""> انتهى التقديم
                                </span>
                            </div>
                        </div>
                        <div class="border_box white_bc p_16">
                            <div class="row">
                                <div class="col-lg-6">
                                    <ul class="d-flex flex-column gap-4 mb_24">
                                        <li class="d-flex align-items-center w-100">
                                            <div class="me_16 img_box gray-bc d-inline-flex align-items-center justify-content-center"><img :src="img('details-arrow.png')" alt=""></div>
                                            <div class="d-flex align-items-center justify-content-between group_h3 w-100">
                                                <h3 class="fs-16 m-0 main-color d-inline-flex align-items-center gap-4"><span class="dark-color">القطاع الرئيسي : </span>{{ tender.category?.name ?? '—' }}</h3>
                                                <h3 class="fs-16 m-0 main-color d-inline-flex align-items-center gap-4"><span class="dark-color">النشاط الفرعي : </span>{{ tender.subcategory?.name ?? '—' }}</h3>
                                            </div>
                                        </li>
                                        <li class="d-flex align-items-center w-100">
                                            <div class="me_16 img_box gray-bc d-inline-flex align-items-center justify-content-center"><img :src="img('details-arrow.png')" alt=""></div>
                                            <h3 class="fs-16 m-0 main-color d-inline-flex align-items-center gap-4"><span class="dark-color"> تشمل المنافسة بنود توريد : </span>{{ tender.includes_supply_items ? 'يوجد' : 'لا يوجد' }}</h3>
                                        </li>
                                    </ul>
                                    <h4 class="fs-14 dark-color d-inline-flex align-items-center gap-4">
                                        <div class="img_box bg-transparent d-flex align-items-center justify-content-center"><img :src="img('details-file-green.png')" alt=""></div>
                                        نشاط المنافسة
                                    </h4>
                                    <div class="border_box p_16 white_bc mb_32 fs-14 dark-color">{{ tender.activity_description ?? '—' }}</div>
                                </div>
                                <div class="col-lg-6">
                                    <h4 class="fs-14 dark-color d-inline-flex align-items-center gap-4">
                                        <div class="img_box bg-transparent d-flex align-items-center justify-content-center"><img :src="img('details-file-green.png')" alt=""></div>
                                        مكان التنفيذ
                                    </h4>
                                    <div class="table-responsive mb_32">
                                        <table class="custom_table text-center mb-0 w-100">
                                            <thead><tr><th class="main-color">المنطقة</th><th class="main-color">المدينة</th></tr></thead>
                                            <tbody>
                                                <tr v-for="g in groupedLocations" :key="g.region"><td class="dark-color">{{ g.region }}</td><td class="dark-color">{{ g.cities }}</td></tr>
                                                <tr v-if="!groupedLocations.length"><td class="dark-color" colspan="2">—</td></tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Tender book (كراسة الشروط) modal -->
        <teleport to="body">
            <div v-if="bookOpen" class="modal-backdrop fade show"
                style="position:fixed; inset:0; background-color:rgba(0,0,0,.5); z-index:99999;" @click="bookOpen = false"></div>
            <div class="modal fade tender_book_modal" :class="{ show: bookOpen }"
                :style="{ display: bookOpen ? 'block' : 'none', position: 'fixed', inset: '0', zIndex: 9999999 }" tabindex="-1" role="dialog">
                <div class="modal-dialog modal-dialog-centered tender_book_modal__dialog" role="document">
                    <div class="modal-content inner_model">
                        <div class="d-flex align-items-center justify-content-end">
                            <button type="button" class="close filter_modal__close m-0" aria-label="إغلاق" @click="bookOpen = false"><img :src="img('close.png')" alt=""></button>
                        </div>
                        <div class="modal-body tender_book_modal__body p-0">
                            <div class="d-flex align-items-start gap-4 mb_32">
                                <div class="img_box second_bc d-flex align-items-center justify-content-center"><img :src="img('details-pdf.png')" alt=""></div>
                                <h3 class="fs-18 dark-color m-0">كراسة الشروط<span class="fs-14 dark-color mb-0 d-block fw-normal mt_8">برجاء تحويل قيمة كراسة الشروط علي بيانات الحساب البنكي التالي وارفاق ايصال التحويل</span></h3>
                            </div>
                            <div class="table-responsive tender_book_modal__table-wrap">
                                <table class="custom_table text-center mb-0 fs-14 w-100">
                                    <thead><tr><th class="main-color">اسم المستفيد</th><th class="main-color">اسم البنك</th><th class="main-color">رقم الآيبان</th></tr></thead>
                                    <tbody><tr><td class="dark-color">{{ tender.client?.bank_beneficiary_name ?? tender.client?.company_name }}</td><td class="dark-color">{{ tender.client?.bank_name ?? '—' }}</td><td class="dark-color" dir="ltr">{{ tender.client?.bank_iban ?? '—' }}</td></tr></tbody>
                                </table>
                            </div>
                            <form class="tender_book_modal__form" @submit.prevent="submitBook">
                                <div class="row align-items-end">
                                    <div class="col-lg-4 col-md-6 col-sm-6 mt_16">
                                        <label class="d-flex align-items-center gap-2 fs-16 main-color"><img :src="img('details-arrow.png')" alt="" class="m-0">قيمة كراسة الشروط</label>
                                        <div class="tender_book_modal__box tender_book_modal__box--value fs-18 fw-bold dark-color">{{ num(tender.brochure_price) }}</div>
                                    </div>
                                    <div class="col-lg-4 col-md-6 col-sm-6 mt_16">
                                        <label class="d-flex align-items-center gap-2 fs-16 main-color"><img :src="img('details-arrow.png')" alt="" class="m-0">ارفاق ايصال التحويل</label>
                                        <label class="tender_book_modal__upload mb-0">
                                            <input type="file" class="sr-only" accept=".pdf,image/jpeg,image/png,image/webp" :disabled="!isProvider" @change="onBookFile">
                                            <span class="tender_book_modal__upload-inner d-flex align-items-center gap-4"><span class="dark-color fs-14">{{ bookForm.receipt_file?.name || 'ارفاق الملف' }}</span></span>
                                        </label>
                                        <FileTypeHint exts="PDF أو صورة (JPG/PNG/WEBP)" :max-mb="5" />
                                        <small v-if="bookForm.errors.receipt_file" class="red-color d-block">{{ bookForm.errors.receipt_file }}</small>
                                    </div>
                                    <div class="col-lg-4 col-12">
                                        <div class="d-flex justify-content-end mt_24">
                                            <button v-if="isProvider" type="submit" :disabled="bookForm.processing" class="main_btn m-0 tender_book_modal__submit">ارسال الطلب</button>
                                            <a v-else href="/login" class="main_btn m-0">سجّل دخولك كمورّد</a>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </teleport>

        <!-- Proposal modal -->
        <teleport to="body">
            <div v-if="proposalOpen" class="modal-backdrop fade show"
                style="position:fixed; inset:0; background-color:rgba(0,0,0,.5); z-index:99999;" @click="proposalOpen = false"></div>
            <div class="modal fade tender_book_modal tender_book_modal--proposal" :class="{ show: proposalOpen }"
                :style="{ display: proposalOpen ? 'block' : 'none', position: 'fixed', inset: '0', zIndex: 9999999 }" tabindex="-1" role="dialog">
                <div class="modal-dialog modal-dialog-centered tender_book_modal__dialog" role="document">
                    <div class="modal-content inner_model">
                        <div class="d-flex align-items-center justify-content-end">
                            <button type="button" class="close filter_modal__close m-0" aria-label="إغلاق" @click="proposalOpen = false"><img :src="img('close.png')" alt=""></button>
                        </div>
                        <div class="modal-body tender_book_modal__body p-0">
                            <div class="d-flex align-items-center gap-4 mb_32">
                                <div class="img_box main_bc d-flex align-items-center justify-content-center"><img :src="img('details-edit.png')" alt=""></div>
                                <h3 class="fs-18 dark-color m-0">تقديم عرض للمنافسة</h3>
                            </div>
                            <form class="tender_book_modal__form" @submit.prevent="submitProposal">
                                <div class="row align-items-end">
                                    <div class="col-lg-4 col-md-6 col-sm-6 mb_24 mb-md-0">
                                        <label class="d-flex align-items-center gap-2 fs-16 main-color mb_12"><img :src="img('details-arrow.png')" alt="" class="m-0">ملف العرض الفني</label>
                                        <label class="tender_book_modal__upload mb-0"><input type="file" class="sr-only" accept=".pdf,.doc,.docx,image/jpeg,image/png,image/webp" :disabled="!isProvider" @change="onProposalFile('technical_file', $event)"><span class="tender_book_modal__upload-inner d-flex align-items-center gap-4"><span class="dark-color fs-14">{{ proposalForm.technical_file?.name || 'ارفاق الملف' }}</span></span></label>
                                        <FileTypeHint exts="PDF أو Word أو صورة (JPG/PNG/WEBP)" :max-mb="10" />
                                        <small v-if="proposalForm.errors.technical_file" class="red-color d-block">{{ proposalForm.errors.technical_file }}</small>
                                    </div>
                                    <div class="col-lg-4 col-md-6 col-sm-6 mb_24 mb-md-0">
                                        <label class="d-flex align-items-center gap-2 fs-16 main-color mb_12"><img :src="img('details-arrow.png')" alt="" class="m-0">ملف العرض المالي</label>
                                        <label class="tender_book_modal__upload mb-0"><input type="file" class="sr-only" accept=".pdf,.doc,.docx,image/jpeg,image/png,image/webp" :disabled="!isProvider" @change="onProposalFile('financial_file', $event)"><span class="tender_book_modal__upload-inner d-flex align-items-center gap-4"><span class="dark-color fs-14">{{ proposalForm.financial_file?.name || 'ارفاق الملف' }}</span></span></label>
                                        <FileTypeHint exts="PDF أو Word أو صورة (JPG/PNG/WEBP)" :max-mb="10" />
                                        <small v-if="proposalForm.errors.financial_file" class="red-color d-block">{{ proposalForm.errors.financial_file }}</small>
                                    </div>
                                    <div class="col-lg-4 col-md-12 col-sm-12 mb_24 mb-md-0">
                                        <label class="d-flex align-items-center gap-2 fs-16 main-color mb_12"><img :src="img('details-arrow.png')" alt="" class="m-0">قيمة العرض المالي</label>
                                        <input type="text" class="form-control tender_book_modal__value-input" placeholder="أدخل القيمة" inputmode="decimal" :disabled="!isProvider" v-model="proposalForm.financial_value">
                                        <small v-if="proposalForm.errors.financial_value" class="red-color">{{ proposalForm.errors.financial_value }}</small>
                                    </div>
                                </div>
                                <div class="mt_24">
                                    <p class="fs-16 dark-color mb-0 tender_book_modal__terms-text">أتعهد وأقر أنا مقدم الخدمة بالالتزام بدفع عمولة المنصة والبالغة نسبتها {{ tender['commission_rate'] ?? 1 }}% من قيمة العقد الإجمالي المبرم بيني وبين الجهة المستفيدة من خلال منصة اتفاق، وأقر بأن هذه العمولة ثابتة في ذمتي وواجبة السداد لحساب المنصة.</p>
                                    <div class="d-flex justify-content-start mt_24">
                                        <label class="d-inline-flex align-items-center justify-content-start gap-2 mb-0"><input type="checkbox" :disabled="!isProvider" v-model="proposalForm.declaration_accepted"><span class="fs-14 red-color">أقر وأتعهد بذلك</span></label>
                                    </div>
                                    <small v-if="proposalForm.errors.declaration_accepted" class="red-color d-block mt_8">{{ proposalForm.errors.declaration_accepted }}</small>
                                </div>
                                <div class="d-flex justify-content-end mt_24">
                                    <button v-if="isProvider" type="submit" :disabled="proposalForm.processing" class="main_btn m-0 tender_book_modal__submit">ارسال العرض</button>
                                    <a v-else href="/login" class="main_btn m-0">سجّل دخولك كمورّد لتقديم عرض</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </teleport>
    </PublicLayout>
</template>
