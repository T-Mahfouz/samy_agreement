<script setup lang="ts">
import PublicLayout from '@/layouts/PublicLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps<{ title: string; body: string }>();
const img = (n: string) => `/slice/assets/images/${n}`;
</script>

<template>
    <Head :title="title" />

    <PublicLayout>
        <section class="tender_details">
            <div class="container">
                <div class="row">
                    <div class="col-12 d-flex align-items-center justify-content-between flex-wrap mb_32 position-relative">
                        <h3 class="fs-32 main-color fw-bold d-inline-flex align-items-center gap-4">
                            <div class="img_box main_bc d-flex align-items-center justify-content-center"><img :src="img('contact-file.png')" alt="" class="m-0"></div>
                            {{ title }}
                        </h3>
                    </div>
                    <div class="col-12">
                        <div class="border_box p_48 white_bc">
                            <p class="dark-color fs-16" style="white-space:pre-wrap; line-height:2;">{{ body }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>
