<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';
import Pagination from '@/components/admin/Pagination.vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { type BreadcrumbItem, type Paginated } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { MapPin, Pencil, Plus, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';

interface City {
    id: number;
    region_id: number;
    name: string;
    is_active: boolean;
}
interface Region {
    id: number;
    name: string;
    is_active: boolean;
    cities_count: number;
    cities: City[];
}

const props = defineProps<{ regions: Paginated<Region> }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'لوحة التحكم', href: '/admin/dashboard' },
    { title: 'المناطق والمدن', href: '/admin/locations' },
];

/* ---- Region dialog ---- */
const regionOpen = ref(false);
const editingRegionId = ref<number | null>(null);
const regionForm = useForm({ name: '', is_active: true });

const openCreateRegion = () => {
    editingRegionId.value = null;
    regionForm.reset();
    regionForm.clearErrors();
    regionOpen.value = true;
};
const openEditRegion = (r: Region) => {
    editingRegionId.value = r.id;
    regionForm.name = r.name;
    regionForm.is_active = r.is_active;
    regionForm.clearErrors();
    regionOpen.value = true;
};
const submitRegion = () => {
    const opts = { preserveScroll: true, onSuccess: () => { regionOpen.value = false; regionForm.reset(); } };
    if (editingRegionId.value) regionForm.put(`/admin/regions/${editingRegionId.value}`, opts);
    else regionForm.post('/admin/regions', opts);
};
const destroyRegion = (r: Region) => {
    if (confirm(`حذف منطقة «${r.name}» وكل مدنها؟`)) router.delete(`/admin/regions/${r.id}`, { preserveScroll: true });
};

/* ---- City dialog ---- */
const cityOpen = ref(false);
const editingCityId = ref<number | null>(null);
const cityForm = useForm({ region_id: 0 as number, name: '', is_active: true });

const openCreateCity = (region: Region) => {
    editingCityId.value = null;
    cityForm.reset();
    cityForm.region_id = region.id;
    cityForm.clearErrors();
    cityOpen.value = true;
};
const openEditCity = (c: City) => {
    editingCityId.value = c.id;
    cityForm.region_id = c.region_id;
    cityForm.name = c.name;
    cityForm.is_active = c.is_active;
    cityForm.clearErrors();
    cityOpen.value = true;
};
const submitCity = () => {
    const opts = { preserveScroll: true, onSuccess: () => { cityOpen.value = false; cityForm.reset(); } };
    if (editingCityId.value) cityForm.put(`/admin/cities/${editingCityId.value}`, opts);
    else cityForm.post('/admin/cities', opts);
};
const destroyCity = (c: City) => {
    if (confirm(`حذف مدينة «${c.name}»؟`)) router.delete(`/admin/cities/${c.id}`, { preserveScroll: true });
};
</script>

<template>
    <Head title="المناطق والمدن" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4 md:p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">المناطق والمدن</h1>
                    <p class="text-sm text-muted-foreground">إدارة مناطق ومدن التنفيذ</p>
                </div>
                <Button @click="openCreateRegion"><Plus class="size-4" /> إضافة منطقة</Button>
            </div>

            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                <Card v-for="r in regions.data" :key="r.id">
                    <CardHeader class="flex flex-row items-center justify-between space-y-0">
                        <CardTitle class="flex items-center gap-2 text-base">
                            <MapPin class="size-4 text-muted-foreground" />
                            {{ r.name }}
                            <span class="text-xs font-normal text-muted-foreground">({{ r.cities_count }} مدينة)</span>
                            <span
                                v-if="!r.is_active"
                                class="rounded bg-neutral-200 px-2 py-0.5 text-xs text-neutral-600 dark:bg-neutral-800 dark:text-neutral-400"
                            >معطّلة</span>
                        </CardTitle>
                        <div class="flex gap-1">
                            <Button variant="ghost" size="icon" @click="openCreateCity(r)" title="إضافة مدينة"><Plus class="size-4" /></Button>
                            <Button variant="ghost" size="icon" @click="openEditRegion(r)"><Pencil class="size-4" /></Button>
                            <Button variant="ghost" size="icon" class="text-red-600 hover:text-red-700" @click="destroyRegion(r)"><Trash2 class="size-4" /></Button>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <ul class="divide-y">
                            <li v-for="c in r.cities" :key="c.id" class="flex items-center justify-between py-2 text-sm">
                                <span :class="{ 'text-muted-foreground line-through': !c.is_active }">{{ c.name }}</span>
                                <div class="flex gap-1">
                                    <Button variant="ghost" size="icon" class="size-7" @click="openEditCity(c)"><Pencil class="size-3.5" /></Button>
                                    <Button variant="ghost" size="icon" class="size-7 text-red-600 hover:text-red-700" @click="destroyCity(c)"><Trash2 class="size-3.5" /></Button>
                                </div>
                            </li>
                            <li v-if="r.cities.length === 0" class="py-3 text-center text-xs text-muted-foreground">لا توجد مدن — أضف مدينة</li>
                        </ul>
                    </CardContent>
                </Card>
                <p v-if="regions.data.length === 0" class="col-span-full p-8 text-center text-muted-foreground">لا توجد مناطق بعد</p>
            </div>
            <Pagination :links="regions.links" :total="regions.total" />
        </div>

        <!-- Region dialog -->
        <Dialog v-model:open="regionOpen">
            <DialogContent>
                <DialogHeader><DialogTitle>{{ editingRegionId ? 'تعديل منطقة' : 'إضافة منطقة' }}</DialogTitle></DialogHeader>
                <form @submit.prevent="submitRegion" class="space-y-4">
                    <div class="grid gap-2">
                        <Label for="region_name">اسم المنطقة</Label>
                        <Input id="region_name" v-model="regionForm.name" placeholder="مثال: منطقة الرياض" />
                        <InputError :message="regionForm.errors.name" />
                    </div>
                    <div class="flex items-center gap-2">
                        <input id="region_active" type="checkbox" v-model="regionForm.is_active" class="size-4" />
                        <Label for="region_active">مفعّلة</Label>
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="outline" @click="regionOpen = false">إلغاء</Button>
                        <Button type="submit" :disabled="regionForm.processing">حفظ</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- City dialog -->
        <Dialog v-model:open="cityOpen">
            <DialogContent>
                <DialogHeader><DialogTitle>{{ editingCityId ? 'تعديل مدينة' : 'إضافة مدينة' }}</DialogTitle></DialogHeader>
                <form @submit.prevent="submitCity" class="space-y-4">
                    <div class="grid gap-2">
                        <Label for="city_region">المنطقة</Label>
                        <select
                            id="city_region"
                            v-model="cityForm.region_id"
                            class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm"
                        >
                            <option v-for="r in regions.data" :key="r.id" :value="r.id">{{ r.name }}</option>
                        </select>
                        <InputError :message="cityForm.errors.region_id" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="city_name">اسم المدينة</Label>
                        <Input id="city_name" v-model="cityForm.name" placeholder="مثال: الرياض" />
                        <InputError :message="cityForm.errors.name" />
                    </div>
                    <div class="flex items-center gap-2">
                        <input id="city_active" type="checkbox" v-model="cityForm.is_active" class="size-4" />
                        <Label for="city_active">مفعّلة</Label>
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="outline" @click="cityOpen = false">إلغاء</Button>
                        <Button type="submit" :disabled="cityForm.processing">حفظ</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </AdminLayout>
</template>
