<script setup lang="ts">
import PublicLayout from '@/layouts/PublicLayout.vue';
import { Head, router } from '@inertiajs/vue3';

interface Contract {
    id: number; tender_no: string | null; reference_no: string | null; tender_name: string | null;
    client_name: string | null; provider_name: string | null; contract_value: string | null;
    contract_duration_months: number | null; documentation_date: string | null; status: string;
    client_signed_at: string | null; client_signed_ip: string | null;
    provider_signed_at: string | null; provider_signed_ip: string | null;
}
const props = defineProps<{ contract: Contract; side: string | null; canSign: boolean }>();

const img = (n: string) => `/slice/assets/images/${n}`;
const fmt = (s: string | null) => (s ? new Date(s).toLocaleString('ar-EG', { dateStyle: 'medium', timeStyle: 'short' }) : '');
const sign = () => { if (confirm('بالموافقة أنت توقّع هذا العقد إلكترونيًا. متابعة؟')) router.post(`/contract/${props.contract.id}/sign`, {}, { preserveScroll: true }); };
</script>

<template>
    <Head title="عقد إتفاق ألكترونى" />

    <PublicLayout>
        <section>
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-10">
                        <div class="contract_item border_box white_bc p_24">
                            <h3 class="fw-bold fs-32 dark-color text-center mb_32">عقد إتفاق ألكترونى</h3>

                            <div class="contract_intro dark-color mb_48" dir="rtl" style="line-height: 2.1; text-align: justify;">
                                <p class="mb_16">صدر هذا العقد الإلكتروني استنادًا إلى المنافسة المطروحة من المستفيد ({{ contract.client_name ?? '—' }}) والعرض المقدم من المورد ({{ contract.provider_name ?? '—' }}) الذي تمت الترسية عليه، ويُعد توثيقًا للاتفاق المبرم بين المستفيد والمورد.</p>
                                <p class="mb_16">وتُعد وثائق المنافسة والعرض المقبول وقرار الترسية جزءًا لا يتجزأ من هذا العقد ومكملة لأحكامه.</p>
                                <p class="mb_0">ويقر الطرفان بأن اعتماد هذا العقد إلكترونيًا يُعد موافقة نهائية وملزمة على جميع أحكامه، وتترتب عليه الآثار النظامية المقررة للعقود الإلكترونية وفق الأنظمة المعمول بها في المملكة العربية السعودية.</p>
                            </div>

                            <form @submit.prevent>
                                <div class="row">
                                    <div class="col-md-6"><div class="form-group"><label class="dark-color">إسم المستفيد</label><input class="form-control border-0" type="text" readonly :value="contract.client_name ?? ''"></div></div>
                                    <div class="col-md-6"><div class="form-group"><label class="dark-color">إسم المورد</label><input class="form-control border-0" type="text" readonly :value="contract.provider_name ?? ''"></div></div>
                                    <div class="col-md-6"><div class="form-group"><label class="dark-color">رقم المنافسه</label><input class="form-control border-0" type="text" readonly :value="contract.tender_no ?? ''"></div></div>
                                    <div class="col-md-6"><div class="form-group"><label class="dark-color">الرقم المرجعى</label><input class="form-control border-0" type="text" readonly :value="contract.reference_no ?? ''"></div></div>
                                    <div class="col-12"><div class="form-group"><label class="dark-color">إسم المنافسه</label><input class="form-control border-0" type="text" readonly :value="contract.tender_name ?? ''"></div></div>
                                    <div class="col-md-6"><div class="form-group"><label class="dark-color">مدة العقد</label><input class="form-control border-0" type="text" readonly :value="contract.contract_duration_months ? contract.contract_duration_months + ' شهر' : '—'"></div></div>
                                    <div class="col-md-6"><div class="form-group"><label class="dark-color">قيمة العرض المالى</label><input class="form-control border-0" type="text" readonly :value="contract.contract_value ? Number(contract.contract_value).toLocaleString('en') + ' ريال سعودى' : '—'"></div></div>
                                    <div class="col-md-6"><div class="form-group"><label class="dark-color">تاريخ التوثيق</label><input class="form-control border-0" type="text" readonly :value="contract.documentation_date ?? '—'"></div></div>

                                    <div v-if="canSign" class="col-12 text-center mt_32 d-flex justify-content-center">
                                        <button type="button" class="main_btn" @click="sign">أوافق على العقد</button>
                                    </div>
                                    <div v-else-if="side && (side === 'client' ? contract.client_signed_at : contract.provider_signed_at)" class="col-12 text-center mt_32">
                                        <p class="main-color fw-bold">لقد وقّعت على هذا العقد.</p>
                                    </div>
                                </div>
                            </form>

                            <div class="row mt_64">
                                <div class="col-md-6">
                                    <div class="sign_item" v-if="contract.client_signed_at">
                                        <p class="fs-16 fw-bold mb_24 d-flex align-items-center gap-2"><img :src="img('check.png')" class="m-0">تم التوقيع إلكترونيا</p>
                                        <ul class="d-flex flex-column gap-2">
                                            <li class="fs-14 fw-bold"><span>تاريخ : </span>{{ fmt(contract.client_signed_at) }}</li>
                                            <li class="fs-14 fw-bold"><span>الاسم : </span>{{ contract.client_name }}</li>
                                            <li class="fs-14 fw-bold"><span>IP : </span>{{ contract.client_signed_ip }}</li>
                                        </ul>
                                    </div>
                                    <div v-else class="sign_item"><p class="fs-16 dark-gray-color">المستفيد لم يوقّع بعد</p></div>
                                </div>
                                <div class="col-md-6">
                                    <div class="sign_item" v-if="contract.provider_signed_at">
                                        <p class="fs-16 fw-bold mb_24 d-flex align-items-center gap-2"><img :src="img('check.png')" class="m-0">تم التوقيع إلكترونيا</p>
                                        <ul class="d-flex flex-column gap-2">
                                            <li class="fs-14 fw-bold"><span>تاريخ : </span>{{ fmt(contract.provider_signed_at) }}</li>
                                            <li class="fs-14 fw-bold"><span>الاسم : </span>{{ contract.provider_name }}</li>
                                            <li class="fs-14 fw-bold"><span>IP : </span>{{ contract.provider_signed_ip }}</li>
                                        </ul>
                                    </div>
                                    <div v-else class="sign_item"><p class="fs-16 dark-gray-color">المورد لم يوقّع بعد</p></div>
                                </div>
                                <div v-if="contract.status === 'active'" class="col-12 text-center mt_64">
                                    <p class="fs-16 fw-bold dark-gray-color">هذا العقد مسجل إلكترونيا</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>
