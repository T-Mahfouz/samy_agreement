<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

interface SettingsData {
    platform_bank_name: string;
    platform_bank_beneficiary: string;
    platform_bank_iban: string;
    default_commission_rate: string;
    contact_phone: string;
    contact_whatsapp: string;
    contact_email: string;
    contact_support_email: string;
}

const props = defineProps<{ settings: SettingsData }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'لوحة التحكم', href: '/admin/dashboard' },
    { title: 'الإعدادات', href: '/admin/settings' },
];

const form = useForm({ ...props.settings });

const submit = () => form.put('/admin/settings', { preserveScroll: true });

const sendingTest = ref(false);
const sendTestEmail = () => {
    sendingTest.value = true;
    router.post('/admin/test-email', {}, { preserveScroll: true, onFinish: () => (sendingTest.value = false) });
};
</script>

<template>
    <Head title="الإعدادات" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4 md:p-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h1 class="text-2xl font-bold">الإعدادات</h1>
                    <p class="text-sm text-muted-foreground">إعدادات المنصة العامة</p>
                </div>
                <Button type="button" variant="outline" :disabled="sendingTest" @click="sendTestEmail">
                    {{ sendingTest ? 'جارٍ الإرسال...' : 'إرسال بريد تجريبي' }}
                </Button>
            </div>

            <form @submit.prevent="submit" class="space-y-4">
                <Card>
                    <CardHeader><CardTitle class="text-base">الحساب البنكي للمنصة (لاستلام العمولة)</CardTitle></CardHeader>
                    <CardContent class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div class="grid gap-2">
                            <Label for="bank_name">اسم البنك</Label>
                            <Input id="bank_name" v-model="form.platform_bank_name" />
                            <InputError :message="form.errors.platform_bank_name" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="beneficiary">اسم المستفيد</Label>
                            <Input id="beneficiary" v-model="form.platform_bank_beneficiary" />
                            <InputError :message="form.errors.platform_bank_beneficiary" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="iban">رقم الآيبان</Label>
                            <Input id="iban" v-model="form.platform_bank_iban" dir="ltr" />
                            <InputError :message="form.errors.platform_bank_iban" />
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader><CardTitle class="text-base">العمولة</CardTitle></CardHeader>
                    <CardContent class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div class="grid gap-2">
                            <Label for="commission">نسبة العمولة الافتراضية (%)</Label>
                            <Input id="commission" type="number" step="0.01" min="0" max="100" v-model="form.default_commission_rate" />
                            <InputError :message="form.errors.default_commission_rate" />
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader><CardTitle class="text-base">بيانات التواصل</CardTitle></CardHeader>
                    <CardContent class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="phone">رقم الجوال</Label>
                            <Input id="phone" type="tel" inputmode="numeric" v-model="form.contact_phone" dir="ltr" />
                            <InputError :message="form.errors.contact_phone" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="whatsapp">رقم الواتساب</Label>
                            <Input id="whatsapp" type="tel" inputmode="numeric" v-model="form.contact_whatsapp" dir="ltr" />
                            <InputError :message="form.errors.contact_whatsapp" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="email">البريد الإلكتروني</Label>
                            <Input id="email" type="email" v-model="form.contact_email" dir="ltr" />
                            <InputError :message="form.errors.contact_email" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="support_email">بريد الدعم الفني</Label>
                            <Input id="support_email" type="email" v-model="form.contact_support_email" dir="ltr" />
                            <InputError :message="form.errors.contact_support_email" />
                        </div>
                    </CardContent>
                </Card>

                <div class="flex justify-start">
                    <Button type="submit" :disabled="form.processing">حفظ الإعدادات</Button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
