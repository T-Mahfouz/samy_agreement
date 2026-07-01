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

interface Category {
    id: number;
    parent_id: number | null;
    name: string;
    is_active: boolean;
    sort_order: number;
    parent?: { id: number; name: string } | null;
    children_count: number;
}

const props = defineProps<{
    categories: Paginated<Category>;
    parents: { id: number; name: string }[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'لوحة التحكم', href: '/admin/dashboard' },
    { title: 'التصنيفات', href: '/admin/categories' },
];

const isOpen = ref(false);
const editingId = ref<number | null>(null);

const form = useForm({
    name: '',
    parent_id: '' as string | number,
    is_active: true,
    sort_order: 0,
});

const openCreate = () => {
    editingId.value = null;
    form.reset();
    form.clearErrors();
    isOpen.value = true;
};

const openEdit = (c: Category) => {
    editingId.value = c.id;
    form.name = c.name;
    form.parent_id = c.parent_id ?? '';
    form.is_active = c.is_active;
    form.sort_order = c.sort_order;
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
        form.put(`/admin/categories/${editingId.value}`, options);
    } else {
        form.post('/admin/categories', options);
    }
};

const destroy = (c: Category) => {
    if (confirm(`حذف التصنيف «${c.name}»؟`)) {
        router.delete(`/admin/categories/${c.id}`, { preserveScroll: true });
    }
};
</script>

<template>
    <Head title="التصنيفات" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4 md:p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">التصنيفات</h1>
                    <p class="text-sm text-muted-foreground">القطاعات الرئيسية والأنشطة الفرعية</p>
                </div>
                <Button @click="openCreate"><Plus class="size-4" /> إضافة تصنيف</Button>
            </div>

            <Card>
                <CardContent class="p-0">
                    <div class="overflow-x-auto">
                    <table class="w-full min-w-[600px] text-sm">
                        <thead>
                            <tr class="border-b text-right text-xs text-muted-foreground">
                                <th class="p-3 font-medium">الاسم</th>
                                <th class="p-3 font-medium">القطاع الرئيسي</th>
                                <th class="p-3 font-medium">عدد الفروع</th>
                                <th class="p-3 font-medium">الترتيب</th>
                                <th class="p-3 font-medium">الحالة</th>
                                <th class="p-3 font-medium">إجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="c in categories.data" :key="c.id" class="border-b last:border-0 hover:bg-accent/40">
                                <td class="p-3 font-medium">{{ c.name }}</td>
                                <td class="p-3 text-muted-foreground">{{ c.parent?.name ?? '— (قطاع رئيسي)' }}</td>
                                <td class="p-3 text-muted-foreground">{{ c.children_count }}</td>
                                <td class="p-3 text-muted-foreground">{{ c.sort_order }}</td>
                                <td class="p-3">
                                    <span
                                        class="rounded px-2 py-0.5 text-xs"
                                        :class="c.is_active ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-neutral-200 text-neutral-600 dark:bg-neutral-800 dark:text-neutral-400'"
                                    >{{ c.is_active ? 'مفعّل' : 'معطّل' }}</span>
                                </td>
                                <td class="p-3">
                                    <div class="flex gap-1">
                                        <Button variant="ghost" size="icon" @click="openEdit(c)"><Pencil class="size-4" /></Button>
                                        <Button variant="ghost" size="icon" class="text-red-600 hover:text-red-700" @click="destroy(c)"><Trash2 class="size-4" /></Button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="categories.data.length === 0">
                                <td colspan="6" class="p-8 text-center text-muted-foreground">لا توجد تصنيفات بعد</td>
                            </tr>
                        </tbody>
                    </table>
                    </div>
                    <Pagination :links="categories.links" :total="categories.total" />
                </CardContent>
            </Card>
        </div>

        <Dialog v-model:open="isOpen">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>{{ editingId ? 'تعديل تصنيف' : 'إضافة تصنيف' }}</DialogTitle>
                </DialogHeader>
                <form @submit.prevent="submit" class="space-y-4">
                    <div class="grid gap-2">
                        <Label for="name">اسم التصنيف</Label>
                        <Input id="name" v-model="form.name" placeholder="مثال: الصيانة والتشغيل" />
                        <InputError :message="form.errors.name" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="parent_id">القطاع الرئيسي (اتركه فارغًا لقطاع رئيسي)</Label>
                        <select
                            id="parent_id"
                            v-model="form.parent_id"
                            class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm"
                        >
                            <option value="">— قطاع رئيسي —</option>
                            <option v-for="p in parents" :key="p.id" :value="p.id" :disabled="p.id === editingId">{{ p.name }}</option>
                        </select>
                        <InputError :message="form.errors.parent_id" />
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
