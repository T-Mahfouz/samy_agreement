<script setup lang="ts">
import Pagination from '@/components/admin/Pagination.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { type BreadcrumbItem, type Paginated } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Check, Eye, Trash2, X } from 'lucide-vue-next';

interface Provider {
    id: number;
    company_name: string;
    commercial_register_no: string | null;
    mobile: string | null;
    status: 'pending' | 'approved' | 'rejected';
    documents_count: number;
    user?: { id: number; name: string; email: string; status: string } | null;
    main_category?: { id: number; name: string } | null;
}

defineProps<{
    providers: Paginated<Provider>;
    filter: string | null;
    counts: { all: number; pending: number; approved: number; rejected: number };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'لوحة التحكم', href: '/admin/dashboard' },
    { title: 'الموردون', href: '/admin/providers' },
];

const statusLabels: Record<string, string> = { pending: 'بانتظار الاعتماد', approved: 'معتمد', rejected: 'مرفوض' };
const statusClass: Record<string, string> = {
    pending: 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
    approved: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300',
    rejected: 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
};

const tabs = [
    { key: '', label: 'الكل', countKey: 'all' as const },
    { key: 'pending', label: 'بانتظار الاعتماد', countKey: 'pending' as const },
    { key: 'approved', label: 'معتمد', countKey: 'approved' as const },
    { key: 'rejected', label: 'مرفوض', countKey: 'rejected' as const },
];

const setStatus = (p: Provider, status: string) => {
    const verb = status === 'approved' ? 'اعتماد' : 'رفض';
    if (confirm(`${verb} المورّد «${p.company_name}»؟`)) {
        router.put(`/admin/providers/${p.id}`, { status }, { preserveScroll: true });
    }
};

const deleteProvider = (p: Provider) => {
    if (confirm(`⚠️ حذف نهائي\n\nسيتم حذف المورّد «${p.company_name}» وحسابه وكل عروضه ومستنداته ومدفوعاته نهائيًا، ولا يمكن التراجع.\n\nهل أنت متأكد؟`)) {
        router.delete(`/admin/providers/${p.id}`);
    }
};
</script>

<template>
    <Head title="الموردون" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4 md:p-6">
            <div>
                <h1 class="text-2xl font-bold">الموردون</h1>
                <p class="text-sm text-muted-foreground">اعتماد ومراجعة مقدمي الخدمة</p>
            </div>

            <!-- Status tabs -->
            <div class="flex flex-wrap gap-2">
                <Link
                    v-for="t in tabs"
                    :key="t.key"
                    :href="t.key ? `/admin/providers?status=${t.key}` : '/admin/providers'"
                    class="rounded-full border px-4 py-1.5 text-sm transition-colors"
                    :class="(filter ?? '') === t.key ? 'border-primary bg-primary text-primary-foreground' : 'hover:bg-accent'"
                >
                    {{ t.label }}
                    <span class="mr-1 text-xs opacity-80">({{ counts[t.countKey] }})</span>
                </Link>
            </div>

            <Card>
                <CardContent class="p-0">
                    <div class="overflow-x-auto">
                    <table class="w-full min-w-[700px] text-sm">
                        <thead>
                            <tr class="border-b text-right text-xs text-muted-foreground">
                                <th class="p-3 font-medium">المنشأة</th>
                                <th class="p-3 font-medium">السجل التجاري</th>
                                <th class="p-3 font-medium">القطاع</th>
                                <th class="p-3 font-medium">الجوال</th>
                                <th class="p-3 font-medium">المستندات</th>
                                <th class="p-3 font-medium">الحالة</th>
                                <th class="p-3 font-medium">إجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="p in providers.data" :key="p.id" class="border-b last:border-0 hover:bg-accent/40">
                                <td class="p-3">
                                    <div class="font-medium">{{ p.company_name }}</div>
                                    <div class="text-xs text-muted-foreground" dir="ltr">{{ p.user?.email }}</div>
                                </td>
                                <td class="p-3 text-muted-foreground">{{ p.commercial_register_no ?? '—' }}</td>
                                <td class="p-3 text-muted-foreground">{{ p.main_category?.name ?? '—' }}</td>
                                <td class="p-3 text-muted-foreground" dir="ltr">{{ p.mobile ?? '—' }}</td>
                                <td class="p-3 text-muted-foreground">{{ p.documents_count }}</td>
                                <td class="p-3"><span class="rounded px-2 py-0.5 text-xs" :class="statusClass[p.status]">{{ statusLabels[p.status] }}</span></td>
                                <td class="p-3">
                                    <div class="flex gap-1">
                                        <Button variant="ghost" size="icon" as-child><Link :href="`/admin/providers/${p.id}`"><Eye class="size-4" /></Link></Button>
                                        <Button v-if="p.status !== 'approved'" variant="ghost" size="icon" class="text-emerald-600 hover:text-emerald-700" title="اعتماد" @click="setStatus(p, 'approved')"><Check class="size-4" /></Button>
                                        <Button v-if="p.status !== 'rejected'" variant="ghost" size="icon" class="text-red-600 hover:text-red-700" title="رفض" @click="setStatus(p, 'rejected')"><X class="size-4" /></Button>
                                        <Button variant="ghost" size="icon" class="text-red-700 hover:bg-red-50 hover:text-red-800" title="حذف نهائي" @click="deleteProvider(p)"><Trash2 class="size-4" /></Button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="providers.data.length === 0">
                                <td colspan="7" class="p-8 text-center text-muted-foreground">لا يوجد موردون</td>
                            </tr>
                        </tbody>
                    </table>
                    </div>
                    <Pagination :links="providers.links" :total="providers.total" />
                </CardContent>
            </Card>
        </div>
    </AdminLayout>
</template>
