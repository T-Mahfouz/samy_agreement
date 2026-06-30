<script setup lang="ts">
import Pagination from '@/components/admin/Pagination.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { type BreadcrumbItem, type Paginated } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Ban, Check, Eye } from 'lucide-vue-next';

interface Client {
    id: number;
    company_name: string;
    mobile: string | null;
    bank_name: string | null;
    tenders_count: number;
    user?: { id: number; name: string; email: string; status: string } | null;
}

defineProps<{ clients: Paginated<Client> }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'لوحة التحكم', href: '/admin/dashboard' },
    { title: 'العملاء', href: '/admin/clients' },
];

const statusLabels: Record<string, string> = { active: 'نشط', suspended: 'معلّق', pending: 'معلّق' };
const statusClass: Record<string, string> = {
    active: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300',
    suspended: 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
    pending: 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
};

const setStatus = (c: Client, status: string) => {
    const verb = status === 'active' ? 'تفعيل' : 'تعليق';
    if (confirm(`${verb} حساب «${c.company_name}»؟`)) {
        router.put(`/admin/clients/${c.id}`, { status }, { preserveScroll: true });
    }
};
</script>

<template>
    <Head title="العملاء" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4 md:p-6">
            <div>
                <h1 class="text-2xl font-bold">العملاء</h1>
                <p class="text-sm text-muted-foreground">الجهات طالبة الخدمة (المستفيدون)</p>
            </div>

            <Card>
                <CardContent class="p-0">
                    <div class="overflow-x-auto">
                    <table class="w-full min-w-[640px] text-sm">
                        <thead>
                            <tr class="border-b text-right text-xs text-muted-foreground">
                                <th class="p-3 font-medium">المنشأة</th>
                                <th class="p-3 font-medium">البريد</th>
                                <th class="p-3 font-medium">الجوال</th>
                                <th class="p-3 font-medium">المنافسات</th>
                                <th class="p-3 font-medium">الحالة</th>
                                <th class="p-3 font-medium">إجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="c in clients.data" :key="c.id" class="border-b last:border-0 hover:bg-accent/40">
                                <td class="p-3 font-medium">{{ c.company_name }}</td>
                                <td class="p-3 text-muted-foreground" dir="ltr">{{ c.user?.email }}</td>
                                <td class="p-3 text-muted-foreground" dir="ltr">{{ c.mobile ?? '—' }}</td>
                                <td class="p-3 text-muted-foreground">{{ c.tenders_count }}</td>
                                <td class="p-3"><span class="rounded px-2 py-0.5 text-xs" :class="statusClass[c.user?.status ?? 'pending']">{{ statusLabels[c.user?.status ?? 'pending'] }}</span></td>
                                <td class="p-3">
                                    <div class="flex gap-1">
                                        <Button variant="ghost" size="icon" as-child><Link :href="`/admin/clients/${c.id}`"><Eye class="size-4" /></Link></Button>
                                        <Button v-if="c.user?.status !== 'active'" variant="ghost" size="icon" class="text-emerald-600 hover:text-emerald-700" title="تفعيل" @click="setStatus(c, 'active')"><Check class="size-4" /></Button>
                                        <Button v-else variant="ghost" size="icon" class="text-red-600 hover:text-red-700" title="تعليق" @click="setStatus(c, 'suspended')"><Ban class="size-4" /></Button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="clients.data.length === 0">
                                <td colspan="6" class="p-8 text-center text-muted-foreground">لا يوجد عملاء</td>
                            </tr>
                        </tbody>
                    </table>
                    </div>
                    <Pagination :links="clients.links" :total="clients.total" />
                </CardContent>
            </Card>
        </div>
    </AdminLayout>
</template>
