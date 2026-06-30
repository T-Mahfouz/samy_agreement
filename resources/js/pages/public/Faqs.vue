<script setup lang="ts">
import PublicLayout from '@/layouts/PublicLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';

interface Faq { id: number; question: string; answer: string }
defineProps<{ faqs: Faq[] }>();
const img = (n: string) => `/slice/assets/images/${n}`;

const open = ref<number | null>(null);
const toggle = (id: number) => { open.value = open.value === id ? null : id; };
</script>

<template>
    <Head title="الأسئلة الشائعة" />

    <PublicLayout>
        <section class="tender_details">
            <div class="container">
                <div class="row">
                    <div class="col-12 d-flex align-items-center justify-content-between flex-wrap mb_32 position-relative">
                        <h3 class="fs-32 main-color fw-bold d-inline-flex align-items-center gap-4">
                            <div class="img_box main_bc d-flex align-items-center justify-content-center"><img :src="img('menu-icon-3.png')" alt="" class="m-0"></div>
                            الأسئلة الشائعة
                        </h3>
                    </div>
                    <div class="col-12">
                        <div class="border_box white_bc p_24 faqs_group">
                            <div v-for="f in faqs" :key="f.id" class="border_bottom">
                                <button type="button"
                                    class="btn btn-link text-decoration-none w-100 text-start p_16 m-0 main-color fs-16 fw-bold rounded-0 shadow-none"
                                    :class="{ collapsed: open !== f.id }" @click="toggle(f.id)">
                                    <span class="d-flex align-items-center justify-content-between w-100 gap-4">
                                        <span class="fs-20 fw-bold d-flex align-items-center gap-2 flex-grow-1 text-start">
                                            <img :src="img('details-arrow.png')" alt="" class="m-0 flex-shrink-0">
                                            {{ f.question }}
                                        </span>
                                        <span class="img_box gray-bc d-flex align-items-center justify-content-center flex-shrink-0 m-0 fw-bold">{{ open === f.id ? '−' : '+' }}</span>
                                    </span>
                                </button>
                                <div class="collapse" :class="{ show: open === f.id }">
                                    <div class="p_16 dark-color fs-16 pt-0">{{ f.answer }}</div>
                                </div>
                            </div>
                            <p v-if="faqs.length === 0" class="dark-color p_16 m-0">لا توجد أسئلة بعد</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>
