<script setup lang="ts">
import SlicePagination from '@/components/SlicePagination.vue';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

interface Req {
    id: number; tender_id: number; tender_no: string | null; reference_no: string | null;
    tender_name: string | null; client: string | null; amount: string; receipt_file: string | null;
    status: string; has_file: boolean;
}
interface Paged<T> { data: T[]; links: { url: string | null; label: string; active: boolean }[]; total: number }
defineProps<{ requests: Paged<Req> }>();

const img = (n: string) => `/slice/assets/images/${n}`;
const statusLabels: Record<string, string> = { pending: 'قيد المراجعة', paid: 'تم الاعتماد', rejected: 'مرفوض' };
</script>

<template>
    <Head title="طلباتي تحميل كراسة الشروط" />

    <PublicLayout>
        <section class="tender_details">
            <div class="container">
                <div class="row">
                    <div class="col-12 d-flex align-items-center justify-content-between flex-wrap mb_32 position-relative">
                        <h3 class="dark-color d-inline-flex flex-column gap-2 mb_16 fs-24 fw-bold">طلباتي تحميل كراسة الشروط</h3>
                        <Link href="/provider/dashboard" class="main_btn dark_light d-inline-flex align-items-center gap-2">
                            <span class="img_box white_bc main-color d-inline-flex align-items-center justify-content-center fw-bold fs-24"><img :src="img('details-arrow.png')" alt=""></span>
                            رجوع للوحة التحكم
                        </Link>
                    </div>

                    <div class="col-12">
                        <div class="border_box white_bc p_24">
                            <div class="table-responsive mb_24">
                                <table class="custom_table custom_border text-center mb-0 w-100 align-middle">
                                    <thead><tr>
                                        <th class="main-color">رقم المنافسة</th>
                                        <th class="main-color">الرقم المرجعي</th>
                                        <th class="main-color big-cell">اسم المنافسة</th>
                                        <th class="main-color">اسم المستفيد</th>
                                        <th class="main-color">قيمة كراسة الشروط</th>
                                        <th class="main-color">ايصال التحويل</th>
                                        <th class="main-color">ملف كراسة الشروط</th>
                                    </tr></thead>
                                    <tbody>
                                        <tr v-for="r in requests.data" :key="r.id">
                                            <td class="dark-color">{{ r.tender_no }}</td>
                                            <td class="dark-color">{{ r.reference_no ?? '—' }}</td>
                                            <td class="main-color big-cell"><a :href="`/tenders/${r.tender_id}`" class="tender_details__link main-color">{{ r.tender_name }}</a></td>
                                            <td class="dark-color">{{ r.client ?? '—' }}</td>
                                            <td class="dark-color ltr">{{ Number(r.amount).toLocaleString('en', { minimumFractionDigits: 2 }) }}</td>
                                            <td><a v-if="r.receipt_file" :href="`/payments/${r.id}/receipt`" target="_blank" class="second-color"><u>مشاهدة الإيصال</u></a><span v-else class="dark-color">—</span></td>
                                            <td>
                                                <div v-if="r.status === 'paid' && r.has_file" class="d-flex align-items-center justify-content-center gap-2 flex-wrap">
                                                    <a :href="`/provider/tenders/${r.tender_id}/brochure/download?inline=1`" target="_blank" rel="noopener" class="second-color fs-14"><u>معاينة</u></a>
                                                    <a :href="`/provider/tenders/${r.tender_id}/brochure/download`" class="main_btn second fs-14 pe_16 pst_16" role="button">تحميل كراسة الشروط</a>
                                                </div>
                                                <span v-else-if="r.status === 'paid' && !r.has_file" class="fs-14 dark-color">تم الاعتماد — كراسة الشروط غير مرفوعة بعد</span>
                                                <span v-else class="fs-14" :class="r.status === 'rejected' ? 'red-color' : 'dark-color'">{{ statusLabels[r.status] }}</span>
                                            </td>
                                        </tr>
                                        <tr v-if="requests.data.length === 0"><td colspan="7" class="dark-color p_24">لا توجد طلبات</td></tr>
                                    </tbody>
                                </table>
                            </div>
                            <SlicePagination :links="requests.links" />
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>
