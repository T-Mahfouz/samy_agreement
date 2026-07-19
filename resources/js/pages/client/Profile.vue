<script setup lang="ts">
import PublicLayout from '@/layouts/PublicLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps<{ profile: Record<string, any> | null; username: string | null; email: string }>();
const img = (n: string) => `/slice/assets/images/${n}`;

const form = useForm({
    company_name: props.profile?.company_name ?? '',
    mobile: props.profile?.mobile ?? '',
    bank_name: props.profile?.bank_name ?? '',
    bank_beneficiary_name: props.profile?.bank_beneficiary_name ?? '',
    bank_iban: props.profile?.bank_iban ?? '',
    username: props.username ?? '',
    email: props.email ?? '',
    password: '',
    password_confirmation: '',
});

const submit = () => form.put('/client/profile', {
    preserveScroll: true,
    onSuccess: () => { form.password = ''; form.password_confirmation = ''; },
    onError: () => { form.password = ''; form.password_confirmation = ''; },
});
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

                    <div v-if="form.recentlySuccessful" class="col-12 mb_24">
                        <div class="border_box p_16 white_bc main-color fw-bold text-center">تم تحديث البيانات بنجاح ✓</div>
                    </div>

                    <div class="col-12">
                        <form @submit.prevent="submit" novalidate>
                            <div class="border_box p_24 white_bc mb_32">
                                <h3 class="fs-16 dark-color d-flex align-items-center gap-4 mb_24">
                                    <div class="img_box border-bc d-flex align-items-center justify-content-center">
                                        <img :src="img('details-file.png')" alt="" class="m-0">
                                    </div>
                                    البيانات الأساسية للمستفيد
                                </h3>
                                <div class="row">
                                    <div class="col-12 col-sm-6 col-md-6 col-lg-2">
                                        <div class="form-group">
                                            <label for="facility_name">أسم المنشأة</label>
                                            <input type="text" class="form-control" id="facility_name" v-model="form.company_name" placeholder="أسم المنشأة">
                                            <small v-if="form.errors.company_name" class="red-color">{{ form.errors.company_name }}</small>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-6 col-md-6 col-lg-2">
                                        <div class="form-group">
                                            <label for="mobile">رقم الجوال <span class="red-color">*</span></label>
                                            <input type="tel" inputmode="numeric" class="form-control" id="mobile" v-model="form.mobile" placeholder="05xxxxxxxx" dir="ltr">
                                            <small v-if="form.errors.mobile" class="red-color d-block">{{ form.errors.mobile }}</small>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-6 col-md-6 col-lg-2">
                                        <div class="form-group">
                                            <label for="bank_name">اسم البنك</label>
                                            <input type="text" class="form-control" id="bank_name" v-model="form.bank_name" placeholder="اسم البنك">
                                            <small v-if="form.errors.bank_name" class="red-color d-block">{{ form.errors.bank_name }}</small>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-6 col-md-6 col-lg-2">
                                        <div class="form-group">
                                            <label for="bank_beneficiary_name">اسم المستفيد</label>
                                            <input type="text" class="form-control" id="bank_beneficiary_name" v-model="form.bank_beneficiary_name" placeholder="اسم المستفيد">
                                            <small v-if="form.errors.bank_beneficiary_name" class="red-color d-block">{{ form.errors.bank_beneficiary_name }}</small>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-6 col-md-8 col-lg-2">
                                        <div class="form-group">
                                            <label for="bank_iban">رقم الآيبان</label>
                                            <input type="text" class="form-control" id="bank_iban" v-model="form.bank_iban" placeholder="SAxx xxxx xxxx xxxx xxxx xxxx" dir="ltr">
                                            <small v-if="form.errors.bank_iban" class="red-color d-block">{{ form.errors.bank_iban }}</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="border_box p_24 white_bc mb_32">
                                <h3 class="fs-16 dark-color d-flex align-items-center gap-4 mb_24">
                                    <div class="img_box border-bc d-flex align-items-center justify-content-center">
                                        <img :src="img('details-edit.png')" alt="" class="m-0">
                                    </div>
                                    بيانات الدخول
                                </h3>
                                <div class="login">
                                    <div class="row">
                                        <div class="col-12 col-lg-3 col-md-6 col-sm-6">
                                            <div class="form-group">
                                                <label for="username">اسم المستخدم</label>
                                                <img :src="img('login-user.png')" alt="">
                                                <input type="text" class="form-control" id="username" v-model="form.username" placeholder="اسم المستخدم">
                                                <small v-if="form.errors.username" class="red-color">{{ form.errors.username }}</small>
                                            </div>
                                        </div>
                                        <div class="col-12 col-lg-3 col-md-6 col-sm-6">
                                            <div class="form-group">
                                                <label for="email">البريد الإلكتروني</label>
                                                <img :src="img('mail.png')" alt="">
                                                <input type="email" class="form-control" id="email" v-model="form.email" placeholder="البريد الإلكتروني" dir="ltr">
                                                <small v-if="form.errors.email" class="red-color">{{ form.errors.email }}</small>
                                            </div>
                                        </div>
                                        <div class="col-12 col-lg-3 col-md-6 col-sm-6">
                                            <div class="form-group">
                                                <label for="password">كلمة المرور</label>
                                                <img :src="img('login-lock.png')" alt="">
                                                <input type="password" class="form-control" id="password" v-model="form.password" placeholder="اتركها فارغة إن لم ترغب في تغييرها">
                                                <small v-if="form.errors.password" class="red-color">{{ form.errors.password }}</small>
                                            </div>
                                        </div>
                                        <div class="col-12 col-lg-3 col-md-6 col-sm-6">
                                            <div class="form-group">
                                                <label for="password_confirm">تأكيد كلمة المرور</label>
                                                <img :src="img('login-lock.png')" alt="">
                                                <input type="password" class="form-control" id="password_confirm" v-model="form.password_confirmation" placeholder="تأكيد كلمة المرور">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end mt_24">
                                <button type="submit" :disabled="form.processing" class="main_btn m-0 shadow pe_32 ps_32">{{ form.processing ? 'جاري الحفظ...' : 'حفظ التعديلات' }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>
