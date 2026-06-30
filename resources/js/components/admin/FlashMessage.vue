<script setup lang="ts">
import { type SharedData } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { CheckCircle2, XCircle } from 'lucide-vue-next';
import { computed } from 'vue';

const page = usePage<SharedData>();
const success = computed(() => page.props.flash?.success);
const error = computed(() => page.props.flash?.error);
</script>

<template>
    <div v-if="success || error" class="px-4 pt-4 md:px-6">
        <div
            v-if="success"
            class="flex items-center gap-2 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-300"
        >
            <CheckCircle2 class="size-5 shrink-0" />
            <span>{{ success }}</span>
        </div>
        <div
            v-if="error"
            class="flex items-center gap-2 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-900 dark:bg-red-950/40 dark:text-red-300"
        >
            <XCircle class="size-5 shrink-0" />
            <span>{{ error }}</span>
        </div>
    </div>
</template>
