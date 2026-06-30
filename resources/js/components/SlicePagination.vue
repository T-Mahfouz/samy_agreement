<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

defineProps<{ links: { url: string | null; label: string; active: boolean }[] }>();

// أرقام الصفحات فقط (نتجاهل أزرار السابق/التالي زي تصميم Slice)
const isPageLink = (label: string) =>
    !['Previous', 'Next', '&laquo;', '&raquo;', 'pagination.previous', 'pagination.next'].some((s) => label.includes(s));
</script>

<template>
    <div v-if="links.length > 3" class="d-flex justify-content-center flex-wrap mt_32 w-100">
        <nav class="ag-pagination d-flex align-items-center" aria-label="Pagination">
            <template v-for="(link, i) in links" :key="i">
                <Link
                    v-if="link.url && isPageLink(link.label)"
                    :href="link.url"
                    preserve-scroll
                    preserve-state
                    class="ag-pagination__page"
                    :class="{ 'is-active': link.active }"
                    :aria-current="link.active ? 'page' : undefined"
                    v-html="link.label"
                />
            </template>
        </nav>
    </div>
</template>
