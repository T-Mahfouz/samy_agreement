<script setup lang="ts">
import SlicePagination from '@/components/SlicePagination.vue';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

interface Req { id: number; provider: string | null; amount: string; receipt_file: string | null; status: string }
interface Paged<T> { data: T[]; links: { url: string | null; label: string; active: boolean }[]; total: number }
const props = defineProps<{ tender: { id: number; tender_no: string; name: string }; requests: Paged<Req> }>();

const img = (n: string) => `/slice/assets/images/${n}`;

const decisions: Record<number, string> = {};
props.requests.data.forEach((r) => { decisions[r.id] = r.status; });
const form = useForm<{ decisions: Record<number, string> }>({ decisions });

const submit = () => form.put(`/client/tenders/${props.tender.id}/brochure-requests`, { preserveScroll: true });
</script>

<template>
    <Head title="طلبات كراسة الشروط" />

    <PublicLayout>
        <section class="tender_details">
            <div class="container">
                <div class="row">
                    <div class="col-12 d-flex align-items-center justify-content-between flex-wrap mb_32 position-relative">
                        <h3 class="dark-color d-inline-flex flex-column gap-2 mb_16 fs-24 fw-bold">
                            طلبات تحميل كراسة الشروط
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
                                        <thead><tr>
                                            <th class="main-color big-xl-cell">اسم المورد</th>
                                            <th class="main-color">قيمة كراسة الشروط</th>
                                            <th class="main-color">ايصال التحويل</th>
                                            <th class="main-color big-xl-cell">حالة الطلب</th>
                                        </tr></thead>
                                        <tbody>
                                            <tr v-for="r in requests.data" :key="r.id">
                                                <td class="dark-color big-xl-cell">{{ r.provider }}</td>
                                                <td class="dark-color ltr">{{ Number(r.amount).toLocaleString('en', { minimumFractionDigits: 2 }) }}</td>
                                                <td><a v-if="r.receipt_file" :href="`/payments/${r.id}/receipt`" target="_blank" class="second-color"><u>مشاهدة الإيصال</u></a><span v-else class="dark-color">—</span></td>
                                                <td class="dark-color big-xl-cell">
                                                    <div class="d-inline-flex align-items-center gap-2">
                                                        <label class="offer_exam__choice d-inline-flex align-items-center gap-2 mb-0">
                                                            <input type="radio" :name="`booklet_req_${r.id}`" value="paid" v-model="form.decisions[r.id]">
                                                            <span>موافقة</span>
                                                        </label>
                                                        <label class="offer_exam__choice d-inline-flex align-items-center gap-2 mb-0">
                                                            <input type="radio" :name="`booklet_req_${r.id}`" value="rejected" v-model="form.decisions[r.id]">
                                                            <span>رفض</span>
                                                        </label>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr v-if="requests.data.length === 0"><td colspan="4" class="dark-color p_24">لا توجد طلبات</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                                <SlicePagination :links="requests.links" />
                                <div class="d-flex justify-content-end">
                                    <button type="submit" :disabled="form.processing || requests.data.length === 0" class="main_btn m-0 shadow pe_32 ps_32">حفظ القرارات</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>
