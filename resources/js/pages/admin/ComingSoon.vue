<script setup lang="ts">
import { Card, CardContent } from '@/components/ui/card';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Construction } from 'lucide-vue-next';

const props = defineProps<{ title: string }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'لوحة التحكم', href: '/admin/dashboard' },
    { title: props.title, href: '#' },
];
</script>

<template>
    <Head :title="title" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
            <h1 class="text-2xl font-bold">{{ title }}</h1>
            <Card>
                <CardContent class="flex flex-col items-center justify-center gap-3 py-16 text-center">
                    <Construction class="size-12 text-muted-foreground" />
                    <p class="text-lg font-medium">قسم «{{ title }}» قيد الإنشاء</p>
                    <p class="max-w-md text-sm text-muted-foreground">
                        تم تجهيز قاعدة البيانات والمسار لهذا القسم. سيتم بناء واجهته في الخطوة التالية.
                    </p>
                </CardContent>
            </Card>
        </div>
    </AdminLayout>
</template>
