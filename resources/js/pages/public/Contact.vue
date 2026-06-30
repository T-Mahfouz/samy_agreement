<script setup lang="ts">
import PublicLayout from '@/layouts/PublicLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

defineProps<{ info: { phone: string; whatsapp: string; email: string; support_email: string } }>();
const img = (n: string) => `/slice/assets/images/${n}`;

// تحويل رقم محلي (يبدأ بـ 0) إلى رابط واتساب دولي (966)
const waLink = (n: string) => {
    const d = (n || '').replace(/\D/g, '');
    const intl = d.startsWith('966') ? d : d.replace(/^0/, '966');
    return `https://wa.me/${intl}`;
};

const form = useForm({ full_name: '', mobile: '', email: '', message: '' });
const submit = () => form.post('/contact', { preserveScroll: true, onSuccess: () => form.reset() });
</script>

<template>
    <Head title="تواصل معنا" />

    <PublicLayout>
        <section class="tender_details">
            <div class="container">
                <div class="row">
                    <div class="col-12 d-flex align-items-center justify-content-between flex-wrap mb_32 position-relative">
                        <h3 class="fs-32 main-color fw-bold d-inline-flex align-items-center gap-4">
                            <div class="img_box main_bc d-flex align-items-center justify-content-center"><img :src="img('contact-support.png')" alt="" class="m-0"></div>
                            تواصل مع إدارة منصة اتفاق
                        </h3>
                    </div>
                    <div class="col-12">
                        <div class="border_box white_bc contact_wrap">
                            <div class="row m-0">
                                <!-- Contact info -->
                                <div class="col-12 col-lg-4 border_end p_24">
                                    <h3 class="fs-16 main-color d-inline-flex align-items-center gap-4 mb_24">
                                        <div class="img_box dark_gray_bc d-flex align-items-center justify-content-center"><img :src="img('contact-file.png')" alt="" class="m-0"></div>
                                        بيانات التواصل مع الإدارة
                                    </h3>
                                    <ul class="list-unstyled p-0 m-0 d-flex flex-column gap-4 mb_32 contact_list">
                                        <li class="d-flex align-items-center gap-4 flex-wrap border-0">
                                            <div class="img_box main_border d-flex align-items-center justify-content-center flex-shrink-0"><img :src="img('contact-phone.png')" alt="" class="m-0"></div>
                                            <div class="flex-grow-1"><span class="d-block dark-color fs-14 mb_4">رقم الجوال</span><a v-if="info.phone" :href="`tel:${info.phone}`" class="d-block dark-color fw-bold fs-18" dir="ltr">{{ info.phone }}</a><span v-else class="d-block dark-color fw-bold fs-18">—</span></div>
                                        </li>
                                        <li class="d-flex align-items-center gap-4 flex-wrap border-0">
                                            <div class="img_box second_border d-flex align-items-center justify-content-center flex-shrink-0"><img :src="img('contact-whatsapp.png')" alt="" class="m-0"></div>
                                            <div class="flex-grow-1"><span class="d-block dark-color fs-14 mb_4">رقم الواتس اب</span><a v-if="info.whatsapp" :href="waLink(info.whatsapp)" target="_blank" rel="noopener" class="d-block dark-color fw-bold fs-18" dir="ltr">{{ info.whatsapp }}</a><span v-else class="d-block dark-color fw-bold fs-18">—</span></div>
                                        </li>
                                        <li class="d-flex align-items-center gap-4 flex-wrap border-0">
                                            <div class="img_box third_border d-flex align-items-center justify-content-center flex-shrink-0"><img :src="img('contact-mail.png')" alt="" class="m-0"></div>
                                            <div class="flex-grow-1"><span class="d-block dark-color fs-14 mb_4">البريد الإلكتروني</span><a v-if="info.email" :href="`mailto:${info.email}`" class="d-block dark-color fw-bold fs-18" dir="ltr">{{ info.email }}</a><span v-else class="d-block dark-color fw-bold fs-18">—</span></div>
                                        </li>
                                        <li class="d-flex align-items-center gap-4 flex-wrap border-0">
                                            <div class="img_box third_border d-flex align-items-center justify-content-center flex-shrink-0"><img :src="img('contact-support.png')" alt="" class="m-0"></div>
                                            <div class="flex-grow-1"><span class="d-block dark-color fs-14 mb_4">الدعم الفني</span><a v-if="info.support_email" :href="`mailto:${info.support_email}`" class="d-block dark-color fw-bold fs-18" dir="ltr">{{ info.support_email }}</a><span v-else class="d-block dark-color fw-bold fs-18">—</span></div>
                                        </li>
                                    </ul>
                                    <ul class="social d-flex align-items-center gap-2 justify-content-center flex-wrap border-0">
                                        <li class="border-0"><a href="#" aria-label="YouTube"><img :src="img('youtube.png')" alt=""></a></li>
                                        <li class="border-0"><a href="#" aria-label="LinkedIn"><img :src="img('linkedin.png')" alt=""></a></li>
                                        <li class="border-0"><a href="#" aria-label="Instagram"><img :src="img('instagram.png')" alt=""></a></li>
                                        <li class="border-0"><a href="#" aria-label="X"><img :src="img('x.png')" alt=""></a></li>
                                    </ul>
                                </div>
                                <!-- Contact form -->
                                <div class="col-12 col-lg-8 p_24">
                                    <h3 class="fs-16 main-color d-inline-flex align-items-center gap-4 mb_24">
                                        <div class="img_box dark_gray_bc d-flex align-items-center justify-content-center"><img :src="img('contact-mail-white.png')" alt="" class="m-0"></div>
                                        استمارة التواصل الإلكتروني
                                    </h3>
                                    <form @submit.prevent="submit" novalidate>
                                        <div class="row">
                                            <div class="col-12 col-md-4"><div class="form-group"><label>الإسم بالكامل</label><input type="text" class="form-control" v-model="form.full_name"><small v-if="form.errors.full_name" class="red-color">{{ form.errors.full_name }}</small></div></div>
                                            <div class="col-12 col-md-4"><div class="form-group"><label>رقم الجوال</label><input type="tel" class="form-control" v-model="form.mobile" dir="rtl"></div></div>
                                            <div class="col-12 col-md-4"><div class="form-group"><label>البريد الإلكتروني</label><input type="email" class="form-control" v-model="form.email" dir="ltr"></div></div>
                                            <div class="col-12"><div class="form-group"><label>تفاصيل الرسالة</label><textarea class="form-control" v-model="form.message" rows="6"></textarea><small v-if="form.errors.message" class="red-color">{{ form.errors.message }}</small></div></div>
                                        </div>
                                        <div class="text-center mt_8">
                                            <button type="submit" :disabled="form.processing" class="main_btn light m-0 d-inline-flex align-items-center justify-content-center pe_48 pst_48">ارسال</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>
