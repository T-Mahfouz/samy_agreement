<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { ArrowRight, Check, Download, FileText, X } from 'lucide-vue-next';

interface Doc {
    id: number;
    doc_type: string;
    file_path: string;
    uploaded_at: string | null;
}
interface Provider {
    id: number;
    company_name: string;
    commercial_register_no: string | null;
    cr_issue_date: string | null;
    cr_issue_date_hijri: string | null;
    cr_type: string | null;
    mobile: string | null;
    activity_description: string | null;
    status: 'pending' | 'approved' | 'rejected';
    user?: { id: number; name: string; username: string | null; email: string; phone: string | null; status: string } | null;
    main_category?: { id: number; name: string } | null;
    sub_category?: { id: number; name: string } | null;
    documents: Doc[];
}

const props = defineProps<{ provider: Provider }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'لوحة التحكم', href: '/admin/dashboard' },
    { title: 'الموردون', href: '/admin/providers' },
    { title: props.provider.company_name, href: `/admin/providers/${props.provider.id}` },
];

const statusLabels: Record<string, string> = { pending: 'بانتظار الاعتماد', approved: 'معتمد', rejected: 'مرفوض' };
const statusClass: Record<string, string> = {
    pending: 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
    approved: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300',
    rejected: 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
};

const docLabels: Record<string, string> = {
    commercial_register: 'السجل التجاري',
    zakat_cert: 'شهادة الزكاة',
    tax_cert: 'شهادة الضريبة',
    sector_classification: 'تصنيف القطاع',
    social_insurance: 'التأمينات الاجتماعية',
    saudization_cert: 'شهادة السعودة',
    investment_license: 'رخصة الاستثمار',
    municipality_license: 'رخصة البلدية',
    chamber_membership: 'انتساب الغرفة التجارية',
    contractors_authority_cert: 'انتساب هيئة المقاولين',
    sme_authority_cert: 'هيئة المنشآت الصغيرة والمتوسطة',
    other_licenses: 'رخص أخرى',
    authorized_signatory_letter: 'خطاب المفوض بالتوقيع',
    authorized_signatory_id: 'هوية المفوض بالتوقيع',
    manager_id: 'هوية مدير الشركة',
};

const info = [
    { label: 'رقم السجل التجاري', value: props.provider.commercial_register_no },
    { label: 'نوع السجل', value: props.provider.cr_type },
    { label: 'تاريخ الإصدار (ميلادي)', value: props.provider.cr_issue_date },
    { label: 'تاريخ الإصدار (هجري)', value: props.provider.cr_issue_date_hijri },
    { label: 'الجوال', value: props.provider.mobile },
    { label: 'القطاع الرئيسي', value: props.provider.main_category?.name },
    { label: 'النشاط الفرعي', value: props.provider.sub_category?.name },
];

const setStatus = (status: string) => {
    const verb = status === 'approved' ? 'اعتماد' : 'رفض';
    if (confirm(`${verb} المورّد «${props.provider.company_name}»؟`)) {
        router.put(`/admin/providers/${props.provider.id}`, { status }, { preserveScroll: true });
    }
};

</script>

<template>
    <Head :title="provider.company_name" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4 md:p-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <Button variant="ghost" size="icon" as-child><a href="/admin/providers"><ArrowRight class="size-5" /></a></Button>
                    <div>
                        <h1 class="text-2xl font-bold">{{ provider.company_name }}</h1>
                        <span class="rounded px-2 py-0.5 text-xs" :class="statusClass[provider.status]">{{ statusLabels[provider.status] }}</span>
                    </div>
                </div>
                <div class="flex gap-2">
                    <Button v-if="provider.status !== 'approved'" class="bg-emerald-600 hover:bg-emerald-700" @click="setStatus('approved')"><Check class="size-4" /> اعتماد</Button>
                    <Button v-if="provider.status !== 'rejected'" variant="outline" class="text-red-600" @click="setStatus('rejected')"><X class="size-4" /> رفض</Button>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
                <!-- Company info -->
                <Card class="lg:col-span-2">
                    <CardHeader><CardTitle class="text-base">بيانات المنشأة</CardTitle></CardHeader>
                    <CardContent>
                        <dl class="grid grid-cols-1 gap-x-6 gap-y-3 sm:grid-cols-2">
                            <div v-for="row in info" :key="row.label" class="flex justify-between gap-2 border-b pb-2 text-sm">
                                <dt class="text-muted-foreground">{{ row.label }}</dt>
                                <dd class="font-medium">{{ row.value || '—' }}</dd>
                            </div>
                        </dl>
                        <div v-if="provider.activity_description" class="mt-4">
                            <div class="mb-1 text-sm text-muted-foreground">وصف النشاط</div>
                            <p class="rounded-lg border bg-muted/30 p-3 text-sm leading-relaxed">{{ provider.activity_description }}</p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Account info -->
                <Card>
                    <CardHeader><CardTitle class="text-base">حساب المستخدم</CardTitle></CardHeader>
                    <CardContent class="space-y-3 text-sm">
                        <div class="flex justify-between border-b pb-2"><span class="text-muted-foreground">الاسم</span><span class="font-medium">{{ provider.user?.name }}</span></div>
                        <div class="flex justify-between border-b pb-2"><span class="text-muted-foreground">اسم المستخدم</span><span class="font-medium">{{ provider.user?.username ?? '—' }}</span></div>
                        <div class="flex justify-between border-b pb-2"><span class="text-muted-foreground">البريد</span><span class="font-medium" dir="ltr">{{ provider.user?.email }}</span></div>
                        <div class="flex justify-between"><span class="text-muted-foreground">حالة الحساب</span><span class="font-medium">{{ provider.user?.status }}</span></div>
                    </CardContent>
                </Card>
            </div>

            <!-- Documents -->
            <Card>
                <CardHeader><CardTitle class="text-base">المستندات ({{ provider.documents.length }})</CardTitle></CardHeader>
                <CardContent>
                    <div v-if="provider.documents.length === 0" class="py-6 text-center text-sm text-muted-foreground">لم يرفع المورّد أي مستندات</div>
                    <div v-else class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        <a
                            v-for="d in provider.documents"
                            :key="d.id"
                            :href="`/provider-documents/${d.id}/download`"
                            target="_blank"
                            rel="noopener"
                            class="flex items-center justify-between gap-2 rounded-lg border p-3 text-sm transition-colors hover:bg-accent"
                        >
                            <span class="flex items-center gap-2">
                                <FileText class="size-4 text-muted-foreground" />
                                {{ docLabels[d.doc_type] ?? d.doc_type }}
                            </span>
                            <Download class="size-4 text-muted-foreground" />
                        </a>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AdminLayout>
</template>
