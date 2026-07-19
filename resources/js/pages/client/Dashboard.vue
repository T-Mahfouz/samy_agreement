<script setup lang="ts">
import Countdown from '@/components/Countdown.vue';
import SlicePagination from '@/components/SlicePagination.vue';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

interface Paged<T> { data: T[]; links: { url: string | null; label: string; active: boolean }[]; total: number }
import { ref } from 'vue';

interface Loc { id: number; region?: { name: string } | null; city?: { name: string } | null }
interface Tender {
    id: number; tender_no: string; reference_no: string | null; name: string; type: string;
    brochure_price: string; contract_duration_months: number | null; published_at: string | null;
    offers_deadline: string | null; offers_deadline_hijri: string | null; offers_deadline_time: string | null;
    offers_open: string | null; offers_open_hijri: string | null; offers_open_time: string | null;
    status: string; offers_count: number; locations?: Loc[];
    awarded_offer?: { financial_value: string | null; provider?: { company_name: string } | null } | null;
    contract?: { id: number; status: string } | null;
}

const props = defineProps<{
    clientName: string;
    activeTenders: Paged<Tender>;
    awardedTenders: Paged<Tender>;
    stats: { active: number; awarded: number; offers: number; contracts: number };
}>();

const img = (n: string) => `/slice/assets/images/${n}`;
const typeLabels: Record<string, string> = { general: 'منافسة عامة', direct_purchase: 'شراء مباشر', limited: 'محدودة' };
const statusLabels: Record<string, string> = { active: 'نشطة', examination: 'فحص العروض', awarding: 'مرحلة الترسية', awarded: 'تم الترسية', cancelled: 'ملغاة' };
const contractStatusLabels: Record<string, string> = { awaiting_signature: 'بانتظار التوقيع', active: 'ساري', completed: 'مكتمل', cancelled: 'ملغي' };

const tab = ref<'active' | 'awarded'>('active');

const uniq = (arr: (string | undefined | null)[]) => [...new Set(arr.filter(Boolean))] as string[];
const region = (t: Tender) => { const n = uniq((t.locations ?? []).map((l) => l.region?.name)); return n.length ? n.join('، ') : '—'; };
const city = (t: Tender) => { const n = uniq((t.locations ?? []).map((l) => l.city?.name)); return n.length ? n.join('، ') : '—'; };

const offersOpened = (t: Tender) => {
    if (t.status === 'cancelled') return false;
    const openDate = t.offers_open ?? t.offers_deadline;
    if (!openDate) return false;
    const rawTime = t.offers_open ? t.offers_open_time : t.offers_deadline_time;
    const time = rawTime ? rawTime.slice(0, 5) : (t.offers_open ? '00:00' : '23:59');
    return new Date(`${openDate}T${time}:00`).getTime() <= Date.now();
};
const price = (v: string) => (Number(v) > 0 ? `${Number(v).toLocaleString('en')} ريال` : 'مجانية');
const durationLabels: Record<number, string> = { 3: '3 شهور', 6: '6 شهور', 12: '1 سنة', 24: '2 سنة', 36: '3 سنة', 48: '4 سنة', 60: '5 سنة' };
const duration = (m: number | null) => (m ? (durationLabels[m] ?? `${m} شهر`) : '—');
const remaining = (date: string | null) => {
    if (!date) return '—';
    const d = Math.ceil((new Date(date).getTime() - Date.now()) / 86400000);
    return d < 0 ? 'انتهى' : `${d} يوم متبقي`;
};

const logout = () => router.post('/logout');
const cancelTender = (t: Tender) => {
    if (confirm(`سيتم حذف المنافسة «${t.name}» وجميع بياناتها (العروض والمدفوعات والعقد) نهائيًا ولا يمكن التراجع. هل أنت متأكد؟`)) {
        router.put(`/client/tenders/${t.id}/cancel`, {}, { preserveScroll: true });
    }
};
</script>

