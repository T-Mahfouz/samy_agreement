<script setup lang="ts">
import Pagination from '@/components/admin/Pagination.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { type BreadcrumbItem, type Paginated } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Eye } from 'lucide-vue-next';

interface Contract {
    id: number;
    contract_value: string | null;
    documentation_date: string | null;
    client_signed_at: string | null;
    provider_signed_at: string | null;
    status: string;
    tender?: { id: number; tender_no: string; name: string } | null;
    client?: { id: number; company_name: string } | null;
    provider?: { id: number; company_name: string } | null;
}

defineProps<{
    contracts: Paginated<Contract>;
    filter: string | null;
    counts: Record<string, number>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'لوحة التحكم', href: '/admin/dashboard' },
    { title: 'العقود', href: '/admin/contracts' },
];

const statusLabels: Record<string, string> = { awaiting_signature: 'بانتظار التوقيع', active: 'ساري', completed: 'مكتمل', cancelled: 'ملغي' };
const statusClass: Record<string, string> = {
    awaiting_signature: 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
    active: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300',
    completed: 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300',
    cancelled: 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
};

const tabs = [
    { key: '', label: 'الكل', countKey: 'all' },
    { key: 'awaiting_signature', label: 'بانتظار التوقيع', countKey: 'awaiting_signature' },
    { key: 'active', label: 'ساري', countKey: 'active' },
    { key: 'completed', label: 'مكتمل', countKey: 'completed' },
    { key: 'cancelled', label: 'ملغي', countKey: 'cancelled' },
];
</script>

<template>
    <Head title="العقود" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4 md:p-6">
            <div>
                <h1 class="text-2xl font-bold">العقود</h1>
                <p class="text-sm text-muted-foreground">العقود الإلكترونية بين العملاء والموردين</p>
            </div>

            <div class="flex flex-wrap gap-2">
                <Link
                    v-for="t in tabs"
                    :key="t.key"
                    :href="t.key ? `/admin/contracts?status=${t.key}` : '/admin/contracts'"
                    class="rounded-full border px-4 py-1.5 text-sm transition-colors"
                    :class="(filter ?? '') === t.key ? 'border-primary bg-primary text-primary-foreground' : 'hover:bg-accent'"
                >
                    {{ t.label }} <span class="mr-1 text-xs opacity-80">({{ counts[t.countKey] ?? 0 }})</span>
                </Link>
            </div>

            <Card>
                <CardContent class="p-0">
                    <div class="overflow-x-auto">
                    <table class="w-full min-w-[700px] text-sm">
                        <thead>
                            <tr class="border-b text-right text-xs text-muted-foreground">
                                <th class="p-3 font-medium">المنافسة</th>
                                <th class="p-3 font-medium">العميل</th>
                                <th class="p-3 font-medium">المورد</th>
                                <th class="p-3 font-medium">القيمة</th>
                                <th class="p-3 font-medium">التوقيعات</th>
                                <th class="p-3 font-medium">الحالة</th>
                                <th class="p-3 font-medium">إجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="c in contracts.data" :key="c.id" class="border-b last:border-0 hover:bg-accent/40">
                                <td class="p-3">{{ c.tender?.tender_no ?? '—' }}</td>
                                <td class="p-3 text-muted-foreground">{{ c.client?.company_name ?? '—' }}</td>
                                <td class="p-3 text-muted-foreground">{{ c.provider?.company_name ?? '—' }}</td>
                                <td class="p-3">{{ c.contract_value ? `${Number(c.contract_value).toLocaleString('ar-EG')} ر.س` : '—' }}</td>
                                <td class="p-3 text-xs">
                                    <span :class="c.client_signed_at ? 'text-emerald-600' : 'text-muted-foreground'">عميل {{ c.client_signed_at ? '✔' : '✗' }}</span>
                                    /
                                    <span :class="c.provider_signed_at ? 'text-emerald-600' : 'text-muted-foreground'">مورد {{ c.provider_signed_at ? '✔' : '✗' }}</span>
                                </td>
                                <td class="p-3"><span class="rounded px-2 py-0.5 text-xs" :class="statusClass[c.status]">{{ statusLabels[c.status] ?? c.status }}</span></td>
                                <td class="p-3">
                                    <Button variant="ghost" size="icon" as-child><Link :href="`/admin/contracts/${c.id}`"><Eye class="size-4" /></Link></Button>
                                </td>
                            </tr>
                            <tr v-if="contracts.data.length === 0">
                                <td colspan="7" class="p-8 text-center text-muted-foreground">لا توجد عقود</td>
                            </tr>
                        </tbody>
                    </table>
                    </div>
                    <Pagination :links="contracts.links" :total="contracts.total" />
                </CardContent>
            </Card>
        </div>
    </AdminLayout>
</template>
