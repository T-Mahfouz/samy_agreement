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

interface ContentPage {
    id: number;
    slug: string;
    section_key: string | null;
    title: string | null;
    body: string | null;
    updated_at: string;
}

defineProps<{ pages: Paginated<ContentPage> }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'لوحة التحكم', href: '/admin/dashboard' },
    { title: 'محتوى الصفحات', href: '/admin/content' },
];

const slugLabels: Record<string, string> = {
    about: 'عن المنصة',
    'terms-client': 'شروط العميل',
    'terms-provider': 'شروط المورّد',
    privacy: 'سياسة الخصوصية',
};

const isOpen = ref(false);
const editingId = ref<number | null>(null);

const form = useForm({
    slug: '',
    section_key: '',
    title: '',
    body: '',
});

const openCreate = () => {
    editingId.value = null;
    form.reset();
    form.clearErrors();
    isOpen.value = true;
};

const openEdit = (p: ContentPage) => {
    editingId.value = p.id;
    form.slug = p.slug;
    form.section_key = p.section_key ?? '';
    form.title = p.title ?? '';
    form.body = p.body ?? '';
    form.clearErrors();
    isOpen.value = true;
};

const submit = () => {
    const options = {
        preserveScroll: true,
        onSuccess: () => { isOpen.value = false; form.reset(); },
    };
    if (editingId.value) form.put(`/admin/content/${editingId.value}`, options);
    else form.post('/admin/content', options);
};

const destroy = (p: ContentPage) => {
    if (confirm('حذف هذا المحتوى؟')) router.delete(`/admin/content/${p.id}`, { preserveScroll: true });
};
</script>

<template>
    <Head title="محتوى الصفحات" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4 md:p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">محتوى الصفحات</h1>
                    <p class="text-sm text-muted-foreground">النصوص القابلة للتعديل (عن المنصة، الشروط، الخصوصية)</p>
                </div>
                <Button @click="openCreate"><Plus class="size-4" /> إضافة محتوى</Button>
            </div>

            <Card>
                <CardContent class="p-0">
                    <div class="overflow-x-auto">
                    <table class="w-full min-w-[640px] text-sm">
                        <thead>
                            <tr class="border-b text-right text-xs text-muted-foreground">
                                <th class="p-3 font-medium">الصفحة</th>
                                <th class="p-3 font-medium">القسم</th>
                                <th class="p-3 font-medium">العنوان</th>
                                <th class="p-3 font-medium">المحتوى</th>
                                <th class="p-3 font-medium">إجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="p in pages.data" :key="p.id" class="border-b last:border-0 hover:bg-accent/40">
                                <td class="p-3 font-medium">{{ slugLabels[p.slug] ?? p.slug }}</td>
                                <td class="p-3 text-muted-foreground">{{ p.section_key ?? '—' }}</td>
                                <td class="p-3">{{ p.title ?? '—' }}</td>
                                <td class="p-3 text-muted-foreground"><span class="line-clamp-1 max-w-md">{{ p.body }}</span></td>
                                <td class="p-3">
                                    <div class="flex gap-1">
                                        <Button variant="ghost" size="icon" @click="openEdit(p)"><Pencil class="size-4" /></Button>
                                        <Button variant="ghost" size="icon" class="text-red-600 hover:text-red-700" @click="destroy(p)"><Trash2 class="size-4" /></Button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="pages.data.length === 0">
                                <td colspan="5" class="p-8 text-center text-muted-foreground">لا يوجد محتوى بعد</td>
                            </tr>
                        </tbody>
                    </table>
                    </div>
                    <Pagination :links="pages.links" :total="pages.total" />
                </CardContent>
            </Card>
        </div>

        <Dialog v-model:open="isOpen">
            <DialogContent>
                <DialogHeader><DialogTitle>{{ editingId ? 'تعديل محتوى' : 'إضافة محتوى' }}</DialogTitle></DialogHeader>
                <form @submit.prevent="submit" class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="grid gap-2">
                            <Label for="slug">الصفحة (slug)</Label>
                            <Input id="slug" v-model="form.slug" placeholder="about / privacy ..." dir="ltr" />
                            <InputError :message="form.errors.slug" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="section_key">مفتاح القسم</Label>
                            <Input id="section_key" v-model="form.section_key" placeholder="vision / mission ..." dir="ltr" />
                            <InputError :message="form.errors.section_key" />
                        </div>
                    </div>
                    <div class="grid gap-2">
                        <Label for="title">العنوان</Label>
                        <Input id="title" v-model="form.title" placeholder="عنوان القسم" />
                        <InputError :message="form.errors.title" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="body">المحتوى</Label>
                        <textarea
                            id="body"
                            v-model="form.body"
                            rows="7"
                            class="flex w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm"
                            placeholder="النص..."
                        ></textarea>
                        <InputError :message="form.errors.body" />
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
