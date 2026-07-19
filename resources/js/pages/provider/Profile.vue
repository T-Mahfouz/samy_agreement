<script setup lang="ts">
import FileTypeHint from '@/components/FileTypeHint.vue';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

interface Category { id: number; name: string; parent_id: number | null }
interface DocInfo { id: number; name: string; url: string }
const props = defineProps<{
    profile: Record<string, any> | null;
    documents: Record<string, DocInfo>;
    categories: Category[];
    username: string | null;
    email: string;
}>();

const img = (n: string) => `/slice/assets/images/${n}`;

const today = (() => {
    const d = new Date();
    return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
})();

const docFields = [
    { name: 'attach_cr', label: 'السجل التجاري' },
    { name: 'attach_zakat', label: 'شهادة الزكاة' },
    { name: 'attach_tax', label: 'شهادة الضريبة' },
    { name: 'attach_sector_class', label: 'تصنيف القطاع' },
    { name: 'attach_social_insurance', label: 'التأمينات الإجتماعية' },
    { name: 'attach_saudization', label: 'شهادة السعودة' },
    { name: 'attach_investment_license', label: 'رخصة الإستثمار' },
    { name: 'attach_municipal_license', label: 'رخصة البلدية' },
    { name: 'attach_chamber', label: 'شهادة انتساب الغرفة التجارية' },
    { name: 'attach_contractors_auth', label: 'شهادة انتساب الهيئة السعودية للمقاولين' },
    { name: 'attach_sme', label: 'شهادة هيئة المنشآت الصغيرة والمتوسطة' },
    { name: 'attach_other_licenses', label: 'رخص اخرى' },
    { name: 'attach_auth_letter', label: 'صورة خطاب المفوض بالتوقيع' },
    { name: 'attach_auth_id', label: 'صورة هوية المفوض بالتوقيع' },
    { name: 'attach_manager_id', label: 'صورة هوية مدير الشركة' },
];

const form = useForm<Record<string, any>>({
    company_name: props.profile?.company_name ?? '',
    commercial_register_no: props.profile?.commercial_register_no ?? '',
    cr_issue_date: props.profile?.cr_issue_date ?? '',
    cr_type: props.profile?.cr_type ?? '',
    mobile: props.profile?.mobile ?? '',
    main_category_id: props.profile?.main_category_id ?? '',
    activity_description: props.profile?.activity_description ?? '',
    username: props.username ?? '',
    email: props.email ?? '',
    password: '',
    password_confirmation: '',
    attach_cr: null, attach_zakat: null, attach_tax: null, attach_sector_class: null,
    attach_social_insurance: null, attach_saudization: null, attach_investment_license: null,
    attach_municipal_license: null, attach_chamber: null, attach_contractors_auth: null,
    attach_sme: null, attach_other_licenses: null, attach_auth_letter: null,
    attach_auth_id: null, attach_manager_id: null,
});

const rootCategories = computed(() => props.categories.filter((c) => c.parent_id === null));

const onFile = (name: string, e: Event) => {
    form[name] = (e.target as HTMLInputElement).files?.[0] ?? null;
};

const clearFiles = () => docFields.forEach((d) => { form[d.name] = null; });

