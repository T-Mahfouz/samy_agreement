<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';
import Pagination from '@/components/admin/Pagination.vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { type BreadcrumbItem, type Paginated } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Pencil, Plus, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';

interface Faq {
    id: number;
    question: string;
    answer: string;
    sort_order: number;
    is_active: boolean;
}

defineProps<{ faqs: Paginated<Faq> }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'لوحة التحكم', href: '/admin/dashboard' },
    { title: 'الأسئلة الشائعة', href: '/admin/faqs' },
];

const isOpen = ref(false);
const editingId = ref<number | null>(null);

const form = useForm({
    question: '',
    answer: '',
    sort_order: 0,
    is_active: true,
});

const openCreate = () => {
    editingId.value = null;
    form.reset();
    form.clearErrors();
    isOpen.value = true;
};

const openEdit = (f: Faq) => {
    editingId.value = f.id;
    form.question = f.question;
    form.answer = f.answer;
    form.sort_order = f.sort_order;
    form.is_active = f.is_active;
    form.clearErrors();
    isOpen.value = true;
};

const submit = () => {
    const options = {
        preserveScroll: true,
        onSuccess: () => {
            isOpen.value = false;
            form.reset();
        },
    };
    if (editingId.value) {
        form.put(`/admin/faqs/${editingId.value}`, options);
    } else {
        form.post('/admin/faqs', options);
    }
};

const destroy = (f: Faq) => {
    if (confirm('حذف هذا السؤال؟')) {
        router.delete(`/admin/faqs/${f.id}`, { preserveScroll: true });
    }
};
</script>

<template>
    <Head title="الأسئلة الشائعة" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4 md:p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">الأسئلة الشائعة</h1>
                    <p class="text-sm text-muted-foreground">إدارة أسئلة وأجوبة المنصة</p>
                </div>
                <Button @click="openCreate"><Plus class="size-4" /> إضافة سؤال</Button>
            </div>

            <Card>
                <CardContent class="p-0">
                    <div class="overflow-x-auto">
                    <table class="w-full min-w-[640px] text-sm">
                        <thead>
                            <tr class="border-b text-right text-xs text-muted-foreground">
                                <th class="p-3 font-medium">السؤال</th>
                                <th class="p-3 font-medium">الترتيب</th>
                                <th class="p-3 font-medium">الحالة</th>
                                <th class="p-3 font-medium">إجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="f in faqs.data" :key="f.id" class="border-b last:border-0 hover:bg-accent/40">
                                <td class="p-3">
                                    <div class="font-medium">{{ f.question }}</div>
                                    <div class="line-clamp-1 max-w-xl text-xs text-muted-foreground">{{ f.answer }}</div>
                                </td>
                                <td class="p-3 text-muted-foreground">{{ f.sort_order }}</td>
                                <td class="p-3">
                                    <span
                                        class="rounded px-2 py-0.5 text-xs"
                                        :class="f.is_active ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-neutral-200 text-neutral-600 dark:bg-neutral-800 dark:text-neutral-400'"
                                    >{{ f.is_active ? 'مفعّل' : 'معطّل' }}</span>
                                </td>
                                <td class="p-3">
                                    <div class="flex gap-1">
                                        <Button variant="ghost" size="icon" @click="openEdit(f)"><Pencil class="size-4" /></Button>
                                        <Button variant="ghost" size="icon" class="text-red-600 hover:text-red-700" @click="destroy(f)"><Trash2 class="size-4" /></Button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="faqs.data.length === 0">
                                <td colspan="4" class="p-8 text-center text-muted-foreground">لا توجد أسئلة بعد</td>
                            </tr>
                        </tbody>
                    </table>
                    </div>
                    <Pagination :links="faqs.links" :total="faqs.total" />
                </CardContent>
            </Card>
        </div>

        <Dialog v-model:open="isOpen">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>{{ editingId ? 'تعديل سؤال' : 'إضافة سؤال' }}</DialogTitle>
                </DialogHeader>
                <form @submit.prevent="submit" class="space-y-4">
                    <div class="grid gap-2">
                        <Label for="question">السؤال</Label>
                        <Input id="question" v-model="form.question" placeholder="اكتب السؤال" />
                        <InputError :message="form.errors.question" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="answer">الإجابة</Label>
                        <textarea
                            id="answer"
                            v-model="form.answer"
                            rows="5"
                            class="flex w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm"
                            placeholder="اكتب الإجابة"
                        ></textarea>
                        <InputError :message="form.errors.answer" />
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="grid gap-2">
                            <Label for="sort_order">الترتيب</Label>
                            <Input id="sort_order" type="number" min="0" v-model="form.sort_order" />
                            <InputError :message="form.errors.sort_order" />
                        </div>
                        <div class="flex items-end gap-2">
                            <input id="is_active" type="checkbox" v-model="form.is_active" class="size-4" />
                            <Label for="is_active">مفعّل</Label>
                        </div>
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="outline" @click="isOpen = false">إلغاء</Button>
                        <Button type="submit" :disabled="form.processing">حفظ</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </AdminLayout>
</template>
