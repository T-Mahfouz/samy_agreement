<script setup lang="ts">
import PublicLayout from '@/layouts/PublicLayout.vue';
import { Head } from '@inertiajs/vue3';

interface Section { title: string | null; body: string | null }
const props = defineProps<{ sections: { who_we_are: Section | null; vision: Section | null; mission: Section | null } }>();
const img = (n: string) => `/slice/assets/images/${n}`;
const s = props.sections;
</script>

<template>
    <Head title="عن منصة اتفاق" />

    <PublicLayout>
        <section class="tender_details">
            <div class="container">
                <div class="row">
                    <div class="col-12 d-flex align-items-center justify-content-between flex-wrap mb_32 position-relative">
                        <h3 class="fs-32 main-color fw-bold d-inline-flex align-items-center gap-4">
                            <div class="img_box main_bc d-flex align-items-center justify-content-center"><img :src="img('about.png')" alt="" class="m-0"></div>
                            عن منصة اتفاق
                        </h3>
                    </div>
                    <div class="col-12">
                        <div class="border_box p_48 white_bc about">
                            <h3 class="fs-24 main-color fw-bold mb_16">{{ s.who_we_are?.title ?? 'من نحن' }} :</h3>
                            <p class="dark-color mb_32 fs-16">{{ s.who_we_are?.body }}</p>
                            <div class="border_bottom mb_32 mt_32"></div>
                            <div class="row">
                                <div class="col-12 col-md-6">
                                    <div class="border_box p_24 border-radius-0 pst_0 border-0 border_end">
                                        <h3 class="fs-24 main-color fw-bold mb_16">{{ s.vision?.title ?? 'رؤيتنا' }} :</h3>
                                        <p class="dark-color mb-0 fs-16">{{ s.vision?.body }}</p>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="border_box p_24 border-radius-0 pst_0 border-0">
                                        <h3 class="fs-24 main-color fw-bold mb_16">{{ s.mission?.title ?? 'مهمتنا' }} :</h3>
                                        <p class="dark-color mb-0 fs-16">{{ s.mission?.body }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>
