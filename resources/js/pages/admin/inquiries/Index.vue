<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import InputError from '@/components/InputError.vue';
import Pagination from '@/components/admin/Pagination.vue';
import { Label } from '@/components/ui/label';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { type BreadcrumbItem, type Paginated } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { MessageSquareReply } from 'lucide-vue-next';
import { ref } from 'vue';

interface Inquiry {
    id: number;
    question: string;
    answer: string | null;
    answered_at: string | null;
    tender?: { id: number; tender_no: string; name: string } | null;
    provider?: { id: number; company_name: string } | null;
}

defineProps<{
    inquiries: Paginated<Inquiry>;
    filter: string | null;
    counts: Record<string, number>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'لوحة التحكم', href: '/admin/dashboard' },
    { title: 'الاستفسارات', href: '/admin/inquiries' },
];

const tabs = [
    { key: '', label: 'الكل', countKey: 'all' },
    { key: 'unanswered', label: 'بدون رد', countKey: 'unanswered' },
    { key: 'answered', label: 'تم الرد', countKey: 'answered' },
];

const isOpen = ref(false);
const active = ref<Inquiry | null>(null);
const form = useForm({ answer: '' });

const openReply = (i: Inquiry) => {
    active.value = i;
    form.answer = i.answer ?? '';
    form.clearErrors();
    isOpen.value = true;
};

const submit = () => {
    if (!active.value) return;
    form.put(`/admin/inquiries/${active.value.id}`, {
        preserveScroll: true,
        onSuccess: () => { isOpen.value = false; form.reset(); },
    });
};
</script>

<template>
    <Head title="الاستفسارات" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4 md:p-6">
            <div>
                <h1 class="text-2xl font-bold">الاستفسارات</h1>
                <p class="text-sm text-muted-foreground">أسئلة الموردين على المنافسات</p>
            </div>

            <div class="flex flex-wrap gap-2">
                <Link
                    v-for="t in tabs"
                    :key="t.key"
                    :href="t.key ? `/admin/inquiries?filter=${t.key}` : '/admin/inquiries'"
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
                                <th class="p-3 font-medium">المنافسة</th>
                                <th class="p-3 font-medium">المورد</th>
                                <th class="p-3 font-medium">السؤال</th>
                                <th class="p-3 font-medium">الحالة</th>
                                <th class="p-3 font-medium">إجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="i in inquiries.data" :key="i.id" class="border-b last:border-0 hover:bg-accent/40">
                                <td class="p-3">{{ i.tender?.tender_no ?? '—' }}</td>
                                <td class="p-3 text-muted-foreground">{{ i.provider?.company_name ?? '—' }}</td>
                                <td class="p-3"><span class="line-clamp-1 max-w-md">{{ i.question }}</span></td>
                                <td class="p-3">
                                    <span
                                        class="rounded px-2 py-0.5 text-xs"
                                        :class="i.answered_at ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300'"
                                    >{{ i.answered_at ? 'تم الرد' : 'بدون رد' }}</span>
                                </td>
                                <td class="p-3">
                                    <Button variant="ghost" size="icon" @click="openReply(i)" title="رد"><MessageSquareReply class="size-4" /></Button>
                                </td>
                            </tr>
                            <tr v-if="inquiries.data.length === 0">
                                <td colspan="5" class="p-8 text-center text-muted-foreground">لا توجد استفسارات</td>
                            </tr>
                        </tbody>
                    </table>
                    </div>
                    <Pagination :links="inquiries.links" :total="inquiries.total" />
                </CardContent>
            </Card>
        </div>

        <Dialog v-model:open="isOpen">
            <DialogContent v-if="active">
                <DialogHeader><DialogTitle>الرد على الاستفسار</DialogTitle></DialogHeader>
                <div class="space-y-4">
                    <div class="rounded-lg border bg-muted/30 p-3 text-sm">
                        <div class="mb-1 text-xs text-muted-foreground">السؤال — {{ active.provider?.company_name }}</div>
                        <p class="leading-relaxed">{{ active.question }}</p>
                    </div>
                    <form @submit.prevent="submit" class="space-y-3">
                        <div class="grid gap-2">
                            <Label for="answer">الرد</Label>
                            <textarea
                                id="answer"
                                v-model="form.answer"
                                rows="5"
                                class="flex w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm"
                                placeholder="اكتب الرد"
                            ></textarea>
                            <InputError :message="form.errors.answer" />
                        </div>
                        <DialogFooter>
                            <Button type="button" variant="outline" @click="isOpen = false">إلغاء</Button>
                            <Button type="submit" :disabled="form.processing">حفظ الرد</Button>
                        </DialogFooter>
                    </form>
                </div>
            </DialogContent>
        </Dialog>
    </AdminLayout>
</template>