const submit = () => form
    .transform((data) => ({ ...data, _method: 'put' }))
    .post('/provider/profile', {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => { clearFiles(); form.password = ''; form.password_confirmation = ''; },
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
                            تحديث بيانات المورّد
                        </h3>
                        <Link href="/provider/dashboard" class="main_btn dark_light d-inline-flex align-items-center gap-2">
                            <span class="img_box white_bc main-color d-inline-flex align-items-center justify-content-center fw-bold fs-24"><img :src="img('details-arrow.png')" alt=""></span>
                            رجوع للوحة التحكم
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
                                    البيانات الأساسية للمنشأة
                                </h3>
                                <div class="row">
                                    <div class="col-12 col-lg-3 col-md-6 col-sm-6">
                                        <div class="form-group">
                                            <label for="company_name">اسم المنشأة</label>
                                            <input type="text" class="form-control" id="company_name" v-model="form.company_name" placeholder="اسم المنشأة">
                                            <small v-if="form.errors.company_name" class="red-color">{{ form.errors.company_name }}</small>
                                        </div>
                                    </div>
                                    <div class="col-12 col-lg-3 col-md-6 col-sm-6">
                                        <div class="form-group">
                                            <label for="commercial_register_no">رقم السجل التجاري</label>
                                            <input type="text" inputmode="numeric" class="form-control" id="commercial_register_no" v-model="form.commercial_register_no" placeholder="رقم السجل التجاري">
                                            <small v-if="form.errors.commercial_register_no" class="red-color d-block">{{ form.errors.commercial_register_no }}</small>
                                        </div>
                                    </div>
                                    <div class="col-12 col-lg-3 col-md-6 col-sm-6">
                                        <div class="form-group">
                                            <label class="d-flex align-items-center gap-2 mb_12" for="cr_issue_date">تاريخ إصدار السجل التجاري</label>
                                            <input type="date" dir="ltr" :max="today" class="form-control" id="cr_issue_date" v-model="form.cr_issue_date">
                                            <small v-if="form.errors.cr_issue_date" class="red-color d-block">{{ form.errors.cr_issue_date }}</small>
                                        </div>
                                    </div>
                                    <div class="col-12 col-lg-3 col-md-6 col-sm-6">
                                        <div class="form-group">
                                            <label for="cr_type">نوع السجل التجاري</label>
                                            <input type="text" class="form-control" id="cr_type" v-model="form.cr_type" placeholder="نوع السجل التجاري">
                                        </div>
                                    </div>
                                    <div class="col-12 col-lg-3 col-md-6 col-sm-6">
                                        <div class="form-group">
                                            <label for="mobile">رقم الجوال <span class="red-color">*</span></label>
                                            <input type="tel" inputmode="numeric" class="form-control" id="mobile" v-model="form.mobile" placeholder="05xxxxxxxx" dir="ltr">
                                            <small v-if="form.errors.mobile" class="red-color d-block">{{ form.errors.mobile }}</small>
                                        </div>
                                    </div>
                                    <div class="col-12 col-lg-3 col-md-6 col-sm-6">
                                        <div class="form-group">
                                            <label for="main_category_id">القطاع الرئيسي</label>
                                            <select class="form-control" id="main_category_id" v-model="form.main_category_id">
                                                <option value="" disabled>اختر القطاع الرئيسي</option>
                                                <option v-for="c in rootCategories" :key="c.id" :value="c.id">{{ c.name }}</option>
                                            </select>
                                            <small v-if="form.errors.main_category_id" class="red-color d-block">{{ form.errors.main_category_id }}</small>
                                        </div>
                                    </div>
                                    <div class="col-12 col-lg-3 col-md-6 col-sm-6">
                                        <div class="form-group mb-0">
                                            <label for="activity_description">وصف النشاط</label>
                                            <input type="text" class="form-control" id="activity_description" v-model="form.activity_description" placeholder="وصف النشاط">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="border_box p_24 white_bc mb_32">
                                <h3 class="fs-16 dark-color d-flex align-items-center gap-4 mb_24">
                                    <div class="img_box border-bc d-flex align-items-center justify-content-center main-color">
                                        <svg width="20" height="20" viewBox="0 0 24 24" aria-hidden="true" class="m-0">
                                            <path fill="currentColor" d="M16.5 6v11.5a4.5 4.5 0 1 1-9 0V5a2.5 2.5 0 0 1 5 0v10.5a1 1 0 1 1-2 0V6h-2v9.5a3 3 0 0 0 6 0V5a4.5 4.5 0 0 0-9 0v12.5a6 6 0 1 0 12 0V6h-2z" />
                                        </svg>
                                    </div>
                                    المرفقات
                                    <span class="fs-14 dark-color fw-normal">— اختر ملفًا جديدًا لاستبدال الحالي، أو اتركه للإبقاء عليه</span>
                                </h3>
                                <div class="row">
                                    <div v-for="d in docFields" :key="d.name" class="col-12 col-lg-3 col-md-6 col-sm-6">
                                        <div class="form-group">
                                            <label class="d-block mb_12">{{ d.label }}</label>
                                            <label class="tender_book_modal__upload mb-0">
                                                <input type="file" class="sr-only" accept=".pdf,image/jpeg,image/png,image/webp" @change="onFile(d.name, $event)">
                                                <span class="tender_book_modal__upload-inner d-flex align-items-center gap-4">
                                                    <span class="dark-color fs-14">{{ form[d.name]?.name || 'ارفاق الملف' }}</span>
                                                    <svg class="tender_book_modal__clip flex-shrink-0" width="22" height="22" viewBox="0 0 24 24" aria-hidden="true">
                                                        <path fill="#000000" d="M16.5 6v11.5a4.5 4.5 0 1 1-9 0V5a2.5 2.5 0 0 1 5 0v10.5a1 1 0 1 1-2 0V6h-2v9.5a3 3 0 0 0 6 0V5a4.5 4.5 0 0 0-9 0v12.5a6 6 0 1 0 12 0V6h-2z" />
                                                    </svg>
                                                </span>
                                            </label>
                                            <a v-if="documents[d.name]" :href="documents[d.name].url" target="_blank" rel="noopener" class="main-color fs-14 d-inline-flex align-items-center gap-2 mt_8">
                                                📎 الملف الحالي
                                            </a>
                                            <FileTypeHint exts="PDF أو صورة (JPG/PNG/WEBP)" :max-mb="10" />
                                            <div v-if="form.progress && form[d.name]" class="progress mt_8" style="height:6px; background:#e6e9e8; border-radius:6px; overflow:hidden;">
                                                <div class="progress-bar" :style="{ width: form.progress.percentage + '%', height: '100%', background: '#1c9c55', transition: 'width .2s' }"></div>
                                            </div>
                                            <small v-if="form.errors[d.name]" class="red-color d-block">{{ form.errors[d.name] }}</small>
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
