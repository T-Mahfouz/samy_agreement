<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowRight, Ban, RotateCcw } from 'lucide-vue-next';

interface Offer {
    id: number;
    financial_value: string | null;
    technical_check: string;
    is_awarded: boolean;
    status: string;
    provider?: { id: number; company_name: string } | null;
}
interface Location {
    id: number;
    region?: { id: number; name: string } | null;
    city?: { id: number; name: string } | null;
}
interface Tender {
    id: number;
    tender_no: string;
    reference_no: string | null;
    serial_no: string;
    name: string;
    type: string;
    purpose: string | null;
    activity_description: string | null;
    submission_method: string | null;
    includes_supply_items: boolean;
    brochure_price: string;
    contract_duration_months: number | null;
    insurance_required: boolean;
    initial_guarantee_required: boolean;
    initial_guarantee_value: string | null;
    final_guarantee_required: boolean;
    final_guarantee_value: string | null;
    commission_rate: string;
    questions_deadline: string | null;
    offers_deadline: string | null;
    offers_deadline_hijri: string | null;
    offers_open: string | null;
    expected_award_date: string | null;
    works_start: string | null;
    status: string;
    client?: { id: number; company_name: string; mobile: string | null; bank_name: string | null; bank_iban: string | null } | null;
    category?: { id: number; name: string } | null;
    subcategory?: { id: number; name: string } | null;
    locations: Location[];
    offers: Offer[];
    contract?: { id: number; status: string } | null;
}

const props = defineProps<{ tender: Tender }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'لوحة التحكم', href: '/admin/dashboard' },
    { title: 'المنافسات', href: '/admin/tenders' },
    { title: props.tender.tender_no, href: `/admin/tenders/${props.tender.id}` },
];

const typeLabels: Record<string, string> = { general: 'منافسة عامة', direct_purchase: 'شراء مباشر', limited: 'محدودة' };
const statusLabels: Record<string, string> = { active: 'نشطة', examination: 'فحص العروض', awarding: 'مرحلة الترسية', awarded: 'تمت الترسية', cancelled: 'ملغاة' };
const statusClass: Record<string, string> = {
    active: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300',
    examination: 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
    awarding: 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
    awarded: 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300',
    cancelled: 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
};
const techLabels: Record<string, string> = { pending: 'قيد الفحص', matching: 'مطابق', not_matching: 'غير مطابق' };

const info = [
    { label: 'الرقم المرجعي', value: props.tender.reference_no },
    { label: 'الرقم التسلسلي', value: props.tender.serial_no },
    { label: 'النوع', value: typeLabels[props.tender.type] },
    { label: 'القطاع', value: props.tender.category?.name },
    { label: 'النشاط الفرعي', value: props.tender.subcategory?.name },
    { label: 'قيمة كراسة الشروط', value: Number(props.tender.brochure_price) > 0 ? `${Number(props.tender.brochure_price).toLocaleString('ar-EG')} ر.س` : 'مجانية' },
    { label: 'مدة العقد (شهور)', value: props.tender.contract_duration_months },
    { label: 'نسبة العمولة', value: `${props.tender.commission_rate}%` },
    { label: 'التأمين', value: props.tender.insurance_required ? 'مطلوب' : 'غير مطلوب' },
    { label: 'بنود توريد', value: props.tender.includes_supply_items ? 'يوجد' : 'لا يوجد' },
];

const dates = [
    { label: 'آخر موعد للاستفسارات', value: props.tender.questions_deadline },
    { label: 'آخر موعد لتقديم العروض', value: props.tender.offers_deadline },
    { label: 'تاريخ فتح العروض', value: props.tender.offers_open },
    { label: 'التاريخ المتوقع للترسية', value: props.tender.expected_award_date },
    { label: 'بدء الأعمال', value: props.tender.works_start },
];

const setStatus = (status: string) => {
    const verb = status === 'cancelled' ? 'إلغاء' : 'إعادة تفعيل';
    if (confirm(`${verb} المنافسة «${props.tender.name}»؟`)) {
        router.put(`/admin/tenders/${props.tender.id}`, { status }, { preserveScroll: true });
    }
};
</script>

