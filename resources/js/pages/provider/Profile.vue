<script setup lang="ts">
import PublicLayout from '@/layouts/PublicLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

interface Category { id: number; name: string; parent_id: number | null }
const props = defineProps<{ profile: Record<string, any> | null; categories: Category[]; email: string }>();
const img = (n: string) => `/slice/assets/images/${n}`;

const form = useForm({
    company_name: props.profile?.company_name ?? '',
    commercial_register_no: props.profile?.commercial_register_no ?? '',
    cr_type: props.profile?.cr_type ?? '',
    mobile: props.profile?.mobile ?? '',
    main_category_id: props.profile?.main_category_id ?? '',
    sub_category_id: props.profile?.sub_category_id ?? '',
    activity_description: props.profile?.activity_description ?? '',
});
const rootCategories = computed(() => props.categories.filter((c) => c.parent_id === null));
const subCategories = computed(() => props.categories.filter((c) => String(c.parent_id) === String(form.main_category_id)));
const submit = () => form.put('/provider/profile', { preserveScroll: true });
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
                            تحديث بيانات المورّد
                        </h3>
                        <Link href="/provider/dashboard" class="main_btn dark_light d-inline-flex align-items-center gap-2">
                            <span class="img_box white_bc main-color d-inline-flex align-items-center justify-content-center fw-bold fs-24"><img :src="img('details-arrow.png')" alt=""></span>
                            رجوع للوحة التحكم
                        </Link>
                    </div>
                    <div class="col-12">
                        <form @submit.prevent="submit">
                            <div class="border_box p_24 white_bc mb_32">
                                <div class="row">
                                    <div class="col-12 col-lg-4"><div class="form-group"><label>اسم المنشأة</label><input type="text" class="form-control" v-model="form.company_name"><small v-if="form.errors.company_name" class="red-color">{{ form.errors.company_name }}</small></div></div>
                                    <div class="col-12 col-lg-4"><div class="form-group"><label>رقم السجل التجاري</label><input type="text" inputmode="numeric" class="form-control" v-model="form.commercial_register_no"><small v-if="form.errors.commercial_register_no" class="red-color d-block">{{ form.errors.commercial_register_no }}</small></div></div>
                                    <div class="col-12 col-lg-4"><div class="form-group"><label>نوع السجل التجاري</label><input type="text" class="form-control" v-model="form.cr_type"></div></div>
                                    <div class="col-12 col-lg-4"><div class="form-group"><label>رقم الجوال</label><input type="tel" inputmode="numeric" class="form-control" v-model="form.mobile" placeholder="05xxxxxxxx" dir="ltr"><small v-if="form.errors.mobile" class="red-color d-block">{{ form.errors.mobile }}</small></div></div>
                                    <div class="col-12 col-lg-4"><div class="form-group"><label>البريد الإلكتروني</label><input type="email" class="form-control" :value="email" disabled dir="ltr"></div></div>
                                    <div class="col-12 col-lg-4"><div class="form-group"><label>القطاع الرئيسي</label><select class="form-control" v-model="form.main_category_id" @change="form.sub_category_id = ''"><option value="">اختر القطاع</option><option v-for="c in rootCategories" :key="c.id" :value="c.id">{{ c.name }}</option></select></div></div>
                                    <div class="col-12 col-lg-4"><div class="form-group"><label>النشاط الفرعي</label><select class="form-control" v-model="form.sub_category_id"><option value="">اختر النشاط</option><option v-for="c in subCategories" :key="c.id" :value="c.id">{{ c.name }}</option></select></div></div>
                                    <div class="col-12 col-lg-8"><div class="form-group mb-0"><label>وصف النشاط</label><input type="text" class="form-control" v-model="form.activity_description"></div></div>
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
