<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Building2, FileText, Receipt, ScrollText, Users, FileSignature, Mail } from 'lucide-vue-next';

interface TenderRow {
    id: number;
    tender_no: string;
    name: string;
    status: string;
    client?: { id: number; company_name: string } | null;
    created_at: string;
}
interface ProviderRow {
    id: number;
    company_name: string;
    commercial_register_no: string | null;
    created_at: string;
}
interface PaymentRow {
    id: number;
    type: string;
    amount: string;
    provider?: { id: number; company_name: string } | null;
    tender?: { id: number; tender_no: string; name: string } | null;
    created_at: string;
}

const props = defineProps<{
    stats: {
        tenders: { total: number; active: number; examination: number; awarding: number; awarded: number; cancelled: number };
        offers: number;
        clients: number;
        providers: { total: number; pending: number };
        contracts: { total: number; awaiting_signature: number; active: number };
        payments: { pending: number; brochure_pending: number; commission_pending: number };
        messages_new: number;
    };
    recentTenders: TenderRow[];
    pendingProviders: ProviderRow[];
    pendingPayments: PaymentRow[];
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'لوحة التحكم', href: '/admin/dashboard' }];

const tenderStatusLabels: Record<string, string> = {
    active: 'نشطة',
    examination: 'فحص العروض',
    awarding: 'مرحلة الترسية',
    awarded: 'تمت الترسية',
    cancelled: 'ملغاة',
};
const tenderStatusClass: Record<string, string> = {
    active: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300',
    examination: 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
    awarding: 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
    awarded: 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300',
    cancelled: 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
};
const paymentTypeLabels: Record<string, string> = {
    brochure_fee: 'كراسة الشروط',
    commission: 'عمولة المنصة',
};

const cards = [
    { label: 'المنافسات', value: props.stats.tenders.total, sub: `${props.stats.tenders.active} نشطة`, icon: FileText, href: '/admin/tenders', color: 'emerald' },
    { label: 'العروض المقدمة', value: props.stats.offers, sub: 'إجمالي', icon: ScrollText, href: '/admin/offers', color: 'blue' },
    { label: 'الموردون', value: props.stats.providers.total, sub: `${props.stats.providers.pending} بانتظار الاعتماد`, icon: Building2, href: '/admin/providers', color: 'orange' },
    { label: 'العملاء', value: props.stats.clients, sub: 'إجمالي', icon: Users, href: '/admin/clients', color: 'violet' },
    { label: 'العقود', value: props.stats.contracts.total, sub: `${props.stats.contracts.awaiting_signature} بانتظار التوقيع`, icon: FileSignature, href: '/admin/contracts', color: 'indigo' },
    { label: 'مدفوعات معلّقة', value: props.stats.payments.pending, sub: `${props.stats.payments.commission_pending} عمولات`, icon: Receipt, href: '/admin/payments', color: 'rose' },
    { label: 'رسائل جديدة', value: props.stats.messages_new, sub: 'تواصل معنا', icon: Mail, href: '/admin/messages', color: 'teal' },
];

const chipClass: Record<string, string> = {
    emerald: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300',
    blue: 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
    orange: 'bg-orange-100 text-orange-700 dark:bg-orange-900/40 dark:text-orange-300',
    violet: 'bg-violet-100 text-violet-700 dark:bg-violet-900/40 dark:text-violet-300',
    indigo: 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300',
    rose: 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300',
    teal: 'bg-teal-100 text-teal-700 dark:bg-teal-900/40 dark:text-teal-300',
};
</script>

