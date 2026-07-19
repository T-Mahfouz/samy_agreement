<script setup lang="ts">
import Pagination from '@/components/admin/Pagination.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { type BreadcrumbItem, type Paginated } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Check, FileText, X } from 'lucide-vue-next';
import { ref } from 'vue';

interface Payment {
    id: number;
    type: 'brochure_fee' | 'commission';
    paid_to: 'client' | 'platform';
    amount: string;
    receipt_file: string | null;
    status: 'pending' | 'paid' | 'rejected';
    reviewed_at: string | null;
    created_at: string;
    provider?: { id: number; company_name: string } | null;
    tender?: { id: number; tender_no: string; name: string } | null;
    reviewer?: { id: number; name: string } | null;
}

const props = defineProps<{
    payments: Paginated<Payment>;
    filters: { status: string | null; type: string | null; q: string | null };
    counts: { all: number; pending: number; paid: number; rejected: number };
    typeCounts: { brochure_fee: number; commission: number };
    totalAmount: number;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'لوحة التحكم', href: '/admin/dashboard' },
    { title: 'المدفوعات', href: '/admin/payments' },
];

const statusLabels: Record<string, string> = { pending: 'قيد المراجعة', paid: 'مدفوعة', rejected: 'مرفوضة' };
const statusClass: Record<string, string> = {
    pending: 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
    paid: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300',
    rejected: 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
};
const typeLabels: Record<string, string> = { brochure_fee: 'كراسة الشروط', commission: 'عمولة المنصة' };
const paidToLabels: Record<string, string> = { client: 'العميل', platform: 'المنصة' };

const tabs = [
    { key: '', label: 'الكل', countKey: 'all' as const },
    { key: 'pending', label: 'قيد المراجعة', countKey: 'pending' as const },
    { key: 'paid', label: 'مدفوعة', countKey: 'paid' as const },
    { key: 'rejected', label: 'مرفوضة', countKey: 'rejected' as const },
];

const typeTabs = [
    { key: 'brochure_fee', label: 'مدفوعات كراسة الشروط', countKey: 'brochure_fee' as const },
    { key: 'commission', label: 'مدفوعات عمولة المنصة', countKey: 'commission' as const },
];

const buildHref = (params: { status?: string; type?: string; q?: string }) => {
    const qp = new URLSearchParams();
    const type = params.type !== undefined ? params.type : props.filters.type ?? '';
    const status = params.status !== undefined ? params.status : props.filters.status ?? '';
    const search = params.q !== undefined ? params.q : props.filters.q ?? '';
    if (type) qp.set('type', type);
    if (status) qp.set('status', status);
    if (search) qp.set('q', search);
    const s = qp.toString();
    return '/admin/payments' + (s ? `?${s}` : '');
};

const searchTerm = ref(props.filters.q ?? '');
const doSearch = () => router.get(buildHref({ q: searchTerm.value }), {}, { preserveScroll: true, preserveState: true });

const setStatus = (p: Payment, status: string) => {
    const verb = status === 'paid' ? 'اعتماد' : 'رفض';
    if (confirm(`${verb} دفعة «${typeLabels[p.type]}» بقيمة ${Number(p.amount).toLocaleString('ar-EG')} ر.س؟`)) {
        router.put(`/admin/payments/${p.id}`, { status }, { preserveScroll: true });
    }
};

</script>