<template>
    <Head :title="tender.tender_no" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4 md:p-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <Button variant="ghost" size="icon" as-child><a href="/admin/tenders"><ArrowRight class="size-5" /></a></Button>
                    <div>
                        <h1 class="text-xl font-bold">{{ tender.name }}</h1>
                        <div class="flex items-center gap-2 text-sm text-muted-foreground">
                            <span>رقم {{ tender.tender_no }}</span>
                            <span class="rounded px-2 py-0.5 text-xs" :class="statusClass[tender.status]">{{ statusLabels[tender.status] }}</span>
                        </div>
                    </div>
                </div>
                <div class="flex gap-2">
                    <Button v-if="tender.status !== 'cancelled'" variant="outline" class="text-red-600" @click="setStatus('cancelled')"><Ban class="size-4" /> إلغاء المنافسة</Button>
                    <Button v-else variant="outline" @click="setStatus('active')"><RotateCcw class="size-4" /> إعادة تفعيل</Button>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
                <Card class="lg:col-span-2">
                    <CardHeader><CardTitle class="text-base">البيانات الأساسية</CardTitle></CardHeader>
                    <CardContent>
                        <dl class="grid grid-cols-1 gap-x-6 gap-y-3 sm:grid-cols-2">
                            <div v-for="row in info" :key="row.label" class="flex justify-between gap-2 border-b pb-2 text-sm">
                                <dt class="text-muted-foreground">{{ row.label }}</dt>
                                <dd class="font-medium">{{ row.value ?? '—' }}</dd>
                            </div>
                        </dl>
                        <div v-if="tender.purpose" class="mt-4">
                            <div class="mb-1 text-sm text-muted-foreground">الغرض</div>
                            <p class="rounded-lg border bg-muted/30 p-3 text-sm leading-relaxed">{{ tender.purpose }}</p>
                        </div>
                    </CardContent>
                </Card>

                <div class="space-y-4">
                    <Card>
                        <CardHeader><CardTitle class="text-base">الجهة (العميل)</CardTitle></CardHeader>
                        <CardContent class="space-y-2 text-sm">
                            <div class="flex justify-between border-b pb-2"><span class="text-muted-foreground">المنشأة</span><span class="font-medium">{{ tender.client?.company_name }}</span></div>
                            <div class="flex justify-between border-b pb-2"><span class="text-muted-foreground">الجوال</span><span dir="ltr">{{ tender.client?.mobile ?? '—' }}</span></div>
                            <div class="flex justify-between"><span class="text-muted-foreground">البنك</span><span>{{ tender.client?.bank_name ?? '—' }}</span></div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader><CardTitle class="text-base">المواعيد</CardTitle></CardHeader>
                        <CardContent class="space-y-2 text-sm">
                            <div v-for="d in dates" :key="d.label" class="flex justify-between border-b pb-2 last:border-0">
                                <span class="text-muted-foreground">{{ d.label }}</span><span class="font-medium">{{ d.value ?? '—' }}</span>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>

            <Card>
                <CardHeader><CardTitle class="text-base">أماكن التنفيذ</CardTitle></CardHeader>
                <CardContent>
                    <div v-if="tender.locations.length === 0" class="text-sm text-muted-foreground">لا توجد أماكن محددة</div>
                    <div v-else class="flex flex-wrap gap-2">
                        <span v-for="l in tender.locations" :key="l.id" class="rounded-full border px-3 py-1 text-sm">
                            {{ l.region?.name }} — {{ l.city?.name }}
                        </span>
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader><CardTitle class="text-base">العروض المقدمة ({{ tender.offers.length }})</CardTitle></CardHeader>
                <CardContent class="p-0">
                    <div v-if="tender.offers.length" class="overflow-x-auto">
                    <table class="w-full min-w-[560px] text-sm">
                        <thead>
                            <tr class="border-b text-right text-xs text-muted-foreground">
                                <th class="p-3 font-medium">المورد</th>
                                <th class="p-3 font-medium">قيمة العرض</th>
                                <th class="p-3 font-medium">الفحص الفني</th>
                                <th class="p-3 font-medium">ترسية</th>
                                <th class="p-3 font-medium">الحالة</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="o in tender.offers" :key="o.id" class="border-b last:border-0" :class="{ 'bg-emerald-50 dark:bg-emerald-950/20': o.is_awarded }">
                                <td class="p-3 font-medium">{{ o.provider?.company_name ?? '—' }}</td>
                                <td class="p-3">{{ o.financial_value ? `${Number(o.financial_value).toLocaleString('ar-EG')} ر.س` : '—' }}</td>
                                <td class="p-3 text-muted-foreground">{{ techLabels[o.technical_check] ?? o.technical_check }}</td>
                                <td class="p-3">{{ o.is_awarded ? '✔ فائز' : '—' }}</td>
                                <td class="p-3 text-muted-foreground">{{ o.status }}</td>
                            </tr>
                        </tbody>
                    </table>
                    </div>
                    <p v-else class="p-6 text-center text-sm text-muted-foreground">لا توجد عروض مقدمة</p>
                </CardContent>
            </Card>

            <Card v-if="tender.contract">
                <CardContent class="flex items-center justify-between py-4">
                    <span class="text-sm">يوجد عقد مرتبط بهذه المنافسة</span>
                    <Button variant="outline" as-child><Link :href="`/admin/contracts/${tender.contract.id}`">عرض العقد</Link></Button>
                </CardContent>
            </Card>
        </div>
    </AdminLayout>
</template>
