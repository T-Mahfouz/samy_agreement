<script setup lang="ts">
import Pagination from '@/components/admin/Pagination.vue';
import { Card, CardContent } from '@/components/ui/card';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { type BreadcrumbItem, type Paginated } from '@/types';
import { Head, Link } from '@inertiajs/vue3';

interface Offer {
    id: number;
    financial_value: string | null;
    technical_check: string;
    is_awarded: boolean;
    status: string;
    submitted_at: string | null;
    tender?: { id: number; tender_no: string; name: string } | null;
    provider?: { id: number; company_name: string } | null;
}

defineProps<{
    offers: Paginated<Offer>;
    filter: string | null;
    counts: Record<string, number>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'لوحة التحكم', href: '/admin/dashboard' },
    { title: 'العروض', href: '/admin/offers' },
];

const statusLabels: Record<string, string> = { submitted: 'مقدّم', under_review: 'قيد المراجعة', awarded: 'تمت الترسية', rejected: 'مرفوض' };
const statusClass: Record<string, string> = {
    submitted: 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
    under_review: 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
    awarded: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300',
    rejected: 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
};
const techLabels: Record<string, string> = { pending: 'قيد الفحص', matching: 'مطابق', not_matching: 'غير مطابق' };

const tabs = [
    { key: '', label: 'الكل', countKey: 'all' },
    { key: 'submitted', label: 'مقدّم', countKey: 'submitted' },
    { key: 'awarded', label: 'تمت الترسية', countKey: 'awarded' },
    { key: 'rejected', label: 'مرفوض', countKey: 'rejected' },
];
</script>

<template>
    <Head title="العروض" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4 md:p-6">
            <div>
                <h1 class="text-2xl font-bold">العروض</h1>
                <p class="text-sm text-muted-foreground">كل العروض المقدمة على المنافسات</p>
            </div>

            <div class="flex flex-wrap gap-2">
                <Link
                    v-for="t in tabs"
                    :key="t.key"
                    :href="t.key ? `/admin/offers?status=${t.key}` : '/admin/offers'"
                    class="rounded-full border px-4 py-1.5 text-sm transition-colors"
                    :class="(filter ?? '') === t.key ? 'border-primary bg-primary text-primary-foreground' : 'hover:bg-accent'"
                >
                    {{ t.label }} <span class="mr-1 text-xs opacity-80">({{ counts[t.countKey] ?? 0 }})</span>
                </Link>
            </div>

            <Card>
                <CardContent class="p-0">
                    <div class="overflow-x-auto">
                    <table class="w-full min-w-[640px] text-sm">
                        <thead>
                            <tr class="border-b text-right text-xs text-muted-foreground">
                                <th class="p-3 font-medium">المورد</th>
                                <th class="p-3 font-medium">المنافسة</th>
                                <th class="p-3 font-medium">قيمة العرض</th>
                                <th class="p-3 font-medium">الفحص الفني</th>
                                <th class="p-3 font-medium">ترسية</th>
                                <th class="p-3 font-medium">الحالة</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="o in offers.data" :key="o.id" class="border-b last:border-0 hover:bg-accent/40">
                                <td class="p-3 font-medium">{{ o.provider?.company_name ?? '—' }}</td>
                                <td class="p-3">
                                    <Link v-if="o.tender" :href="`/admin/tenders/${o.tender.id}`" class="text-primary hover:underline">{{ o.tender.tender_no }}</Link>
                                    <span v-else>—</span>
                                </td>
                                <td class="p-3">{{ o.financial_value ? `${Number(o.financial_value).toLocaleString('ar-EG')} ر.س` : '—' }}</td>
                                <td class="p-3 text-muted-foreground">{{ techLabels[o.technical_check] ?? o.technical_check }}</td>
                                <td class="p-3">{{ o.is_awarded ? '✔ فائز' : '—' }}</td>
                                <td class="p-3"><span class="rounded px-2 py-0.5 text-xs" :class="statusClass[o.status]">{{ statusLabels[o.status] ?? o.status }}</span></td>
                            </tr>
                            <tr v-if="offers.data.length === 0">
                                <td colspan="6" class="p-8 text-center text-muted-foreground">لا توجد عروض</td>
                            </tr>
                        </tbody>
                    </table>
                    </div>
                    <Pagination :links="offers.links" :total="offers.total" />
                </CardContent>
            </Card>
        </div>
    </AdminLayout>
</template>