<template>
    <Head title="لوحة تحكم العميل" />

    <PublicLayout>
        <section class="tender_details">
            <div class="container">
                <div class="row">
                    <div class="col-12 d-flex align-items-end justify-content-between flex-wrap mb_32 position-relative">
                        <div class="d-flex align-items-center gap-2 flex-column">
                            <h3 class="fs-16 dark-color d-inline-flex flex-column gap-2 mb_16">
                                مرحبا بكم فى منصة اتفاق :
                                <span class="main-color fw-bold fs-32">{{ clientName }}</span>
                            </h3>
                            <div class="d-flex justify-content-start gap-4 w-100">
                                <Link href="/client/profile" class="d-inline-flex align-items-center dark-color gap-2">
                                    <div class="img_box light_main_bc d-flex align-items-center justify-content-center"><img :src="img('profile-edit.png')" alt=""></div>
                                    <span class="dark_color">تحديث بيانات</span>
                                </Link>
                                <a href="#" @click.prevent="logout" class="d-inline-flex align-items-center dark-color gap-2">
                                    <div class="img_box light_third_bc d-flex align-items-center justify-content-center"><img :src="img('profile-logout.png')" alt=""></div>
                                    <span class="dark_color">تسجيل خروج</span>
                                </a>
                            </div>
                        </div>
                        <ul class="d-flex align-items-center gap-4 profile_stats">
                            <li class="text-center p_16 border-radius fs-16 dark-color d-flex flex-column gap-2 main_border"><span class="fw-bold fs-24 main-color">{{ stats.active }}</span>منافسات نشطة</li>
                            <li class="text-center p_16 border-radius fs-16 dark-color d-flex flex-column gap-2 second_border"><span class="fw-bold fs-24 second-color">{{ stats.awarded }}</span>منافسات تم ترسيتها</li>
                            <li class="text-center p_16 border-radius fs-16 dark-color d-flex flex-column gap-2 third_border"><span class="fw-bold fs-24 third-color">{{ stats.offers }}</span>عرض مقدم</li>
                            <li class="text-center p_16 border-radius fs-16 dark-color d-flex flex-column gap-2 dark_border"><span class="fw-bold fs-24 dark-color">{{ stats.contracts }}</span>عقود قائمة</li>
                        </ul>
                    </div>

                    <div class="col-12 d-flex align-items-center justify-content-end gap-4 flex-wrap">
                        <Link href="/client/tenders/create" class="main_btn shadow d-inline-flex align-items-center gap-4 pt_4 pb_4 mb_24">
                            <span class="img_box white_bc main-color d-inline-flex align-items-center justify-content-center fw-bold fs-24">+</span>
                            <span>انشاء منافسة جديدة</span>
                        </Link>
                    </div>

                    <div class="col-12 d-flex align-items-end justify-content-between gap-4 flex-wrap items_search">
                        <ul class="nav nav-tabs partc_tabs w-100" role="tablist">
                            <li class="nav-item" role="presentation">
                                <a class="nav-link" :class="{ active: tab === 'active' }" href="#" @click.prevent="tab = 'active'">المنافسات النشطة</a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link" :class="{ active: tab === 'awarded' }" href="#" @click.prevent="tab = 'awarded'">المنافسات التي تم ترسيتها</a>
                            </li>
                        </ul>
                    </div>

                    <div class="col-12">
                        <div class="tab-content mt_32">
                            <div v-show="tab === 'active'">
                                <p v-if="activeTenders.data.length === 0" class="border_box p_24 white_bc text-center dark-color">لا توجد منافسات نشطة</p>
                                <div v-for="t in activeTenders.data" :key="t.id" class="border_box p_24 white_bc mb_24">
                                    <div class="table-responsive mb_24">
                                        <table class="table table-bordered w-100 mb-0 profile_table">
                                            <thead><tr>
                                                <th class="text-center">رقم المنافسة</th><th class="text-center">الرقم المرجعي</th>
                                                <th class="text-center big-cell">اسم المنافسة</th><th class="text-center">نوع المنافسة</th>
                                                <th class="text-center">المنطقة</th><th class="text-center">المدينة</th>
                                                <th class="text-center">قيمة كراسة الشروط</th><th class="text-center">مدة العقد</th>
                                            </tr></thead>
                                            <tbody><tr>
                                                <td class="text-center dark-color">{{ t.tender_no }}</td>
                                                <td class="text-center dark-color">{{ t.reference_no ?? '—' }}</td>
                                                <td class="text-center main-color big-cell"><Link :href="`/tenders/${t.id}`" class="tender_details__link main-color">{{ t.name }}</Link></td>
                                                <td class="text-center main-color">{{ typeLabels[t.type] }}</td>
                                                <td class="text-center main-color">{{ region(t) }}</td>
                                                <td class="text-center main-color">{{ city(t) }}</td>
                                                <td class="text-center dark-color">{{ price(t.brochure_price) }}</td>
                                                <td class="text-center dark-color">{{ duration(t.contract_duration_months) }}</td>
                                            </tr></tbody>
                                        </table>
                                    </div>
                                    <div class="table-responsive mb_24">
                                        <table class="table table-bordered w-100 mb-0 profile_table">
                                            <thead><tr>
                                                <th class="text-center">تاريخ النشر</th><th class="text-center">آخر موعد لتقديم العروض</th>
                                                <th class="text-center">تاريخ ووقت فتح العروض</th><th class="text-center">الوقت المتبقي</th>
                                                <th class="text-center">حالة المنافسة</th><th class="text-center">العروض المقدمة</th>
                                            </tr></thead>
                                            <tbody><tr>
                                                <td class="text-center dark-color"><span class="d-block">{{ t.published_at ?? '—' }}</span></td>
                                                <td class="text-start main-color"><span class="d-block">{{ t.offers_deadline ?? '—' }}</span><span class="d-block">{{ t.offers_deadline_hijri }}</span><span v-if="t.offers_deadline_time" class="d-block red-color">{{ t.offers_deadline_time?.slice(0,5) }}</span></td>
                                                <td class="text-start main-color"><span class="d-block">{{ t.offers_open ?? '—' }}</span><span class="d-block">{{ t.offers_open_hijri }}</span><span v-if="t.offers_open_time" class="d-block red-color">{{ t.offers_open_time?.slice(0,5) }}</span></td>
                                                <td class="text-center main-color"><Countdown :deadline="t.offers_deadline" :time="t.offers_deadline_time" /></td>
                                                <td class="text-center main-color">{{ statusLabels[t.status] }}</td>
                                                <td class="text-center">
                                                    <div class="d-flex flex-column align-items-center gap-2">
                                                        <span class="third-color">{{ t.offers_count }} عرض مقدم</span>
                                                        <Link v-if="offersOpened(t)" :href="`/client/tenders/${t.id}/offers`" class="main_btn second shadow d-inline-flex align-items-center gap-2 w-124 fs-14 pe_8 pst_8">
                                                            <img :src="img('file.png')" alt="" class="m-0"> فحص العروض
                                                        </Link>
                                                        <span v-else class="fs-14 dark-color text-center">يُتاح فحص العروض بعد موعد ووقت فتح العروض</span>
                                                    </div>
                                                </td>
                                            </tr></tbody>
                                        </table>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between gap-4 table_actions">
                                        <Link :href="`/client/tenders/${t.id}/brochure-requests`" class="main_btn third_light d-inline-flex align-items-center gap-2 fs-14">
                                            <div class="d-inline-flex align-items-center justify-content-center">
                                                <img :src="img('profile-bell.png')" alt="" class="m-0">
                                            </div>
                                            طلبات كراسة الشروط
                                        </Link>
                                        <div class="d-flex align-items-center gap-2 flex-wrap">
                                            <a v-if="t.status !== 'cancelled'" href="#" @click.prevent="cancelTender(t)" class="main_btn red_light d-inline-flex align-items-center gap-2 fs-14">
                                                <img :src="img('profile-delete.png')" alt="" class="m-0"> إلغاء المنافسة
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <SlicePagination :links="activeTenders.links" />
                            </div>

                            <div v-show="tab === 'awarded'">
                                <p v-if="awardedTenders.data.length === 0" class="border_box p_24 white_bc text-center dark-color">لا توجد منافسات تم ترسيتها</p>
                                <div v-for="t in awardedTenders.data" :key="t.id" class="border_box p_24 white_bc mb_24">
                                    <div class="table-responsive mb_24">
                                        <table class="table table-bordered w-100 mb-0 profile_table">
                                            <thead><tr>
                                                <th class="text-center">رقم المنافسة</th><th class="text-center">الرقم المرجعي</th>
                                                <th class="text-center big-cell">اسم المنافسة</th><th class="text-center">نوع المنافسة</th>
                                                <th class="text-center">المنطقة</th><th class="text-center">المدينة</th>
                                                <th class="text-center">قيمة كراسة الشروط</th><th class="text-center">مدة العقد</th>
                                            </tr></thead>
                                            <tbody><tr>
                                                <td class="text-center dark-color">{{ t.tender_no }}</td>
                                                <td class="text-center dark-color">{{ t.reference_no ?? '—' }}</td>
                                                <td class="text-center main-color big-cell"><Link :href="`/tenders/${t.id}`" class="tender_details__link main-color">{{ t.name }}</Link></td>
                                                <td class="text-center main-color">{{ typeLabels[t.type] }}</td>
                                                <td class="text-center main-color">{{ region(t) }}</td>
                                                <td class="text-center main-color">{{ city(t) }}</td>
                                                <td class="text-center dark-color">{{ price(t.brochure_price) }}</td>
                                                <td class="text-center dark-color">{{ duration(t.contract_duration_months) }}</td>
                                            </tr></tbody>
                                        </table>
                                    </div>
                                    <div class="table-responsive mb_24">
                                        <table class="table table-bordered w-100 mb-0 profile_table contract_award_table">
                                            <thead><tr>
                                                <th class="text-center">العروض المقدمة</th><th class="text-center">حالة المنافسة</th>
                                                <th class="text-center">اسم المورد</th><th class="text-center">قيمة العرض المالي</th>
                                                <th class="text-center">حالة العقد</th><th class="text-center">العقد الإلكتروني</th>
                                            </tr></thead>
                                            <tbody><tr>
                                                <td class="text-center"><Link :href="`/client/tenders/${t.id}/offers`" class="contract_award_table__link">مشاهدة العروض</Link></td>
                                                <td class="text-center contract_award_table__status contract_award_table__status--success">تم الترسية</td>
                                                <td class="text-center dark-color">{{ t.awarded_offer?.provider?.company_name ?? '—' }}</td>
                                                <td class="text-center dark-color">{{ t.awarded_offer?.financial_value ? Number(t.awarded_offer.financial_value).toLocaleString('en', {minimumFractionDigits: 2}) : '—' }}</td>
                                                <td class="text-center contract_award_table__status contract_award_table__status--pending">{{ t.contract ? contractStatusLabels[t.contract.status] : '—' }}</td>
                                                <td class="text-center"><span v-if="!t.contract" class="dark-color">—</span><Link v-else :href="`/contract/${t.contract.id}`" class="contract_award_table__link">مشاهدة العقد</Link></td>
                                            </tr></tbody>
                                        </table>
                                    </div>
                                </div>
                                <SlicePagination :links="awardedTenders.links" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>