<template>
    <Head title="المدفوعات" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4 md:p-6">
            <div>
                <h1 class="text-2xl font-bold">المدفوعات</h1>
                <p class="text-sm text-muted-foreground">مراجعة إيصالات التحويل واعتمادها</p>
            </div>

            <div class="flex flex-wrap gap-2 border-b">
                <Link
                    v-for="tt in typeTabs"
                    :key="tt.key"
                    :href="buildHref({ type: tt.key, status: '', q: '' })"
                    class="-mb-px border-b-2 px-4 py-2 text-sm font-medium transition-colors"
                    :class="(filters.type ?? 'brochure_fee') === tt.key ? 'border-primary text-primary' : 'border-transparent text-muted-foreground hover:text-foreground'"
                >
                    {{ tt.label }} <span class="text-xs opacity-80">({{ typeCounts[tt.countKey] }})</span>
                </Link>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex flex-wrap gap-2">
                    <Link
                        v-for="t in tabs"
                        :key="t.key"
                        :href="buildHref({ status: t.key })"
                        class="rounded-full border px-4 py-1.5 text-sm transition-colors"
                        :class="(filters.status ?? '') === t.key ? 'border-primary bg-primary text-primary-foreground' : 'hover:bg-accent'"
                    >
                        {{ t.label }} <span class="mr-1 text-xs opacity-80">({{ counts[t.countKey] }})</span>
                    </Link>
                </div>
                <form class="flex items-center gap-2" @submit.prevent="doSearch">
                    <input v-model="searchTerm" type="search" placeholder="بحث باسم المورد أو رقم المنافسة" class="h-9 w-64 max-w-full rounded-md border border-input bg-transparent px-3 text-sm shadow-sm" />
                    <Button type="submit" variant="outline" size="sm">بحث</Button>
                </form>
            </div>

            <div class="flex items-center gap-2 text-sm">
                <span class="text-muted-foreground">إجمالي القيمة (حسب التصفية الحالية):</span>
                <span class="font-bold">{{ Number(totalAmount).toLocaleString('ar-EG') }} ر.س</span>
            </div>

            <Card>
                <CardContent class="p-0">
                    <div class="overflow-x-auto">
                    <table class="w-full min-w-[760px] text-sm">
                        <thead>
                            <tr class="border-b text-right text-xs text-muted-foreground">
                                <th class="p-3 font-medium">المورد</th>
                                <th class="p-3 font-medium">المنافسة</th>
                                <th class="p-3 font-medium">القيمة</th>
                                <th class="p-3 font-medium">تُدفع إلى</th>
                                <th class="p-3 font-medium">الإيصال</th>
                                <th class="p-3 font-medium">الحالة</th>
                                <th class="p-3 font-medium">إجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="p in payments.data" :key="p.id" class="border-b last:border-0 hover:bg-accent/40">
                                <td class="p-3 text-muted-foreground">{{ p.provider?.company_name ?? '—' }}</td>
                                <td class="p-3 text-muted-foreground">{{ p.tender?.tender_no ?? '—' }}</td>
                                <td class="p-3 font-medium">{{ Number(p.amount).toLocaleString('ar-EG') }} ر.س</td>
                                <td class="p-3 text-muted-foreground">{{ paidToLabels[p.paid_to] }}</td>
                                <td class="p-3">
                                    <a v-if="p.receipt_file" :href="`/payments/${p.id}/receipt`" target="_blank" rel="noopener" class="inline-flex items-center gap-1 text-primary hover:underline">
                                        <FileText class="size-4" /> عرض
                                    </a>
                                    <span v-else class="text-muted-foreground">—</span>
                                </td>
                                <td class="p-3"><span class="rounded px-2 py-0.5 text-xs" :class="statusClass[p.status]">{{ statusLabels[p.status] }}</span></td>
                                <td class="p-3">
                                    <div v-if="p.status === 'pending'" class="flex gap-1">
                                        <Button variant="ghost" size="icon" class="text-emerald-600 hover:text-emerald-700" title="اعتماد" @click="setStatus(p, 'paid')"><Check class="size-4" /></Button>
                                        <Button variant="ghost" size="icon" class="text-red-600 hover:text-red-700" title="رفض" @click="setStatus(p, 'rejected')"><X class="size-4" /></Button>
                                    </div>
                                    <span v-else class="text-xs text-muted-foreground">{{ p.reviewer?.name ?? '—' }}</span>
                                </td>
                            </tr>
                            <tr v-if="payments.data.length === 0">
                                <td colspan="7" class="p-8 text-center text-muted-foreground">لا توجد مدفوعات</td>
                            </tr>
                        </tbody>
                    </table>
                    </div>
                    <Pagination :links="payments.links" :total="payments.total" />
                </CardContent>
            </Card>
        </div>
    </AdminLayout>
</template>
