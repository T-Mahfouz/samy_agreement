<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowRight } from 'lucide-vue-next';

interface Tender {
    id: number;
    tender_no: string;
    name: string;
    status: string;
}
interface Client {
    id: number;
    company_name: string;
    mobile: string | null;
    bank_name: string | null;
    bank_beneficiary_name: string | null;
    bank_iban: string | null;
    user?: { id: number; name: string; username: string | null; email: string; phone: string | null; status: string } | null;
    tenders: Tender[];
}

const props = defineProps<{ client: Client }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'لوحة التحكم', href: '/admin/dashboard' },
    { title: 'العملاء', href: '/admin/clients' },
    { title: props.client.company_name, href: `/admin/clients/${props.client.id}` },
];

const tenderStatusLabels: Record<string, string> = { active: 'نشطة', examination: 'فحص العروض', awarding: 'مرحلة الترسية', awarded: 'تمت الترسية', cancelled: 'ملغاة' };

const info = [
    { label: 'الجوال', value: props.client.mobile },
    { label: 'اسم البنك', value: props.client.bank_name },
    { label: 'اسم المستفيد', value: props.client.bank_beneficiary_name },
    { label: 'رقم الآيبان', value: props.client.bank_iban },
];
</script>

<template>
    <Head :title="client.company_name" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4 md:p-6">
            <div class="flex items-center gap-3">
                <Button variant="ghost" size="icon" as-child><a href="/admin/clients"><ArrowRight class="size-5" /></a></Button>
                <h1 class="text-2xl font-bold">{{ client.company_name }}</h1>
            </div>

            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                <Card>
                    <CardHeader><CardTitle class="text-base">بيانات المنشأة والحساب البنكي</CardTitle></CardHeader>
                    <CardContent>
                        <dl class="space-y-2 text-sm">
                            <div v-for="row in info" :key="row.label" class="flex justify-between border-b pb-2">
                                <dt class="text-muted-foreground">{{ row.label }}</dt>
                                <dd class="font-medium" :dir="row.label === 'رقم الآيبان' || row.label === 'الجوال' ? 'ltr' : undefined">{{ row.value ?? '—' }}</dd>
                            </div>
                        </dl>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader><CardTitle class="text-base">حساب المستخدم</CardTitle></CardHeader>
                    <CardContent class="space-y-2 text-sm">
                        <div class="flex justify-between border-b pb-2"><span class="text-muted-foreground">الاسم</span><span class="font-medium">{{ client.user?.name }}</span></div>
                        <div class="flex justify-between border-b pb-2"><span class="text-muted-foreground">البريد</span><span class="font-medium" dir="ltr">{{ client.user?.email }}</span></div>
                        <div class="flex justify-between"><span class="text-muted-foreground">حالة الحساب</span><span class="font-medium">{{ client.user?.status }}</span></div>
                    </CardContent>
                </Card>
            </div>

            <Card>
                <CardHeader><CardTitle class="text-base">منافسات العميل ({{ client.tenders.length }})</CardTitle></CardHeader>
                <CardContent class="p-0">
                    <table v-if="client.tenders.length" class="w-full text-sm">
                        <thead>
                            <tr class="border-b text-right text-xs text-muted-foreground">
                                <th class="p-3 font-medium">رقم المنافسة</th>
                                <th class="p-3 font-medium">الاسم</th>
                                <th class="p-3 font-medium">الحالة</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="t in client.tenders" :key="t.id" class="border-b last:border-0 hover:bg-accent/40">
                                <td class="p-3"><Link :href="`/admin/tenders/${t.id}`" class="text-primary hover:underline">{{ t.tender_no }}</Link></td>
                                <td class="p-3">{{ t.name }}</td>
                                <td class="p-3 text-muted-foreground">{{ tenderStatusLabels[t.status] ?? t.status }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <p v-else class="p-6 text-center text-sm text-muted-foreground">لا توجد منافسات لهذا العميل</p>
                </CardContent>
            </Card>
        </div>
    </AdminLayout>
</template>
