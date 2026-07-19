<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { type BreadcrumbItem, type SharedData, type User } from '@/types';
import { Head, useForm, usePage } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'لوحة التحكم', href: '/admin/dashboard' },
    { title: 'الملف الشخصي', href: '/settings/profile' },
];

const page = usePage<SharedData>();
const user = page.props.auth.user as User;

const form = useForm({
    name: user.name,
    email: user.email,
});

const submit = () => form.patch(route('profile.update'), { preserveScroll: true });
</script>

<template>
    <Head title="الملف الشخصي" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4 md:p-6">
            <div>
                <h1 class="text-2xl font-bold">الملف الشخصي</h1>
                <p class="text-sm text-muted-foreground">تحديث اسمك وبريدك الإلكتروني</p>
            </div>

            <div class="max-w-xl rounded-xl border bg-card p-6">
                <form @submit.prevent="submit" class="flex flex-col gap-5">
                    <div class="grid gap-2">
                        <Label for="name">الاسم</Label>
                        <Input id="name" v-model="form.name" required autocomplete="name" placeholder="الاسم الكامل" />
                        <InputError :message="form.errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="email">البريد الإلكتروني</Label>
                        <Input id="email" type="email" v-model="form.email" required dir="ltr" placeholder="name@example.com" />
                        <InputError :message="form.errors.email" />
                    </div>

                    <div class="flex items-center gap-4">
                        <Button type="submit" :disabled="form.processing">حفظ</Button>
                        <transition
                            enter-active-class="transition ease-in-out"
                            enter-from-class="opacity-0"
                            leave-active-class="transition ease-in-out"
                            leave-to-class="opacity-0"
                        >
                            <p v-if="form.recentlySuccessful" class="text-sm font-medium text-emerald-600">تم الحفظ بنجاح ✓</p>
                        </transition>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
