<script setup lang="ts">
import Countdown from '@/components/Countdown.vue';
import FileTypeHint from '@/components/FileTypeHint.vue';
import SlicePagination from '@/components/SlicePagination.vue';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

interface Paged<T> { data: T[]; links: { url: string | null; label: string; active: boolean }[]; total: number }

interface Tender {
    id: number; tender_no: string; reference_no: string | null; name: string;
    brochure_price: string; contract_duration_months: number | null; published_at: string | null;
    offers_deadline: string | null; offers_deadline_hijri: string | null; offers_deadline_time: string | null;
    offers_open: string | null; offers_open_hijri: string | null; offers_open_time: string | null;
    status: string; client?: { company_name: string } | null; contract?: { id: number; status: string } | null;
}
interface OfferRow {
    id: number; tender: Tender; technical_file: string | null; financial_file: string | null;
    financial_value: string | null; status: string; is_awarded: boolean;
    commission_amount: number | null; commission_status: string | null; contract?: { id: number; status: string } | null;
}

const props = defineProps<{
    providerName: string;
    providerStatus: string | null;
    activeOffers: Paged<OfferRow>;
    awardedOffers: Paged<OfferRow>;
    platformBank: { beneficiary: string; bank: string; iban: string };
    stats: { applied: number; awarded: number; contracts: number };
}>();

const img = (n: string) => `/slice/assets/images/${n}`;
const offerFile = (id: number, type: 'technical' | 'financial') => `/offers/${id}/files/${type}`;
const offerPreview = (id: number, type: 'technical' | 'financial') => `/offers/${id}/files/${type}?inline=1`;
const num = (v: string | number | null) => (v != null && v !== '' ? Number(v).toLocaleString('en', { minimumFractionDigits: 2 }) : '—');
const price = (v: string) => (Number(v) > 0 ? `${Number(v).toLocaleString('en')} ريال` : 'مجانية');
const durationLabels: Record<number, string> = { 3: '3 شهور', 6: '6 شهور', 12: '1 سنة', 24: '2 سنة', 36: '3 سنة', 48: '4 سنة', 60: '5 سنة' };
const duration = (m: number | null) => (m ? (durationLabels[m] ?? `${m} شهر`) : '—');
const offerStatusLabels: Record<string, string> = { submitted: 'مقدّم', under_review: 'قيد المراجعة', awarded: 'تم الترسية', rejected: 'مرفوض' };
const commissionLabels: Record<string, string> = { pending: 'قيد المراجعة', paid: 'تم السداد', rejected: 'مرفوض' };
const contractLabels: Record<string, string> = { awaiting_signature: 'بانتظار التوقيع', active: 'ساري', completed: 'مكتمل', cancelled: 'ملغي' };
const remaining = (d: string | null) => { if (!d) return '—'; const x = Math.ceil((new Date(d).getTime() - Date.now()) / 86400000); return x < 0 ? 'انتهى' : `${x} يوم متبقي`; };

const tab = ref<'active' | 'awarded'>('active');
const logout = () => router.post('/logout');

const commissionOpen = ref(false);
const activeOffer = ref<OfferRow | null>(null);
const commissionForm = useForm<{ receipt_file: File | null }>({ receipt_file: null });

const openCommission = (o: OfferRow) => { activeOffer.value = o; commissionForm.reset(); commissionForm.clearErrors(); commissionOpen.value = true; };
const onReceipt = (e: Event) => { commissionForm.receipt_file = (e.target as HTMLInputElement).files?.[0] ?? null; };
const submitCommission = () => {
    if (!activeOffer.value) return;
    commissionForm.post(`/provider/offers/${activeOffer.value.id}/commission-payment`, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => { commissionOpen.value = false; },
    });
};
</script>

