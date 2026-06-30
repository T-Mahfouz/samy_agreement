<script setup lang="ts">
import PublicLayout from '@/layouts/PublicLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps<{ profile: Record<string, any> | null; email: string }>();
const img = (n: string) => `/slice/assets/images/${n}`;

const form = useForm({
    company_name: props.profile?.company_name ?? '',
    mobile: props.profile?.mobile ?? '',
    bank_name: props.profile?.bank_name ?? '',
    bank_beneficiary_name: props.profile?.bank_beneficiary_name ?? '',
    bank_iban: props.profile?.bank_iban ?? '',
});
const submit = () => form.put('/client/profile', { preserveScroll: true });
</script>

<template>
    <Head title="تحديث البيانات" />
    <PublicLayout>
        <section class="tender_details">
            <div class="container">
                <div class="row">
                    <div class="col-12 d-flex align-items-center justify-content-between flex-wrap mb_32">
                        <h3 class="dark-color fw-bold fs-24 d-inline-flex align-items-center gap-4">
                            <div class="img_box main_bc d-flex align-items-center justify-content-center"><img :src="img('profile-edit.png')" alt="" class="m-0"></div>
                            تحديث بيانات المستفيد
                        </h3>
                        <Link href="/client/dashboard" class="main_btn dark_light d-inline-flex align-items-center gap-2">
                            <span class="img_box white_bc main-color d-inline-flex align-items-center justify-content-center fw-bold fs-24"><img :src="img('details-arrow.png')" alt=""></span>
                            رجوع الى منافساتي
                        </Link>
                    </div>
                    <div class="col-12">
                        <form @submit.prevent="submit">
                            <div class="border_box p_24 white_bc mb_32">
                                <div class="row">
                                    <div class="col-12 col-lg-4"><div class="form-group"><label>اسم المنشأة</label><input type="text" class="form-control" v-model="form.company_name"><small v-if="form.errors.company_name" class="red-color">{{ form.errors.company_name }}</small></div></div>
                                    <div class="col-12 col-lg-4"><div class="form-group"><label>رقم الجوال</label><input type="tel" class="form-control" v-model="form.mobile" dir="rtl"></div></div>
                                    <div class="col-12 col-lg-4"><div class="form-group"><label>البريد الإلكتروني</label><input type="email" class="form-control" :value="email" disabled dir="ltr"></div></div>
                                    <div class="col-12 col-lg-4"><div class="form-group"><label>اسم البنك</label><input type="text" class="form-control" v-model="form.bank_name"></div></div>
                                    <div class="col-12 col-lg-4"><div class="form-group"><label>اسم المستفيد</label><input type="text" class="form-control" v-model="form.bank_beneficiary_name"></div></div>
                                    <div class="col-12 col-lg-4"><div class="form-group"><label>رقم الآيبان</label><input type="text" class="form-control" v-model="form.bank_iban" dir="ltr"></div></div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end">
                                <button type="submit" :disabled="form.processing" class="main_btn m-0 shadow pe_32 ps_32">حفظ التعديلات</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>
