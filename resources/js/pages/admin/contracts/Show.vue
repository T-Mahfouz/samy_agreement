<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { ArrowRight, CheckCircle2, XCircle } from 'lucide-vue-next';

interface Contract {
    id: number;
    content: string | null;
    contract_value: string | null;
    contract_duration_months: number | null;
    documentation_date: string | null;
    client_signed_at: string | null;
    client_signed_ip: string | null;
    provider_signed_at: string | null;
    provider_signed_ip: string | null;
    status: string;
    tender?: { id: number; tender_no: string; name: string; contract_duration_months: number | null } | null;
    client?: { id: number; company_name: string } | null;
    provider?: { id: number; company_name: string } | null;
    offer?: { id: number; financial_value: string | null } | null;
}

const props = defineProps<{ contract: Contract }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'لوحة التحكم', href: '/admin/dashboard' },
    { title: 'العقود', href: '/admin/contracts' },
    { title: `عقد #${props.contract.id}`, href: `/admin/contracts/${props.contract.id}` },
];

const statusLabels: Record<string, string> = { awaiting_signature: 'بانتظار التوقيع', active: 'ساري', completed: 'مكتمل', cancelled: 'ملغي' };
const statusClass: Record<string, string> = {
    awaiting_signature: 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
    active: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300',
    completed: 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300',
    cancelled: 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
};

const setStatus = (status: string) => {
    if (confirm(`تغيير حالة العقد إلى «${statusLabels[status]}»؟`)) {
        router.put(`/admin/contracts/${props.contract.id}`, { status }, { preserveScroll: true });
    }
};

const fmt = (s: string | null) => (s ? new Date(s).toLocaleString('ar-EG', { dateStyle: 'medium', timeStyle: 'short' }) : null);
</script>

<template>
    <Head :title="`عقد #${contract.id}`" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4 md:p-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <Button variant="ghost" size="icon" as-child><a href="/admin/contracts"><ArrowRight class="size-5" /></a></Button>
                    <div>
                        <h1 class="text-xl font-bold">عقد إلكتروني #{{ contract.id }}</h1>
                        <div class="flex items-center gap-2 text-sm text-muted-foreground">
                            <span>{{ contract.tender?.tender_no }}</span>
                            <span class="rounded px-2 py-0.5 text-xs" :class="statusClass[contract.status]">{{ statusLabels[contract.status] }}</span>
                        </div>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2">
                    <Button v-if="contract.status === 'active'" @click="setStatus('completed')">إنهاء (مكتمل)</Button>
                    <Button v-if="contract.status !== 'cancelled' && contract.status !== 'completed'" variant="outline" class="text-red-600" @click="setStatus('cancelled')">إلغاء العقد</Button>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
                <Card class="lg:col-span-1">
                    <CardHeader><CardTitle class="text-base">بيانات العقد</CardTitle></CardHeader>
                    <CardContent class="space-y-2 text-sm">
                        <div class="flex justify-between border-b pb-2"><span class="text-muted-foreground">المنافسة</span><span class="font-medium">{{ contract.tender?.name }}</span></div>
                        <div class="flex justify-between border-b pb-2"><span class="text-muted-foreground">العميل</span><span class="font-medium">{{ contract.client?.company_name }}</span></div>
                        <div class="flex justify-between border-b pb-2"><span class="text-muted-foreground">المورد</span><span class="font-medium">{{ contract.provider?.company_name }}</span></div>
                        <div class="flex justify-between border-b pb-2"><span class="text-muted-foreground">قيمة العقد</span><span class="font-medium">{{ contract.contract_value ? `${Number(contract.contract_value).toLocaleString('ar-EG')} ر.س` : '—' }}</span></div>
                        <div class="flex justify-between border-b pb-2"><span class="text-muted-foreground">مدة العقد</span><span class="font-medium">{{ contract.contract_duration_months ?? contract.tender?.contract_duration_months ?? '—' }} شهر</span></div>
                        <div class="flex justify-between"><span class="text-muted-foreground">تاريخ التوثيق</span><span class="font-medium">{{ contract.documentation_date ?? '—' }}</span></div>
                    </CardContent>
                </Card>

                <Card class="lg:col-span-2">
                    <CardHeader><CardTitle class="text-base">التوقيع الإلكتروني</CardTitle></CardHeader>
                    <CardContent class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="rounded-lg border p-4">
                            <div class="mb-2 flex items-center gap-2 font-medium">
                                <CheckCircle2 v-if="contract.client_signed_at" class="size-5 text-emerald-600" />
                                <XCircle v-else class="size-5 text-muted-foreground" />
                                العميل
                            </div>
                            <div class="space-y-1 text-sm text-muted-foreground">
                                <div>{{ contract.client?.company_name }}</div>
                                <div v-if="contract.client_signed_at">وقّع في: {{ fmt(contract.client_signed_at) }}</div>
                                <div v-if="contract.client_signed_ip" dir="ltr" class="text-right">IP: {{ contract.client_signed_ip }}</div>
                                <div v-if="!contract.client_signed_at" class="text-amber-600">لم يوقّع بعد</div>
                            </div>
                        </div>
                        <div class="rounded-lg border p-4">
                            <div class="mb-2 flex items-center gap-2 font-medium">
                                <CheckCircle2 v-if="contract.provider_signed_at" class="size-5 text-emerald-600" />
                                <XCircle v-else class="size-5 text-muted-foreground" />
                                المورد
                            </div>
                            <div class="space-y-1 text-sm text-muted-foreground">
                                <div>{{ contract.provider?.company_name }}</div>
                                <div v-if="contract.provider_signed_at">وقّع في: {{ fmt(contract.provider_signed_at) }}</div>
                                <div v-if="contract.provider_signed_ip" dir="ltr" class="text-right">IP: {{ contract.provider_signed_ip }}</div>
                                <div v-if="!contract.provider_signed_at" class="text-amber-600">لم يوقّع بعد</div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <Card v-if="contract.content">
                <CardHeader><CardTitle class="text-base">نص العقد</CardTitle></CardHeader>
                <CardContent>
                    <p class="whitespace-pre-wrap text-sm leading-relaxed">{{ contract.content }}</p>
                </CardContent>
            </Card>
        </div>
    </AdminLayout>
</template>
