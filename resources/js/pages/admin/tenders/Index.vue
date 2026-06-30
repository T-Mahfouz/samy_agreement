<script setup lang="ts">
import Pagination from '@/components/admin/Pagination.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { type BreadcrumbItem, type Paginated } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Eye } from 'lucide-vue-next';

interface Tender {
    id: number;
    tender_no: string;
    reference_no: string | null;
    name: string;
    type: string;
    brochure_price: string;
    offers_deadline: string | null;
    offers_count: number;
    status: string;
    client?: { id: number; company_name: string } | null;
    category?: { id: number; name: string } | null;
}

defineProps<{
    tenders: Paginated<Tender>;
    filter: string | null;
    counts: Record<string, number>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'لوحة التحكم', href: '/admin/dashboard' },
    { title: 'المنافسات', href: '/admin/tenders' },
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

const tabs = [
    { key: '', label: 'الكل' },
    { key: 'active', label: 'نشطة' },
    { key: 'examination', label: 'فحص العروض' },
    { key: 'awarding', label: 'الترسية' },
    { key: 'awarded', label: 'تمت الترسية' },
    { key: 'cancelled', label: 'ملغاة' },
];
</script>

<template>
    <Head title="المنافسات" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4 md:p-6">
            <div>
                <h1 class="text-2xl font-bold">المنافسات</h1>
                <p class="text-sm text-muted-foreground">كل المنافسات المنشورة على المنصة</p>
            </div>

            <div class="flex flex-wrap gap-2">
                <Link
                    v-for="t in tabs"
                    :key="t.key"
                    :href="t.key ? `/admin/tenders?status=${t.key}` : '/admin/tenders'"
                    class="rounded-full border px-4 py-1.5 text-sm transition-colors"
                    :class="(filter ?? '') === t.key ? 'border-primary bg-primary text-primary-foreground' : 'hover:bg-accent'"
                >
                    {{ t.label }} <span class="mr-1 text-xs opacity-80">({{ t.key ? (counts[t.key] ?? 0) : counts.all }})</span>
                </Link>
            </div>

            <Card>
                <CardContent class="p-0">
                    <div class="overflow-x-auto">
                    <table class="w-full min-w-[640px] text-sm">
                        <thead>
                            <tr class="border-b text-right text-xs text-muted-foreground">
                                <th class="p-3 font-medium">رقم المنافسة</th>
                                <th class="p-3 font-medium">الاسم</th>
                                <th class="p-3 font-medium">الجهة</th>
                                <th class="p-3 font-medium">النوع</th>
                                <th class="p-3 font-medium">كراسة الشروط</th>
                                <th class="p-3 font-medium">العروض</th>
                                <th class="p-3 font-medium">الحالة</th>
                                <th class="p-3 font-medium">إجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="t in tenders.data" :key="t.id" class="border-b last:border-0 hover:bg-accent/40">
                                <td class="p-3">{{ t.tender_no }}</td>
                                <td class="p-3"><span class="line-clamp-1 max-w-[220px] font-medium">{{ t.name }}</span></td>
                                <td class="p-3 text-muted-foreground">{{ t.client?.company_name ?? '—' }}</td>
                                <td class="p-3 text-muted-foreground">{{ typeLabels[t.type] ?? t.type }}</td>
                                <td class="p-3 text-muted-foreground">{{ Number(t.brochure_price) > 0 ? `${Number(t.brochure_price).toLocaleString('ar-EG')} ر.س` : 'مجانية' }}</td>
                                <td class="p-3 text-muted-foreground">{{ t.offers_count }}</td>
                                <td class="p-3"><span class="rounded px-2 py-0.5 text-xs" :class="statusClass[t.status]">{{ statusLabels[t.status] ?? t.status }}</span></td>
                                <td class="p-3">
                                    <Button variant="ghost" size="icon" as-child><Link :href="`/admin/tenders/${t.id}`"><Eye class="size-4" /></Link></Button>
                                </td>
                            </tr>
                            <tr v-if="tenders.data.length === 0">
                                <td colspan="8" class="p-8 text-center text-muted-foreground">لا توجد منافسات</td>
                            </tr>
                        </tbody>
                    </table>
                    </div>
                    <Pagination :links="tenders.links" :total="tenders.total" />
                </CardContent>
            </Card>
        </div>
    </AdminLayout>
</template>
