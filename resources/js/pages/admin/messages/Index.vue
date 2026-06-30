<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import Pagination from '@/components/admin/Pagination.vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { type BreadcrumbItem, type Paginated } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { Mail, Phone, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';

interface Message {
    id: number;
    full_name: string;
    mobile: string | null;
    email: string | null;
    message: string;
    status: 'new' | 'read' | 'replied';
    created_at: string;
}

defineProps<{ messages: Paginated<Message>; newCount: number }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'لوحة التحكم', href: '/admin/dashboard' },
    { title: 'رسائل التواصل', href: '/admin/messages' },
];

const statusLabels: Record<string, string> = { new: 'جديدة', read: 'مقروءة', replied: 'تم الرد' };
const statusClass: Record<string, string> = {
    new: 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
    read: 'bg-neutral-200 text-neutral-600 dark:bg-neutral-800 dark:text-neutral-400',
    replied: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300',
};

const isOpen = ref(false);
const active = ref<Message | null>(null);

const open = (m: Message) => {
    active.value = m;
    isOpen.value = true;
    if (m.status === 'new') {
        router.put(`/admin/messages/${m.id}`, { status: 'read' }, { preserveScroll: true, preserveState: true });
    }
};

const setStatus = (status: string) => {
    if (!active.value) return;
    router.put(`/admin/messages/${active.value.id}`, { status }, {
        preserveScroll: true,
        onSuccess: () => { isOpen.value = false; },
    });
};

const destroy = (m: Message) => {
    if (confirm(`حذف رسالة «${m.full_name}»؟`)) {
        router.delete(`/admin/messages/${m.id}`, {
            preserveScroll: true,
            onSuccess: () => { if (active.value?.id === m.id) isOpen.value = false; },
        });
    }
};

const formatDate = (s: string) => new Date(s).toLocaleString('ar-EG', { dateStyle: 'medium', timeStyle: 'short' });
</script>

<template>
    <Head title="رسائل التواصل" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4 md:p-6">
            <div>
                <h1 class="text-2xl font-bold">رسائل التواصل</h1>
                <p class="text-sm text-muted-foreground">
                    رسائل نموذج «تواصل معنا»
                    <span v-if="newCount" class="mr-1 rounded bg-blue-100 px-2 py-0.5 text-xs text-blue-700 dark:bg-blue-900/40 dark:text-blue-300">{{ newCount }} جديدة</span>
                </p>
            </div>

            <Card>
                <CardContent class="p-0">
                    <div class="overflow-x-auto">
                    <table class="w-full min-w-[640px] text-sm">
                        <thead>
                            <tr class="border-b text-right text-xs text-muted-foreground">
                                <th class="p-3 font-medium">المرسِل</th>
                                <th class="p-3 font-medium">التواصل</th>
                                <th class="p-3 font-medium">الرسالة</th>
                                <th class="p-3 font-medium">التاريخ</th>
                                <th class="p-3 font-medium">الحالة</th>
                                <th class="p-3 font-medium">إجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="m in messages.data"
                                :key="m.id"
                                class="cursor-pointer border-b last:border-0 hover:bg-accent/40"
                                :class="{ 'font-medium': m.status === 'new' }"
                                @click="open(m)"
                            >
                                <td class="p-3">{{ m.full_name }}</td>
                                <td class="p-3 text-xs text-muted-foreground">
                                    <div v-if="m.email" dir="ltr" class="text-right">{{ m.email }}</div>
                                    <div v-if="m.mobile" dir="ltr" class="text-right">{{ m.mobile }}</div>
                                </td>
                                <td class="p-3 text-muted-foreground"><span class="line-clamp-1 block max-w-[200px]">{{ m.message }}</span></td>
                                <td class="p-3 text-xs text-muted-foreground">{{ formatDate(m.created_at) }}</td>
                                <td class="p-3"><span class="rounded px-2 py-0.5 text-xs" :class="statusClass[m.status]">{{ statusLabels[m.status] }}</span></td>
                                <td class="p-3" @click.stop>
                                    <Button variant="ghost" size="icon" class="text-red-600 hover:text-red-700" @click="destroy(m)"><Trash2 class="size-4" /></Button>
                                </td>
                            </tr>
                            <tr v-if="messages.data.length === 0">
                                <td colspan="6" class="p-8 text-center text-muted-foreground">لا توجد رسائل</td>
                            </tr>
                        </tbody>
                    </table>
                    </div>
                    <Pagination :links="messages.links" :total="messages.total" />
                </CardContent>
            </Card>
        </div>

        <Dialog v-model:open="isOpen">
            <DialogContent v-if="active">
                <DialogHeader><DialogTitle>رسالة من {{ active.full_name }}</DialogTitle></DialogHeader>
                <div class="space-y-3 text-sm">
                    <div class="flex flex-wrap gap-4 text-muted-foreground">
                        <span v-if="active.email" class="flex items-center gap-1"><Mail class="size-4" /><span dir="ltr">{{ active.email }}</span></span>
                        <span v-if="active.mobile" class="flex items-center gap-1"><Phone class="size-4" /><span dir="ltr">{{ active.mobile }}</span></span>
                    </div>
                    <div class="rounded-lg border bg-muted/30 p-3 leading-relaxed whitespace-pre-wrap">{{ active.message }}</div>
                    <div class="text-xs text-muted-foreground">{{ formatDate(active.created_at) }}</div>
                </div>
                <DialogFooter class="flex-wrap gap-2">
                    <Button variant="outline" class="text-red-600" @click="destroy(active)"><Trash2 class="size-4" /> حذف</Button>
                    <Button variant="outline" @click="setStatus('read')">وضع كمقروءة</Button>
                    <Button @click="setStatus('replied')">تم الرد</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AdminLayout>
</template>
