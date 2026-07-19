<script setup lang="ts">
import PublicLayout from '@/layouts/PublicLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

interface Offer {
    id: number; provider: string | null; technical_file: string | null; financial_file: string | null;
    financial_value: string | null; technical_check: string; is_awarded: boolean;
}
const props = defineProps<{
    tender: { id: number; tender_no: string; name: string; status: string };
    offers: Offer[];
}>();

const img = (n: string) => `/slice/assets/images/${n}`;
const offerFile = (id: number, type: 'technical' | 'financial') => `/offers/${id}/files/${type}`;
const offerPreview = (id: number, type: 'technical' | 'financial') => `/offers/${id}/files/${type}?inline=1`;
const num = (v: string | null) => (v ? Number(v).toLocaleString('en', { minimumFractionDigits: 2 }) : '—');

const initialChecks: Record<number, string> = {};
props.offers.forEach((o) => { initialChecks[o.id] = o.technical_check === 'pending' ? '' : o.technical_check; });
const awarded = props.offers.find((o) => o.is_awarded);

const form = useForm<{ checks: Record<number, string>; award_offer_id: number | null }>({
    checks: initialChecks,
    award_offer_id: awarded?.id ?? null,
});

const onCheck = (id: number, val: string) => {
    form.checks[id] = val;
    if (val === 'not_matching' && form.award_offer_id === id) form.award_offer_id = null;
};

const submit = () => form
    .transform((d) => ({ ...d, checks: Object.fromEntries(Object.entries(d.checks).filter(([, v]) => v !== '')) }))
    .put(`/client/tenders/${props.tender.id}/offers`, { preserveScroll: true });
</script>

<template>
    <Head title="فحص العروض المقدمة" />

    <PublicLayout>
        <section class="tender_details">
            <div class="container">
                <div class="row">
                    <div class="col-12 d-flex align-items-center justify-content-between flex-wrap mb_32 position-relative">
                        <h3 class="dark-color d-inline-flex flex-column gap-2 mb_16 fs-24 fw-bold">
                            فحص العروض المقدمة
                            <span class="main-color fs-16 fw-normal">{{ tender.name }}</span>
                        </h3>
                        <Link href="/client/dashboard" class="main_btn dark_light d-inline-flex align-items-center gap-2">
                            <span class="img_box white_bc main-color d-inline-flex align-items-center justify-content-center fw-bold fs-24"><img :src="img('details-arrow.png')" alt=""></span>
                            رجوع الى منافساتي
                        </Link>
                    </div>

                    <div class="col-12">
                        <form @submit.prevent="submit">
                            <div class="border_box white_bc p_24">
                                <div class="table-responsive mb_24">
                                    <table class="custom_table custom_border text-center mb-0 w-100 align-middle">
                                        <thead>
                                            <tr>
                                                <th class="main-color big-cell">اسم المورد</th>
                                                <th class="main-color">ملف العرض الفني</th>
                                                <th class="main-color big-cell">حالة الفحص</th>
                                                <th class="main-color">ملف العرض المالي</th>
                                                <th class="main-color">قيمة العرض المالي</th>
                                                <th class="main-color">ترسية العرض</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="o in offers" :key="o.id">
                                                <td class="dark-color big-cell">{{ o.provider }}</td>
                                                <td>
                                                    <template v-if="o.technical_file">
                                                        <a :href="offerPreview(o.id, 'technical')" target="_blank" rel="noopener" class="second-color me_8"><u>معاينة</u></a>
                                                        <a :href="offerFile(o.id, 'technical')" class="second-color"><u>تحميل</u></a>
                                                    </template>
                                                    <span v-else class="dark-color">—</span>
                                                </td>
                                                <td class="dark-color big-cell">
                                                    <div class="d-inline-flex align-items-start gap-2 text-right">
                                                        <label class="offer_exam__choice d-inline-flex align-items-center gap-2 mb-0">
                                                            <input type="radio" :name="`offer_exam_${o.id}`" value="matching" :checked="form.checks[o.id] === 'matching'" @change="onCheck(o.id, 'matching')">
                                                            <span>مطابق</span>
                                                        </label>
                                                        <label class="offer_exam__choice d-inline-flex align-items-center gap-2 mb-0">
                                                            <input type="radio" :name="`offer_exam_${o.id}`" value="not_matching" :checked="form.checks[o.id] === 'not_matching'" @change="onCheck(o.id, 'not_matching')">
                                                            <span class="red-color fw-bold">غير مطابق</span>
                                                        </label>
                                                    </div>
                                                </td>
                                                <td>
                                                    <template v-if="form.checks[o.id] === 'matching' && o.financial_file">
                                                        <a :href="offerPreview(o.id, 'financial')" target="_blank" rel="noopener" class="second-color me_8"><u>معاينة</u></a>
                                                        <a :href="offerFile(o.id, 'financial')" class="second-color"><u>تحميل</u></a>
                                                    </template>
                                                    <span v-else class="dark-color">—</span>
                                                </td>
                                                <td class="dark-color ltr">
                                                    <span v-if="form.checks[o.id] === 'matching'">{{ num(o.financial_value) }}</span>
                                                    <span v-else class="dark-color">—</span>
                                                </td>
                                                <td class="offer_award__cell">
                                                    <label v-if="form.checks[o.id] === 'matching'" class="offer_award__choice d-inline-flex align-items-center justify-content-center gap-2 mb-0">
                                                        <input type="radio" name="offer_award" :value="o.id" v-model="form.award_offer_id">
                                                        <span class="dark-color">ترسية</span>
                                                    </label>
                                                    <span v-else class="dark-color">—</span>
                                                </td>
                                            </tr>
                                            <tr v-if="offers.length === 0">
                                                <td colspan="6" class="dark-color p_24">لا توجد عروض مقدمة على هذه المنافسة</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="d-flex justify-content-end">
                                    <button type="submit" :disabled="form.processing || offers.length === 0" class="main_btn m-0 shadow d-inline-flex align-items-center justify-content-center pe_32 ps_32">
                                        حفظ الترسية وإرسال العقد
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>
