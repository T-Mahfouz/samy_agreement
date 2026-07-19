<script setup lang="ts">
import '../../../css/app.css';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Head, useForm } from '@inertiajs/vue3';
import { LayoutGrid, LoaderCircle } from 'lucide-vue-next';

defineProps<{ status?: string }>();

const form = useForm({ email: '', password: '', remember: false });

const submit = () => form.post('/login', { onFinish: () => form.reset('password') });
</script>

<template>
    <Head title="دخول المشرف" />

    <div dir="rtl" class="flex min-h-svh items-center justify-center bg-muted/40 p-4">
        <div class="w-full max-w-sm rounded-xl border bg-card p-8 shadow-sm">
            <div class="mb-6 flex flex-col items-center gap-3 text-center">
                <div class="flex size-12 items-center justify-center rounded-xl bg-gradient-to-br from-primary to-emerald-600 text-white shadow-sm shadow-primary/30">
                    <LayoutGrid class="size-6" />
                </div>
                <div>
                    <h1 class="text-xl font-bold">لوحة تحكم اتفاق</h1>
                    <p class="text-sm text-muted-foreground">تسجيل دخول المشرف</p>
                </div>
            </div>

            <div v-if="status" class="mb-4 rounded-md bg-emerald-50 p-3 text-center text-sm font-medium text-emerald-700">
                {{ status }}
            </div>

            <form @submit.prevent="submit" class="flex flex-col gap-5">
                <div class="grid gap-2">
                    <Label for="email">البريد الإلكتروني</Label>
                    <Input id="email" type="email" v-model="form.email" required autofocus dir="ltr" />
                    <InputError :message="form.errors.email" />
                </div>

                <div class="grid gap-2">
                    <Label for="password">كلمة المرور</Label>
                    <Input id="password" type="password" v-model="form.password" required dir="ltr" />
                    <InputError :message="form.errors.password" />
                </div>

                <label class="flex cursor-pointer items-center gap-2 text-sm text-muted-foreground">
                    <input type="checkbox" v-model="form.remember" class="size-4 rounded border-input"> تذكّرني
                </label>

                <Button type="submit" class="w-full" :disabled="form.processing">
                    <LoaderCircle v-if="form.processing" class="size-4 animate-spin" />
                    دخول
                </Button>
            </form>
        </div>
    </div>
</template>