<template>
    <Head title="لوحة تحكم الأدمن" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
            <div>
                <h1 class="text-2xl font-bold">لوحة تحكم منصة اتفاق</h1>
                <p class="text-sm text-muted-foreground">نظرة عامة على نشاط المنصة</p>
            </div>

            <!-- Stat cards -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <Link v-for="card in cards" :key="card.label" :href="card.href">
                    <Card class="group h-full overflow-hidden transition-all duration-200 hover:-translate-y-1 hover:shadow-lg hover:shadow-primary/5">
                        <CardContent class="flex items-center gap-4 p-5">
                            <div class="flex size-12 shrink-0 items-center justify-center rounded-xl transition-transform group-hover:scale-110" :class="chipClass[card.color]">
                                <component :is="card.icon" class="size-6" />
                            </div>
                            <div class="min-w-0">
                                <div class="text-2xl font-bold leading-tight">{{ card.value.toLocaleString('ar-EG') }}</div>
                                <div class="truncate text-sm font-medium">{{ card.label }}</div>
                                <p class="mt-0.5 truncate text-xs text-muted-foreground">{{ card.sub }}</p>
                            </div>
                        </CardContent>
                    </Card>
                </Link>
            </div>

            <!-- Tender status breakdown -->
            <Card>
                <CardHeader><CardTitle class="text-base">المنافسات حسب الحالة</CardTitle></CardHeader>
                <CardContent>
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-5">
                        <div v-for="(label, key) in tenderStatusLabels" :key="key" class="rounded-lg border p-3 text-center">
                            <div class="text-2xl font-bold">{{ (props.stats.tenders as any)[key] ?? 0 }}</div>
                            <span class="mt-1 inline-block rounded px-2 py-0.5 text-xs" :class="tenderStatusClass[key]">{{ label }}</span>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                <!-- Recent tenders -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between">
                        <CardTitle class="text-base">أحدث المنافسات</CardTitle>
                        <Link href="/admin/tenders" class="text-xs text-primary hover:underline">عرض الكل</Link>
                    </CardHeader>
                    <CardContent>
                        <p v-if="recentTenders.length === 0" class="py-6 text-center text-sm text-muted-foreground">لا توجد منافسات بعد</p>
                        <div v-else class="overflow-x-auto">
                        <table class="w-full min-w-[480px] text-sm">
                            <thead>
                                <tr class="border-b text-right text-xs text-muted-foreground">
                                    <th class="pb-2 font-medium">رقم المنافسة</th>
                                    <th class="pb-2 font-medium">الاسم</th>
                                    <th class="pb-2 font-medium">الجهة</th>
                                    <th class="pb-2 font-medium">الحالة</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="t in recentTenders" :key="t.id" class="border-b last:border-0">
                                    <td class="py-2">{{ t.tender_no }}</td>
                                    <td class="py-2"><span class="line-clamp-1 max-w-[180px]">{{ t.name }}</span></td>
                                    <td class="py-2 text-muted-foreground">{{ t.client?.company_name ?? '—' }}</td>
                                    <td class="py-2">
                                        <span class="rounded px-2 py-0.5 text-xs" :class="tenderStatusClass[t.status]">{{ tenderStatusLabels[t.status] ?? t.status }}</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        </div>
                    </CardContent>
                </Card>

                <!-- Pending providers -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between">
                        <CardTitle class="text-base">موردون بانتظار الاعتماد</CardTitle>
                        <Link href="/admin/providers" class="text-xs text-primary hover:underline">عرض الكل</Link>
                    </CardHeader>
                    <CardContent>
                        <p v-if="pendingProviders.length === 0" class="py-6 text-center text-sm text-muted-foreground">لا يوجد موردون بانتظار الاعتماد</p>
                        <ul v-else class="divide-y">
                            <li v-for="p in pendingProviders" :key="p.id" class="flex items-center justify-between py-2 text-sm">
                                <div>
                                    <div class="font-medium">{{ p.company_name }}</div>
                                    <div class="text-xs text-muted-foreground">سجل تجاري: {{ p.commercial_register_no ?? '—' }}</div>
                                </div>
                                <span class="rounded bg-amber-100 px-2 py-0.5 text-xs text-amber-700 dark:bg-amber-900/40 dark:text-amber-300">معلّق</span>
                            </li>
                        </ul>
                    </CardContent>
                </Card>
            </div>

            <!-- Pending payments -->
            <Card>
                <CardHeader class="flex flex-row items-center justify-between">
                    <CardTitle class="text-base">مدفوعات بانتظار المراجعة</CardTitle>
                    <Link href="/admin/payments" class="text-xs text-primary hover:underline">عرض الكل</Link>
                </CardHeader>
                <CardContent>
                    <p v-if="pendingPayments.length === 0" class="py-6 text-center text-sm text-muted-foreground">لا توجد مدفوعات معلّقة</p>
                    <div v-else class="overflow-x-auto">
                    <table class="w-full min-w-[480px] text-sm">
                        <thead>
                            <tr class="border-b text-right text-xs text-muted-foreground">
                                <th class="pb-2 font-medium">النوع</th>
                                <th class="pb-2 font-medium">المورد</th>
                                <th class="pb-2 font-medium">المنافسة</th>
                                <th class="pb-2 font-medium">القيمة</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="pay in pendingPayments" :key="pay.id" class="border-b last:border-0">
                                <td class="py-2">{{ paymentTypeLabels[pay.type] ?? pay.type }}</td>
                                <td class="py-2 text-muted-foreground">{{ pay.provider?.company_name ?? '—' }}</td>
                                <td class="py-2 text-muted-foreground">{{ pay.tender?.tender_no ?? '—' }}</td>
                                <td class="py-2 font-medium">{{ Number(pay.amount).toLocaleString('ar-EG') }} ر.س</td>
                            </tr>
                        </tbody>
                    </table>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AdminLayout>
</template>