<template>
    <Head title="لوحة تحكم المورّد" />

    <PublicLayout>
        <section class="tender_details">
            <div class="container">
                <div class="row">
                    <div class="col-12 d-flex align-items-end justify-content-between flex-wrap mb_32 position-relative">
                        <div class="d-flex align-items-center gap-2 flex-column">
                            <h3 class="fs-16 dark-color d-inline-flex flex-column gap-2 mb_16">
                                مرحبا بكم فى منصة اتفاق :
                                <span class="main-color fw-bold fs-32">{{ providerName }}</span>
                            </h3>
                            <div class="d-flex justify-content-start gap-4 w-100">
                                <Link href="/provider/profile" class="d-inline-flex align-items-center dark-color gap-2">
                                    <div class="img_box light_main_bc d-flex align-items-center justify-content-center"><img :src="img('profile-edit.png')" alt=""></div>
                                    <span class="dark_color">تحديث بياناتى</span>
                                </Link>
                                <a href="#" @click.prevent="logout" class="d-inline-flex align-items-center dark-color gap-2">
                                    <div class="img_box light_third_bc d-flex align-items-center justify-content-center"><img :src="img('profile-logout.png')" alt=""></div>
                                    <span class="dark_color">تسجيل خروج</span>
                                </a>
                            </div>
                        </div>
                        <ul v-if="providerStatus === 'approved'" class="d-flex align-items-center gap-4 profile_stats three_item">
                            <li class="text-center p_16 border-radius fs-16 dark-color d-flex flex-column gap-2 main_border"><span class="fw-bold fs-24 main-color">{{ stats.applied }}</span>منافسات تم التقديم عليها</li>
                            <li class="text-center p_16 border-radius fs-16 dark-color d-flex flex-column gap-2 second_border"><span class="fw-bold fs-24 second-color">{{ stats.awarded }}</span>منافسات تمت الترسية لي</li>
                            <li class="text-center p_16 border-radius fs-16 dark-color d-flex flex-column gap-2 dark_border"><span class="fw-bold fs-24 dark-color">{{ stats.contracts }}</span>عقود قائمة</li>
                        </ul>
                    </div>

                    <div v-if="providerStatus !== 'approved'" class="col-12">
                        <div class="border_box p_24 white_bc text-center">
                            <div class="img_box light_third_bc d-inline-flex align-items-center justify-content-center mb_16" style="width:64px;height:64px;"><img :src="img('details-file.png')" alt="" style="filter: grayscale(1);"></div>
                            <h3 v-if="providerStatus === 'rejected'" class="fs-20 red-color fw-bold mb_8">تم رفض اعتماد حسابك</h3>
                            <h3 v-else class="fs-20 fw-bold mb_8" style="color:#a85b13;">حسابك قيد المراجعة من إدارة المنصة</h3>
                            <p class="fs-16 dark-color m-0">
                                {{ providerStatus === 'rejected'
                                    ? 'يرجى التواصل مع إدارة المنصة لمزيد من التفاصيل.'
                                    : 'لا يمكنك تصفّح المنافسات أو تحميل كراسات الشروط أو تقديم العروض حتى يتم اعتماد حسابك.' }}
                            </p>
                        </div>
                    </div>

                    <div v-if="providerStatus === 'approved'" class="col-12 d-flex align-items-center justify-content-end gap-4 flex-wrap">
                        <Link href="/provider/booklets" class="main_btn shadow d-inline-flex align-items-center gap-2 pt_4 pb_4 mb_24">
                            <span class="img_box d-inline-flex align-items-center justify-content-center fw-bold fs-24"><img :src="img('details-pdf.png')" alt=""></span>
                            <span>تحميل كراسات الشروط</span>
                        </Link>
                    </div>

                    <div v-if="providerStatus === 'approved'" class="col-12 d-flex align-items-end justify-content-between gap-4 flex-wrap items_search">
                        <ul class="nav nav-tabs partc_tabs w-100" role="tablist">
                            <li class="nav-item" role="presentation"><a class="nav-link" :class="{ active: tab === 'active' }" href="#" @click.prevent="tab = 'active'">منافسات تم التقديم عليها</a></li>
                            <li class="nav-item" role="presentation"><a class="nav-link" :class="{ active: tab === 'awarded' }" href="#" @click.prevent="tab = 'awarded'">منافسات تمت الترسية لي</a></li>
                        </ul>
                    </div>

                    <div v-if="providerStatus === 'approved'" class="col-12">
                        <div class="tab-content mt_32">
                            <div v-show="tab === 'active'">
                                <p v-if="activeOffers.data.length === 0" class="border_box p_24 white_bc text-center dark-color">لم تقدّم على أي منافسة بعد</p>
                                <div v-for="o in activeOffers.data" :key="o.id" class="border_box p_24 white_bc mb_24">
                                    <div class="table-responsive mb_24">
                                        <table class="table table-bordered w-100 mb-0 profile_table">
                                            <thead><tr>
                                                <th class="text-center">رقم المنافسة</th><th class="text-center">الرقم المرجعي</th>
                                                <th class="text-center big-cell">اسم المنافسة</th><th class="text-center">اسم المستفيد</th>
                                                <th class="text-center">قيمة كراسة الشروط</th><th class="text-center">مدة العقد</th><th class="text-center">حالة العرض المقدم</th>
                                            </tr></thead>
                                            <tbody><tr>
                                                <td class="text-center dark-color">{{ o.tender.tender_no }}</td>
                                                <td class="text-center dark-color">{{ o.tender.reference_no ?? '—' }}</td>
                                                <td class="text-center main-color big-cell"><a :href="`/tenders/${o.tender.id}`" class="tender_details__link main-color">{{ o.tender.name }}</a></td>
                                                <td class="text-center main-color">{{ o.tender.client?.company_name ?? '—' }}</td>
                                                <td class="text-center main-color">{{ price(o.tender.brochure_price) }}</td>
                                                <td class="text-center main-color">{{ duration(o.tender.contract_duration_months) }}</td>
                                                <td class="text-center dark-color">{{ offerStatusLabels[o.status] ?? '—' }}</td>
                                            </tr></tbody>
                                        </table>
                                    </div>
                                    <div class="table-responsive mb_24">
                                        <table class="table table-bordered w-100 mb-0 profile_table">
                                            <thead><tr>
                                                <th class="text-center">تاريخ النشر</th><th class="text-center">آخر موعد لتقديم العروض</th>
                                                <th class="text-center">تاريخ ووقت فتح العروض</th><th class="text-center">الوقت المتبقي</th>
                                                <th class="text-center">ملفات العرض المقدمة</th><th class="text-center">قيمة العرض المالي</th>
                                            </tr></thead>
                                            <tbody><tr>
                                                <td class="text-center dark-color"><span class="d-block">{{ o.tender.published_at ?? '—' }}</span></td>
                                                <td class="text-center main-color"><span class="d-block">{{ o.tender.offers_deadline ?? '—' }}</span><span class="d-block">{{ o.tender.offers_deadline_hijri }}</span><span v-if="o.tender.offers_deadline_time" class="d-block red-color">{{ o.tender.offers_deadline_time?.slice(0,5) }}</span></td>
                                                <td class="text-center main-color"><span class="d-block">{{ o.tender.offers_open ?? '—' }}</span><span class="d-block">{{ o.tender.offers_open_hijri }}</span><span v-if="o.tender.offers_open_time" class="d-block red-color">{{ o.tender.offers_open_time?.slice(0,5) }}</span></td>
                                                <td class="text-center main-color"><Countdown :deadline="o.tender.offers_deadline" :time="o.tender.offers_deadline_time" /></td>
                                                <td class="text-center">
                                                    <div class="d-flex flex-column align-items-center gap-2">
                                                        <a v-if="o.technical_file" :href="offerPreview(o.id, 'technical')" target="_blank" rel="noopener" class="second-color"><u>معاينة الفني</u></a>
                                                        <a v-if="o.technical_file" :href="offerFile(o.id, 'technical')" class="second-color"><u>تحميل الفني</u></a>
                                                        <a v-if="o.financial_file" :href="offerPreview(o.id, 'financial')" target="_blank" rel="noopener" class="second-color"><u>معاينة المالي</u></a>
                                                        <a v-if="o.financial_file" :href="offerFile(o.id, 'financial')" class="second-color"><u>تحميل المالي</u></a>
                                                        <span v-if="!o.technical_file && !o.financial_file" class="dark-color fs-14">—</span>
                                                    </div>
                                                </td>
                                                <td class="text-center dark-color ltr">{{ num(o.financial_value) }}</td>
                                            </tr></tbody>
                                        </table>
                                    </div>
                                </div>
                                <SlicePagination :links="activeOffers.links" />
                            </div>

                            <div v-show="tab === 'awarded'">
                                <p v-if="awardedOffers.data.length === 0" class="border_box p_24 white_bc text-center dark-color">لا توجد منافسات تمت ترسيتها لك</p>
                                <div v-for="o in awardedOffers.data" :key="o.id" class="border_box p_24 white_bc mb_24">
                                    <div class="table-responsive mb_24">
                                        <table class="table table-bordered w-100 mb-0 profile_table">
                                            <thead><tr>
                                                <th class="text-center">رقم المنافسة</th><th class="text-center">الرقم المرجعي</th>
                                                <th class="text-center big-cell">اسم المنافسة</th><th class="text-center">اسم المستفيد</th>
                                                <th class="text-center">قيمة كراسة الشروط</th><th class="text-center">مدة العقد</th><th class="text-center">حالة العرض المقدم</th>
                                            </tr></thead>
                                            <tbody><tr>
                                                <td class="text-center dark-color">{{ o.tender.tender_no }}</td>
                                                <td class="text-center dark-color">{{ o.tender.reference_no ?? '—' }}</td>
                                                <td class="text-center main-color big-cell"><a :href="`/tenders/${o.tender.id}`" class="tender_details__link main-color">{{ o.tender.name }}</a></td>
                                                <td class="text-center main-color">{{ o.tender.client?.company_name ?? '—' }}</td>
                                                <td class="text-center main-color">{{ price(o.tender.brochure_price) }}</td>
                                                <td class="text-center main-color">{{ duration(o.tender.contract_duration_months) }}</td>
                                                <td class="text-center dark-color">تم الترسية</td>
                                            </tr></tbody>
                                        </table>
                                    </div>
                                    <div class="table-responsive mb_24">
                                        <table class="table table-bordered w-100 mb-0 profile_table contract_award_table">
                                            <thead><tr>
                                                <th class="text-center">ملفات العرض المقدمة</th><th class="text-center">قيمة العرض المالي</th>
                                                <th class="text-center">عمولة منصة اتفاق</th><th class="text-center">حالة العمولة</th>
                                                <th class="text-center">حالة العقد</th><th class="text-center">العقد الإلكتروني</th>
                                            </tr></thead>
                                            <tbody><tr>
                                                <td class="text-center">
                                                    <div class="d-flex flex-column align-items-center gap-2">
                                                        <a v-if="o.technical_file" :href="offerPreview(o.id, 'technical')" target="_blank" rel="noopener" class="contract_award_table__link">معاينة الفني</a>
                                                        <a v-if="o.technical_file" :href="offerFile(o.id, 'technical')" class="contract_award_table__link">تحميل الفني</a>
                                                        <a v-if="o.financial_file" :href="offerPreview(o.id, 'financial')" target="_blank" rel="noopener" class="contract_award_table__link">معاينة المالي</a>
                                                        <a v-if="o.financial_file" :href="offerFile(o.id, 'financial')" class="contract_award_table__link">تحميل المالي</a>
                                                        <span v-if="!o.technical_file && !o.financial_file" class="dark-color">—</span>
                                                    </div>
                                                </td>
                                                <td class="text-center dark-color ltr">{{ num(o.financial_value) }}</td>
                                                <td class="text-center dark-color">
                                                    <span class="d-block ltr mb_8">{{ num(o.commission_amount) }}</span>
                                                    <a v-if="o.commission_status !== 'paid'" href="#" @click.prevent="openCommission(o)" class="red-color fs-14"><u>سداد العمولة للمنصة</u></a>
                                                </td>
                                                <td class="text-center contract_award_table__status" :class="o.commission_status === 'paid' ? 'contract_award_table__status--success' : 'contract_award_table__status--pending'">{{ o.commission_status ? commissionLabels[o.commission_status] : 'لم يتم السداد' }}</td>
                                                <td class="text-center contract_award_table__status contract_award_table__status--pending">{{ o.contract ? contractLabels[o.contract.status] : '—' }}</td>
                                                <td class="text-center"><a v-if="o.contract" :href="`/contract/${o.contract.id}`" class="contract_award_table__link">مشاهدة العقد</a><span v-else class="dark-color">—</span></td>
                                            </tr></tbody>
                                        </table>
                                    </div>
                                </div>
                                <SlicePagination :links="awardedOffers.links" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <teleport to="body">
            <div v-if="commissionOpen" class="modal-backdrop fade show" style="position:fixed; inset:0; background-color:rgba(0,0,0,.5); z-index:99999;" @click="commissionOpen = false"></div>
            <div class="modal fade tender_book_modal" :class="{ show: commissionOpen }" :style="{ display: commissionOpen ? 'block' : 'none', position: 'fixed', inset: '0', zIndex: 9999999 }" tabindex="-1" role="dialog">
                <div class="modal-dialog modal-dialog-centered tender_book_modal__dialog" role="document">
                    <div class="modal-content inner_model">
                        <div class="d-flex align-items-center justify-content-end">
                            <button type="button" class="close filter_modal__close m-0" aria-label="إغلاق" @click="commissionOpen = false"><img :src="img('close.png')" alt=""></button>
                        </div>
                        <div class="modal-body tender_book_modal__body p-0">
                            <div class="d-flex align-items-start gap-4 mb_32">
                                <h3 class="fs-18 m-0">
                                    <span class="main-color d-block fw-bold">سداد عمولة المنصة</span>
                                    <span class="fs-14 dark-color mb-0 d-block fw-normal mt_8">برجاء تحويل عمولة المنصة علي بيانات الحساب البنكي التالي وارفاق ايصال التحويل</span>
                                </h3>
                            </div>
                            <div class="table-responsive tender_book_modal__table-wrap">
                                <table class="custom_table text-center mb-0 fs-14 w-100">
                                    <thead><tr><th class="main-color">اسم المستفيد</th><th class="main-color">اسم البنك</th><th class="main-color">رقم الآيبان</th></tr></thead>
                                    <tbody><tr><td class="dark-color">{{ platformBank.beneficiary }}</td><td class="dark-color">{{ platformBank.bank }}</td><td class="dark-color" dir="ltr">{{ platformBank.iban }}</td></tr></tbody>
                                </table>
                            </div>
                            <form class="tender_book_modal__form" @submit.prevent="submitCommission">
                                <div class="row align-items-end">
                                    <div class="col-lg-4 col-md-6 col-sm-6 mt_16">
                                        <label class="d-flex align-items-center gap-2 fs-16 main-color"><img :src="img('details-arrow.png')" alt="" class="m-0">عمولة المنصة</label>
                                        <div class="tender_book_modal__box tender_book_modal__box--value fs-18 fw-bold dark-color">{{ num(activeOffer?.commission_amount ?? null) }}</div>
                                    </div>
                                    <div class="col-lg-4 col-md-6 col-sm-6 mt_16">
                                        <label class="d-flex align-items-center gap-2 fs-16 main-color"><img :src="img('details-arrow.png')" alt="" class="m-0">ارفاق ايصال التحويل</label>
                                        <label class="tender_book_modal__upload mb-0">
                                            <input type="file" class="sr-only" accept=".pdf,image/jpeg,image/png,image/webp" @change="onReceipt">
                                            <span class="tender_book_modal__upload-inner d-flex align-items-center gap-4"><span class="dark-color fs-14">{{ commissionForm.receipt_file?.name || 'ارفاق الملف' }}</span></span>
                                        </label>
                                        <FileTypeHint exts="PDF أو صورة (JPG/PNG/WEBP)" :max-mb="5" />
                                        <small v-if="commissionForm.errors.receipt_file" class="red-color d-block">{{ commissionForm.errors.receipt_file }}</small>
                                    </div>
                                    <div class="col-lg-4 col-12">
                                        <div class="d-flex justify-content-end mt_24">
                                            <button type="submit" :disabled="commissionForm.processing" class="main_btn m-0 tender_book_modal__submit">ارسال</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </teleport>
    </PublicLayout>
</template>
