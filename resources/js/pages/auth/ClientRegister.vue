<script setup lang="ts">
import PublicLayout from '@/layouts/PublicLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const img = (n: string) => `/slice/assets/images/${n}`;

const form = useForm({
    role: 'client',
    facility_name: '',
    mobile: '',
    bank_name: '',
    beneficiary_name: '',
    iban: '',
    username: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => form.post('/register', { onError: () => { form.password = ''; form.password_confirmation = ''; } });
</script>

<template>
    <Head title="إنشاء حساب طالب خدمة" />

    <PublicLayout>
        <section class="tender_details">
            <div class="container">
                <div class="row">
                    <div class="col-12 d-flex align-items-center justify-content-between flex-wrap mb_32 position-relative">
                        <h3 class="fs-32 dark-color fw-bold d-inline-flex align-items-center gap-4 resize_32">
                            <div class="img_box main_bc d-flex align-items-center justify-content-center">
                                <img :src="img('user.png')" alt="" class="m-0">
                            </div>
                            إنشاء حساب طالب خدمة
                            <span class="main-color"> ( مستفيد )</span>
                        </h3>
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
                                            <input type="text" class="form-control" id="facility_name" v-model="form.facility_name" placeholder="أسم المنشأة">
                                            <small v-if="form.errors.facility_name" class="red-color">{{ form.errors.facility_name }}</small>
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
                                            <label for="mobile">اسم البنك</label>
                                            <input type="text" class="form-control" id="bank_name" v-model="form.bank_name" placeholder="اسم البنك">
                                            <small v-if="form.errors.bank_name" class="red-color d-block">{{ form.errors.bank_name }}</small>
                                        </div>
                                    </div>

                                    <div class="col-12 col-sm-6 col-md-6 col-lg-2">
                                        <div class="form-group">
                                            <label for="mobile">اسم المستفيد</label>
                                            <input type="text" class="form-control" id="beneficiary_name" v-model="form.beneficiary_name" placeholder="اسم المستفيد">
                                            <small v-if="form.errors.beneficiary_name" class="red-color d-block">{{ form.errors.beneficiary_name }}</small>
                                        </div>
                                    </div>

                                    <div class="col-12 col-sm-6 col-md-8 col-lg-2">
                                        <div class="form-group">
                                            <label for="mobile">رقم الآيبان</label>
                                            <input type="text" class="form-control" id="iban" v-model="form.iban" placeholder="SAxx xxxx xxxx xxxx xxxx xxxx" dir="ltr">
                                            <small v-if="form.errors.iban" class="red-color d-block">{{ form.errors.iban }}</small>
                                        </div>
                                    </div>

                                    <!-- <div class="col-12 col-lg-8">
                                        <div class="form-group">
                                            <label for="bank_name"> بيانات الحساب البنكى</label>
                                            <div class="d-flex gap-2 align-items-center inputs_group">
                                            </div>
                                        </div>
                                    </div> -->
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
                                                <input type="email" class="form-control" id="email" v-model="form.email" placeholder="البريد الإلكتروني">
                                                <small v-if="form.errors.email" class="red-color">{{ form.errors.email }}</small>
                                            </div>
                                        </div>
                                        <div class="col-12 col-lg-3 col-md-6 col-sm-6">
                                            <div class="form-group">
                                                <label for="password">كلمة المرور</label>
                                                <img :src="img('login-lock.png')" alt="">
                                                <input type="password" class="form-control" id="password" v-model="form.password" placeholder="كلمة المرور">
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
                                <button type="submit" :disabled="form.processing" class="main_btn m-0 shadow d-flex align-items-center justify-content-center pst_64 pe_64">{{ form.processing ? 'جاري إنشاء الحساب...' : 'تسجيل' }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>
